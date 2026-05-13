<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use App\Notifications\SystemNotification;
use Carbon\Carbon;

class EventController extends Controller
{
    // GET /api/events  – public approved+published events
    public function index(Request $request)
    {
        return response()->json(
            Event::with('venue', 'creator:id,name', 'sponsors.profile')
                ->withCount('tickets')
                ->withAvg('ratings', 'rating')
                ->where('status', 'approved')
                ->where('is_published', true)
                ->where('is_tickets_open', true)
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

        return response()->json(
            Event::with('venue', 'creator:id,name')
                ->withAvg('ratings', 'rating')
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

        return response()->json(
            Event::with('venue', 'sponsors.profile')
                ->withCount('tickets')
                ->withAvg('ratings', 'rating')
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
            'image'              => 'required|image|max:2048',
            'ministry_document'  => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'agenda'             => 'required|json',
            'event_objective'    => 'required|string',
            'target_audience'    => 'required|string',
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

            if ($overallStart < now()->addDays(30)->startOfDay()) {
                return response()->json([
                    'message' => 'Events must be booked at least 30 days in advance.',
                    'errors' => ['internal_schedule' => ['Cannot book earlier than 30 days from today.']]
                ], 422);
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

            if ($overallStart < now()->addDays(30)->startOfDay()) {
                return response()->json(['message' => 'Events must be booked at least 30 days in advance.', 'errors' => ['external_schedule' => ['Cannot book earlier than 30 days from today.']]], 422);
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

        $agendaJson = null;
        if ($request->has('agenda') && $request->agenda) {
            $agendaData = json_decode($request->agenda, true);
            if (is_array($agendaData)) {
                foreach ($agendaData as $day => $items) {
                    if (is_array($items)) {
                        usort($items, function($a, $b) {
                            return strcmp($a['start_time'], $b['start_time']);
                        });
                        $prevEnd = null;
                        foreach ($items as $item) {
                            if ($item['start_time'] >= $item['end_time']) {
                                return response()->json(['message' => "Invalid time in agenda for $day. Start time must be before end time."], 422);
                            }
                            if ($prevEnd !== null && $item['start_time'] < $prevEnd) {
                                return response()->json(['message' => "Overlapping agenda items are not allowed on $day."], 422);
                            }
                            $prevEnd = $item['end_time'];
                        }
                    }
                }
            }
            $agendaJson = $agendaData;
        }

        $eventData = array_merge($eventData, [
            'title'           => $request->title,
            'description'     => $request->description,
            'event_type'      => $request->event_type,
            'capacity'        => $request->capacity,
            'event_objective' => $request->event_objective,
            'target_audience' => $request->target_audience,
            'status'          => 'pending',
            'created_by'      => $request->user()->id,
            'image'           => $imagePath,
            'agenda'          => $agendaJson,
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
        $event = Event::with('venue', 'creator:id,name', 'sponsors.profile')
            ->withCount('tickets')
            ->withAvg('ratings', 'rating')
            ->findOrFail($id);
            
        // Append sponsorship_request_id to sponsors for contract downloading
        if ($event->relationLoaded('sponsors')) {
            $event->sponsors->map(function ($sponsor) use ($event) {
                $req = \App\Models\SponsorshipRequest::where('event_id', $event->id)
                    ->where('sponsor_id', $sponsor->id)
                    ->where('status', 'accepted')
                    ->first();
                $sponsor->sponsorship_request_id = $req ? $req->id : null;
                return $sponsor;
            });
        }
            
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
                ->withAvg('ratings', 'rating')
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
        
        if ($event->status !== 'approved') {
            return response()->json(['message' => 'Cannot open sponsorship for an event that is not approved.'], 400);
        }
        
        $event->is_sponsorship_open = !$event->is_sponsorship_open;
        $event->save();
        
        return response()->json($event);
    }

    // POST /api/events/{id}/rate  – User rates an event


    private function validateAgenda($agenda)
    {
        if (is_array($agenda)) {
            foreach ($agenda as $day => $items) {
                if (is_array($items)) {
                    usort($items, function($a, $b) {
                        if (!isset($a['start_time'], $b['start_time'])) return 0;
                        return strcmp($a['start_time'], $b['start_time']);
                    });
                    $prevEnd = null;
                    foreach ($items as $item) {
                        if (!isset($item['start_time'], $item['end_time'])) continue;
                        if ($item['start_time'] >= $item['end_time']) {
                            return response()->json(['message' => "Invalid time in agenda for $day. Start time must be before end time."], 422);
                        }
                        if ($prevEnd !== null && $item['start_time'] < $prevEnd) {
                            return response()->json(['message' => "Overlapping agenda items are not allowed on $day."], 422);
                        }
                        $prevEnd = $item['end_time'];
                    }
                }
            }
        }
        return null;
    }

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
        $event = Event::withAvg('ratings', 'rating')->findOrFail($id);
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
            'review_fields.*' => 'string|in:title,description,event_type,capacity,image,ministry_document,booking_proof,venue,dates,agenda',
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
        if (in_array('event_objective', $allowedFields) && $request->has('event_objective')) {
            $updateData['event_objective'] = $request->event_objective;
        }
        if (in_array('target_audience', $allowedFields) && $request->has('target_audience')) {
            $updateData['target_audience'] = $request->target_audience;
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
        if (in_array('agenda', $allowedFields) && $request->has('agenda')) {
            $updateData['agenda'] = $request->agenda ? json_decode($request->agenda, true) : null;
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

        $validation = $this->validateAgenda($agenda);
        if ($validation) return $validation;

        // Normalize: if it's a flat array (legacy), wrap it
        if (is_array($agenda) && !empty($agenda) && isset($agenda[0])) {
            // Legacy flat format — keep as-is for backward compat
        }

        $wasApproved = $event->status === 'approved';
        $updateData = ['agenda' => $agenda];
        
        if ($wasApproved) {
            $updateData['status'] = 'pending';
            $updateData['review_status'] = 'none';
            $updateData['review_message'] = null;
        }

        $event->update($updateData);

        if ($wasApproved) {
            // Notify Admins
            $admins = User::where('role', 'Admin')->get();
            foreach ($admins as $admin) {
                $admin->notify(new SystemNotification(
                    'Event Agenda Updated 🔄',
                    "Event \"{$event->title}\" agenda was updated by {$request->user()->name} and needs re-approval.",
                    'event',
                    '📋',
                    '/admin/events',
                    $event->id
                ));
            }
        }

        return response()->json(['message' => 'Agenda updated successfully', 'event' => $event->fresh()->load('venue')]);
    }

    public function downloadDocument($id, $type, Request $request)
    {
        $event = Event::findOrFail($id);
        $user = $request->user();

        // Security check: Only Admin, or the Manager who created the event, or a Sponsor of this event can download
        $isCreator = $event->created_by === $user->id;
        $isAdmin = $user->role === 'Admin';
        $isSponsor = $event->sponsors()->where('sponsor_id', $user->id)->exists();

        if (!$isAdmin && !$isCreator && !$isSponsor) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $path = null;
        if ($type === 'ministry_document') {
            $path = $event->ministry_document_path;
        } elseif ($type === 'booking_proof') {
            $path = $event->booking_proof_path;
        }

        if (!$path || !\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            return response()->json(['message' => 'Document not found'], 404);
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->download($path);
    }

    // POST /api/events/{id}/cancel-request  – Manager requests cancellation
    public function requestCancellation($id, Request $request)
    {
        if ($request->user()->role !== 'Event Manager') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'cancellation_reason' => 'required|string|max:2000',
        ]);

        $event = Event::findOrFail($id);

        if ($event->created_by !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($event->status !== 'approved') {
            return response()->json(['message' => 'Only approved events can be cancelled.'], 400);
        }

        // Restriction: Cannot cancel if less than 30 days away
        $eventDate = Carbon::parse($event->start_time);
        if ($eventDate->isPast()) {
             return response()->json(['message' => 'Cannot cancel an event that has already started.'], 400);
        }
        
        if ($eventDate->diffInDays(now()) < 30) {
            return response()->json(['message' => 'Cannot request cancellation for events starting in less than 30 days.'], 400);
        }

        $event->update([
            'status' => 'cancellation_requested',
            'cancellation_reason' => $request->cancellation_reason,
            'is_tickets_open' => false, // Suspend ticket sales immediately
            'is_sponsorship_open' => false, // Suspend sponsorship too
        ]);

        // Notify Admins
        $admins = User::where('role', 'Admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new SystemNotification(
                'Cancellation Requested ⚠️',
                "Manager {$request->user()->name} requested to cancel \"{$event->title}\".",
                'event',
                '⚠️',
                '/admin/events',
                $event->id
            ));
        }

        return response()->json(['message' => 'Cancellation request submitted', 'event' => $event]);
    }

    // PUT /api/events/{id}/cancel-approve  – Admin approves cancellation
    public function approveCancellation($id, Request $request)
    {
        if ($request->user()->role !== 'Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $event = Event::findOrFail($id);

        if ($event->status !== 'cancellation_requested') {
            return response()->json(['message' => 'Event is not in cancellation request state.'], 400);
        }

        $event->update([
            'status' => 'cancelled',
        ]);

        // Notify Manager
        $manager = User::find($event->created_by);
        if ($manager) {
            $manager->notify(new SystemNotification(
                'Cancellation Approved 🚫',
                "Your cancellation request for \"{$event->title}\" has been approved.",
                'event',
                '🚫',
                '/manager/events',
                $event->id
            ));
        }

        // Notify and update Sponsors
        $sponsorshipRequests = \App\Models\SponsorshipRequest::where('event_id', $event->id)
            ->whereIn('status', ['accepted', 'negotiating', 'pending'])
            ->get();

        foreach ($sponsorshipRequests as $sreq) {
            $sreq->update(['status' => 'cancelled']);
            
            $sponsor = User::find($sreq->sponsor_id);
            if ($sponsor) {
                $sponsor->notify(new SystemNotification(
                    'Event Cancelled 🚫',
                    "The event \"{$event->title}\" which you were sponsoring has been cancelled.",
                    'event',
                    '🚫',
                    '/sponsor/requests',
                    $event->id
                ));
            }
        }

        return response()->json(['message' => 'Event cancelled successfully', 'event' => $event]);
    }

    // PUT /api/events/{id}/cancel-reject  – Admin rejects cancellation
    public function rejectCancellation($id, Request $request)
    {
        if ($request->user()->role !== 'Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:2000',
        ]);

        $event = Event::findOrFail($id);

        if ($event->status !== 'cancellation_requested') {
            return response()->json(['message' => 'Event is not in cancellation request state.'], 400);
        }

        $event->update([
            'status' => 'approved', // Return to approved
            'cancellation_rejection_reason' => $request->rejection_reason,
            // is_tickets_open stays false!
        ]);

        // Notify Manager
        $manager = User::find($event->created_by);
        if ($manager) {
            $manager->notify(new SystemNotification(
                'Cancellation Rejected ⚠️',
                "Your cancellation request for \"{$event->title}\" was rejected: {$request->rejection_reason}",
                'event',
                '⚠️',
                '/manager/events',
                $event->id
            ));
        }

        return response()->json(['message' => 'Cancellation request rejected', 'event' => $event]);
    }

    // PATCH /api/events/{id}/toggle-tickets
    public function toggleTickets($id, Request $request)
    {
        if ($request->user()->role !== 'Event Manager') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $event = Event::findOrFail($id);

        if ($event->created_by !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!in_array($event->status, ['approved', 'cancellation_requested'])) {
            return response()->json(['message' => 'Cannot toggle tickets for this event status.'], 400);
        }

        $event->is_tickets_open = !$event->is_tickets_open;
        $event->save();

        return response()->json($event);
    }

    public function updatePublishedSchedule(Request $request, $id)
    {
        $event = Event::findOrFail($id);
        
        if ($request->user()->id !== $event->created_by && $request->user()->role !== 'Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'published_schedule' => 'required|array',
            'publish' => 'sometimes|boolean',
        ]);

        $updateData = [
            'published_schedule' => $request->published_schedule
        ];

        // If publish flag is explicitly set, use it; otherwise auto-publish if schedule has items
        if ($request->has('publish')) {
            $updateData['is_published'] = $request->publish;
        }

        $event->update($updateData);

        return response()->json([
            'message' => 'Published schedule updated successfully.',
            'event' => $event
        ]);
    }
}
