<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use App\Notifications\SystemNotification;

class EventController extends Controller
{
    // GET /api/events  – public approved events
    public function index(Request $request)
    {
        $user = $request->user();
        if ($user && $user->role === 'Sponsor') {
            if (!$user->profile?->is_available) {
                return response()->json(['message' => 'Your sponsorship availability is currently turned off. Go to your dashboard or profile to turn it back on to browse opportunities.'], 403);
            }
        }

        return response()->json(
            Event::with('venue', 'creator:id,name')
                ->where('status', 'approved')
                ->where('is_sponsorship_open', true)
                ->orderBy('start_time')
                ->get()
        );
    }

    // GET /api/events/pending  – admin sees pending events
    public function pending(Request $request)
    {
        if ($request->user()->role !== 'Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Auto-reject events that have reached their start time without approval
        Event::where('status', 'pending')
            ->where('start_time', '<=', now())
            ->update([
                'status' => 'rejected',
                'rejection_reason' => 'Auto-rejected: Event start time has reached/passed without admin approval.'
            ]);

        return response()->json(
            Event::with('venue', 'creator:id,name')
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->get()
        );
    }

    // GET /api/events/my  – manager's own events
    public function myEvents(Request $request)
    {
        if ($request->user()->role !== 'Event Manager') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Auto-reject events that have reached their start time without approval
        Event::where('created_by', $request->user()->id)
            ->where('status', 'pending')
            ->where('start_time', '<=', now())
            ->update([
                'status' => 'rejected',
                'rejection_reason' => 'Auto-rejected: Event start time has reached/passed without admin approval.'
            ]);

        return response()->json(
            Event::with('venue')
                ->where('created_by', $request->user()->id)
                ->orderBy('created_at', 'desc')
                ->get()
        );
    }

    // POST /api/events  – Event Manager creates an event
    public function store(Request $request)
    {
        if ($request->user()->role !== 'Event Manager') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'title'              => 'required|string|max:255',
            'description'        => 'required|string',
            'event_type'         => 'required|string|in:مؤتمر,ندوة,ورشة عمل,دورة تدريبية,ترفيه,ملتقى علمي,رياضة,تقنية,اجتماعية,معرض',
            'location_type'      => 'required|in:internal,external',
            'capacity'           => 'required|integer|min:1',
            'image'              => 'nullable|image|max:2048',
            'ministry_document'  => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        if ($request->location_type === 'internal') {
            $request->validate([
                'venue_id'          => 'required|exists:venues,id',
                'internal_schedule' => 'required|json',
            ]);

            $venue = \App\Models\Venue::find($request->venue_id);
            if ($venue && $request->capacity > $venue->capacity) {
                return response()->json([
                    'message' => "Capacity cannot exceed the venue's total capacity of {$venue->capacity}.",
                    'errors' => ['capacity' => ["Maximum allowed capacity is {$venue->capacity}."]]
                ], 422);
            }

            $schedule = json_decode($request->internal_schedule, true);
            if (!is_array($schedule) || count($schedule) === 0) {
                return response()->json([
                    'message' => 'Please provide a valid schedule.',
                    'errors' => ['internal_schedule' => ['Schedule cannot be empty.']]
                ], 422);
            }

            $overallStart = null;
            $overallEnd = null;

            foreach ($schedule as &$slot) {
                if (!isset($slot['date'], $slot['period'])) {
                    return response()->json(['message' => 'Invalid schedule format.'], 422);
                }

                if ($slot['period'] === 'morning') {
                    $slotStart = \Carbon\Carbon::parse("{$slot['date']} {$venue->morning_start}");
                    $slotEnd   = \Carbon\Carbon::parse("{$slot['date']} {$venue->morning_end}");
                } elseif ($slot['period'] === 'evening') {
                    $slotStart = \Carbon\Carbon::parse("{$slot['date']} {$venue->evening_start}");
                    $slotEnd   = \Carbon\Carbon::parse("{$slot['date']} {$venue->evening_end}");
                } else {
                    $slotStart = \Carbon\Carbon::parse("{$slot['date']} {$venue->morning_start}");
                    $slotEnd   = \Carbon\Carbon::parse("{$slot['date']} {$venue->evening_end}");
                }

                // Attach start_time and end_time for easier processing later if needed
                $slot['start_time'] = $slotStart->format('H:i');
                $slot['end_time'] = $slotEnd->format('H:i');

                // Venue overlap conflict check
                $overlapping = Event::where('venue_id', $request->venue_id)
                    ->whereIn('status', ['pending', 'approved'])
                    ->where(function ($query) use ($slotStart, $slotEnd) {
                        // For old events
                        $query->where(function ($q) use ($slotStart, $slotEnd) {
                            $q->whereNull('internal_schedule')
                              ->where('start_time', '<', $slotEnd)
                              ->where('end_time', '>', $slotStart);
                        })
                        // For new events (JSON search would be complex, so we check start_time and end_time overall first)
                        // Then we could check individual slots. Since MySQL JSON overlapping is tricky, 
                        // we can rely on overall bounds filtering, then in PHP loop.
                        // Actually, Event currently stores overall start_time and end_time.
                        // If there is an overlap in overall times, we must load them and check the JSON.
                        ->orWhere(function ($q) use ($slotStart, $slotEnd) {
                            $q->whereNotNull('internal_schedule')
                              ->where('start_time', '<=', $slotEnd)
                              ->where('end_time', '>=', $slotStart);
                        });
                    })
                    ->get();

                foreach ($overlapping as $overlapEvent) {
                    if ($overlapEvent->internal_schedule) {
                        foreach ($overlapEvent->internal_schedule as $exSlot) {
                            if ($exSlot['date'] === $slot['date']) {
                                // Same day, check overlap
                                $exStart = \Carbon\Carbon::parse("{$exSlot['date']} {$exSlot['start_time']}");
                                $exEnd = \Carbon\Carbon::parse("{$exSlot['date']} {$exSlot['end_time']}");
                                if ($slotStart < $exEnd && $slotEnd > $exStart) {
                                    return response()->json([
                                        'message' => 'The selected venue is already booked or requested for another event during this time period.',
                                        'errors' => ['venue_id' => ["Venue is unavailable on {$slot['date']} ({$slot['period']})."]]
                                    ], 422);
                                }
                            }
                        }
                    } else {
                        // It overlapped the old style query
                        return response()->json([
                            'message' => 'The selected venue is already booked or requested for another event during this time period.',
                            'errors' => ['venue_id' => ["Venue is unavailable on {$slot['date']}. (Conflict with existing single-day event)"]]
                        ], 422);
                    }
                }

                if (is_null($overallStart) || $slotStart < $overallStart) {
                    $overallStart = $slotStart;
                }
                if (is_null($overallEnd) || $slotEnd > $overallEnd) {
                    $overallEnd = $slotEnd;
                }
            }

            $eventData = [
                'venue_id'     => $request->venue_id,
                'booking_date' => null, // Legacy field
                'period'       => null, // Legacy field
                'start_time'   => $overallStart,
                'end_time'     => $overallEnd,
                'internal_schedule' => $schedule,
                'external_venue_name' => null,
                'external_venue_location' => null,
                'booking_proof_path' => null,
                'ministry_document_path' => null,
            ];

        } else {
            // External Venue
            $request->validate([
                'external_venue_name'     => 'required|string|max:255',
                'external_venue_location' => 'nullable|url|max:500',
                'booking_proof'           => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
                'external_schedule'       => 'required|json',
            ]);

            $schedule = json_decode($request->external_schedule, true);
            if (!is_array($schedule) || count($schedule) === 0) {
                return response()->json([
                    'message' => 'Please provide a valid schedule.',
                    'errors' => ['external_schedule' => ['Schedule cannot be empty.']]
                ], 422);
            }

            $overallStart = null;
            $overallEnd = null;

            foreach ($schedule as $slot) {
                if (!isset($slot['date'], $slot['start_time'], $slot['end_time'])) {
                    return response()->json(['message' => 'Invalid schedule format.'], 422);
                }
                
                $slotStart = \Carbon\Carbon::parse("{$slot['date']} {$slot['start_time']}");
                $slotEnd = \Carbon\Carbon::parse("{$slot['date']} {$slot['end_time']}");

                if ($slotStart >= $slotEnd) {
                    return response()->json(['message' => 'End time must be after start time for each day.', 'errors' => ['external_schedule' => ['Invalid time range on ' . $slot['date']]]], 422);
                }

                if (is_null($overallStart) || $slotStart < $overallStart) {
                    $overallStart = $slotStart;
                }
                if (is_null($overallEnd) || $slotEnd > $overallEnd) {
                    $overallEnd = $slotEnd;
                }
            }

            if ($overallStart <= now()) {
                return response()->json(['message' => 'Schedule must be in the future.', 'errors' => ['external_schedule' => ['Cannot book in the past.']]], 422);
            }

            // External venue overlap conflict check (Detailed)
            $overlappingEvents = Event::where('external_venue_name', $request->external_venue_name)
                ->whereIn('status', ['pending', 'approved'])
                ->where('start_time', '<', $overallEnd)
                ->where('end_time', '>', $overallStart)
                ->get();

            foreach ($overlappingEvents as $overlapEvent) {
                if ($overlapEvent->external_schedule) {
                    foreach ($overlapEvent->external_schedule as $exSlot) {
                        $exStart = \Carbon\Carbon::parse("{$exSlot['date']} {$exSlot['start_time']}");
                        $exEnd = \Carbon\Carbon::parse("{$exSlot['date']} {$exSlot['end_time']}");
                        
                        foreach ($schedule as $slot) {
                            $slotStart = \Carbon\Carbon::parse("{$slot['date']} {$slot['start_time']}");
                            $slotEnd = \Carbon\Carbon::parse("{$slot['date']} {$slot['end_time']}");
                            
                            if ($slotStart < $exEnd && $slotEnd > $exStart) {
                                return response()->json([
                                    'message' => 'This external hall is already booked or requested for another event during this time period.',
                                    'errors' => ['external_schedule' => ['External hall is unavailable on ' . $slot['date'] . '.']]
                                ], 422);
                            }
                        }
                    }
                } else {
                    foreach ($schedule as $slot) {
                        $slotStart = \Carbon\Carbon::parse("{$slot['date']} {$slot['start_time']}");
                        $slotEnd = \Carbon\Carbon::parse("{$slot['date']} {$slot['end_time']}");
                        
                        if ($slotStart < $overlapEvent->end_time && $slotEnd > $overlapEvent->start_time) {
                            return response()->json([
                                'message' => 'This external hall is already booked or requested for another event during this time period.',
                                'errors' => ['external_schedule' => ['External hall is unavailable on ' . $slot['date'] . '.']]
                            ], 422);
                        }
                    }
                }
            }

            $proofPath = $request->file('booking_proof')->store('proofs', 'public');

            $eventData = [
                'venue_id'     => null,
                'booking_date' => null,
                'period'       => null,
                'external_venue_name'     => $request->external_venue_name,
                'external_venue_location' => $request->external_venue_location,
                'booking_proof_path'      => $proofPath,
                'ministry_document_path'  => null,
                'start_time'   => $overallStart,
                'end_time'     => $overallEnd,
                'external_schedule' => $schedule,
            ];
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('events', 'public');
        }

        // Ministry document (required for all events)
        $ministryPath = $request->file('ministry_document')->store('ministry_docs', 'public');
        $eventData['ministry_document_path'] = $ministryPath;

        $eventData = array_merge($eventData, [
            'title'       => $request->title,
            'description' => $request->description,
            'event_type'  => $request->event_type,
            'capacity'    => $request->capacity,
            'status'      => 'pending',
            'created_by'  => $request->user()->id,
            'image'       => $imagePath,
            'agenda'      => $request->agenda ? json_decode($request->agenda, true) : null,
        ]);

        $event = Event::create($eventData);

        // ── Notify all Admins about new pending event ──
        $admins = User::where('role', 'Admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new SystemNotification(
                'New Event Pending',
                "Event \"{$event->title}\" was submitted by {$request->user()->name} and needs your approval.",
                'event',
                '📋',
                '/admin/events',
                $event->id
            ));
        }

        return response()->json($event->load('venue'), 201);
    }

    // PUT /api/events/{id}/approve  – Admin approves
    public function approve($id, Request $request)
    {
        if ($request->user()->role !== 'Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $event = Event::findOrFail($id);
        $event->status = 'approved';
        $event->save();

        // ── Notify the Event Manager ──
        $manager = User::find($event->created_by);
        if ($manager) {
            $manager->notify(new SystemNotification(
                'Event Approved ✅',
                "Your event \"{$event->title}\" has been approved and is now live!",
                'event',
                '✅',
                '/manager/events',
                $event->id
            ));
        }

        return response()->json(['message' => 'Event approved', 'event' => $event]);
    }

    // PUT /api/events/{id}/reject  – Admin rejects
    public function reject($id, Request $request)
    {
        if ($request->user()->role !== 'Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $event = Event::findOrFail($id);
        $event->status = 'rejected';
        $event->rejection_reason = $request->input('rejection_reason');
        $event->save();

        // ── Notify the Event Manager ──
        $manager = User::find($event->created_by);
        if ($manager) {
            $reason = $event->rejection_reason ? ": {$event->rejection_reason}" : '.';
            $manager->notify(new SystemNotification(
                'Event Rejected ❌',
                "Your event \"{$event->title}\" has been rejected{$reason}",
                'event',
                '❌',
                '/manager/events',
                $event->id
            ));
        }

        return response()->json(['message' => 'Event rejected', 'event' => $event]);
    }

    // GET /api/events/{id}  – single event details
    public function show($id)
    {
        $event = Event::with('venue', 'creator:id,name', 'sponsors.profile')->findOrFail($id);
        return response()->json($event);
    }

    // GET /api/events/all  – Admin sees all events
    public function all(Request $request)
    {
        if ($request->user()->role !== 'Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json(
            Event::with('venue', 'creator:id,name')
                ->orderBy('created_at', 'desc')
                ->get()
        );
    }
    
    // PATCH /api/events/{id}/toggle-sponsorship
    public function toggleSponsorship($id, Request $request)
    {
        if ($request->user()->role !== 'Event Manager') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $event = Event::findOrFail($id);
        
        // Ensure event belongs to manager
        if ($event->created_by !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $event->is_sponsorship_open = !$event->is_sponsorship_open;
        $event->save();
        
        return response()->json($event);
    }

    // POST /api/events/{id}/rate  – User rates an event
    public function rate($id, Request $request)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review_text' => 'nullable|string|max:1000',
        ]);

        $event = Event::findOrFail($id);
        $user = $request->user();

        // Check if user has a ticket
        $hasTicket = \App\Models\Ticket::where('event_id', $event->id)->where('user_id', $user->id)->exists();
        if (!$hasTicket) {
            return response()->json(['message' => 'You cannot review an event without a valid ticket.'], 403);
        }

        // Check if event has started
        if ($event->time_status === 'upcoming') {
            return response()->json(['message' => 'You cannot review an event that has not started yet.'], 403);
        }

        $rating = \App\Models\Rating::updateOrCreate(
            ['event_id' => $event->id, 'user_id' => $user->id],
            [
                'rating' => $request->rating,
                'review_text' => $request->review_text
            ]
        );

        // ── Notify the Event Manager about new rating ──
        $manager = User::find($event->created_by);
        if ($manager && $manager->id !== $user->id) {
            $stars = str_repeat('⭐', $request->rating);
            $manager->notify(new SystemNotification(
                'New Rating Received',
                "Your event \"{$event->title}\" received a {$request->rating}-star rating {$stars}",
                'event',
                '⭐',
                '/manager/event-stats/' . $event->id,
                $event->id
            ));
        }

        return response()->json([
            'message' => 'Rating submitted successfully',
            'rating' => $rating,
            'average_rating' => $event->fresh()->average_rating
        ]);
    }

    // GET /api/events/{id}/reviews  – Get reviews for an event
    public function reviews($id)
    {
        $event = Event::findOrFail($id);
        $reviews = $event->ratings()->with('user:id,name,image,avatar')->whereNotNull('review_text')->orderBy('updated_at', 'desc')->get();
        // Fallback or mix: maybe we want all ratings, but specifically reviews with text are more useful to display
        // We'll return all ratings so we can show stars, but if they have text it acts as a full review
        $allRatings = $event->ratings()->with('user:id,name,image,avatar')->orderBy('updated_at', 'desc')->get();
        
        return response()->json([
            'average_rating' => $event->average_rating,
            'reviews' => $allRatings
        ]);
    }

    // PUT /api/events/{id}/review — Admin sends review with field requirements
    public function sendReview($id, Request $request)
    {
        if ($request->user()->role !== 'Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'review_message' => 'required|string|max:1000',
            'review_fields'  => 'required|array|min:1',
            'review_fields.*' => 'string|in:title,description,event_type,capacity,image,ministry_document,booking_proof,venue,dates',
        ]);

        $event = Event::findOrFail($id);

        if ($event->status !== 'pending') {
            return response()->json(['message' => 'Only pending events can be reviewed.'], 422);
        }

        $event->update([
            'review_message' => $request->review_message,
            'review_fields'  => $request->review_fields,
            'review_status'  => 'needs_review',
        ]);

        // Notify Event Manager
        $manager = User::find($event->created_by);
        if ($manager) {
            $manager->notify(new SystemNotification(
                'Event Review Required 📝',
                "Your event \"{$event->title}\" needs changes: {$request->review_message}",
                'event',
                '📝',
                '/manager/events',
                $event->id
            ));
        }

        return response()->json(['message' => 'Review sent successfully', 'event' => $event]);
    }

    // PUT /api/events/{id}/update-pending — Manager updates pending event (only allowed fields)
    public function updatePending($id, Request $request)
    {
        if ($request->user()->role !== 'Event Manager') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $event = Event::findOrFail($id);

        if ($event->created_by !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($event->status !== 'pending') {
            return response()->json(['message' => 'Only pending events can be updated.'], 422);
        }

        if ($event->review_status !== 'needs_review') {
            return response()->json(['message' => 'No review changes requested for this event.'], 422);
        }

        $allowedFields = $event->review_fields ?? [];
        $updateData = [];

        // Process each allowed field
        if (in_array('title', $allowedFields) && $request->has('title')) {
            $updateData['title'] = $request->title;
        }
        if (in_array('description', $allowedFields) && $request->has('description')) {
            $updateData['description'] = $request->description;
        }
        if (in_array('event_type', $allowedFields) && $request->has('event_type')) {
            $updateData['event_type'] = $request->event_type;
        }
        if (in_array('capacity', $allowedFields) && $request->has('capacity')) {
            $updateData['capacity'] = (int) $request->capacity;
        }
        if (in_array('image', $allowedFields) && $request->hasFile('image')) {
            $updateData['image'] = $request->file('image')->store('events', 'public');
        }
        if (in_array('ministry_document', $allowedFields) && $request->hasFile('ministry_document')) {
            $updateData['ministry_document_path'] = $request->file('ministry_document')->store('ministry_docs', 'public');
        }
        if (in_array('booking_proof', $allowedFields) && $request->hasFile('booking_proof')) {
            $updateData['booking_proof_path'] = $request->file('booking_proof')->store('proofs', 'public');
        }

        if (empty($updateData)) {
            return response()->json(['message' => 'No valid updates provided.'], 422);
        }

        $updateData['review_status'] = 'reviewed';
        $event->update($updateData);

        // Notify Admins
        $admins = User::where('role', 'Admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new SystemNotification(
                'Event Updated 🔄',
                "Event \"{$event->title}\" was updated by {$request->user()->name} after review.",
                'event',
                '🔄',
                '/admin/events',
                $event->id
            ));
        }

        return response()->json(['message' => 'Event updated successfully', 'event' => $event->fresh()->load('venue')]);
    }

    // DELETE /api/events/{id}  – Event Manager deletes a pending event
    public function destroy($id, Request $request)
    {
        if ($request->user()->role !== 'Event Manager') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $event = Event::findOrFail($id);

        if ($event->created_by !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($event->status !== 'pending') {
            return response()->json(['message' => 'You can only delete events that are pending approval.'], 400);
        }

        // Delete associated files if needed (Optional but good practice)
        if ($event->image) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($event->image);
        }
        if ($event->ministry_document_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($event->ministry_document_path);
        }
        if ($event->booking_proof_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($event->booking_proof_path);
        }

        $event->delete();

        return response()->json(['message' => 'Event deleted successfully.']);
    }

    // PUT /api/events/{id}/agenda  – Manager updates event agenda
    public function updateAgenda($id, Request $request)
    {
        if ($request->user()->role !== 'Event Manager') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $event = Event::findOrFail($id);

        if ($event->created_by !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'agenda' => 'present|nullable',
        ]);

        $agenda = $request->agenda;

        // Normalize: if it's a flat array (legacy), wrap it
        if (is_array($agenda) && !empty($agenda) && isset($agenda[0])) {
            // Legacy flat format — keep as-is for backward compat
        }

        $event->update(['agenda' => $agenda]);

        return response()->json(['message' => 'Agenda updated successfully', 'event' => $event->fresh()->load('venue')]);
    }
}
