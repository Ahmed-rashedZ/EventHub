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
            'title'         => 'required|string|max:255',
            'description'   => 'required|string',
            'event_type'    => 'required|string|in:مؤتمر,ندوة,ورشة عمل,دورة تدريبية,ترفيه,ملتقى علمي,رياضة,تقنية,اجتماعية,معرض',
            'location_type' => 'required|in:internal,external',
            'capacity'      => 'required|integer|min:1',
            'image'         => 'nullable|image|max:2048',
        ]);

        if ($request->location_type === 'internal') {
            $request->validate([
                'venue_id'     => 'required|exists:venues,id',
                'booking_date' => 'required|date|after_or_equal:today',
                'period'       => 'required|in:morning,evening,full_day',
            ]);

            $venue = \App\Models\Venue::find($request->venue_id);
            if ($venue && $request->capacity > $venue->capacity) {
                return response()->json([
                    'message' => "Capacity cannot exceed the venue's total capacity of {$venue->capacity}.",
                    'errors' => ['capacity' => ["Maximum allowed capacity is {$venue->capacity}."]]
                ], 422);
            }

            if ($request->period === 'morning') {
                $start_time = \Carbon\Carbon::parse("{$request->booking_date} {$venue->morning_start}");
                $end_time   = \Carbon\Carbon::parse("{$request->booking_date} {$venue->morning_end}");
            } elseif ($request->period === 'evening') {
                $start_time = \Carbon\Carbon::parse("{$request->booking_date} {$venue->evening_start}");
                $end_time   = \Carbon\Carbon::parse("{$request->booking_date} {$venue->evening_end}");
            } else {
                $start_time = \Carbon\Carbon::parse("{$request->booking_date} {$venue->morning_start}");
                $end_time   = \Carbon\Carbon::parse("{$request->booking_date} {$venue->evening_end}");
            }

            // Venue overlap conflict check
            $overlapping = Event::where('venue_id', $request->venue_id)
                ->whereIn('status', ['pending', 'approved'])
                ->where('start_time', '<', $end_time)
                ->where('end_time', '>', $start_time)
                ->exists();

            if ($overlapping) {
                return response()->json([
                    'message' => 'The selected venue is already booked or requested for another event during this time period.',
                    'errors' => ['venue_id' => ['Venue is unavailable during this time period.']]
                ], 422);
            }

            $eventData = [
                'venue_id'     => $request->venue_id,
                'booking_date' => $request->booking_date,
                'period'       => $request->period,
                'start_time'   => $start_time,
                'end_time'     => $end_time,
                'external_venue_name' => null,
                'external_venue_location' => null,
                'booking_proof_path' => null,
            ];

        } else {
            // External Venue
            $request->validate([
                'external_venue_name'     => 'required|string|max:255',
                'external_venue_location' => 'nullable|url|max:500',
                'booking_proof'           => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
                'start_time'              => 'required|date|after:now',
                'end_time'                => 'required|date|after:start_time',
            ]);

            // External venue overlap conflict check
            $overlapping = Event::where('external_venue_name', $request->external_venue_name)
                ->whereIn('status', ['pending', 'approved'])
                ->where('start_time', '<', $request->end_time)
                ->where('end_time', '>', $request->start_time)
                ->exists();

            if ($overlapping) {
                return response()->json([
                    'message' => 'This external hall is already booked or requested for another event during this time period.',
                    'errors' => ['external_venue_name' => ['External hall is unavailable during this time period.']]
                ], 422);
            }

            $proofPath = $request->file('booking_proof')->store('proofs', 'public');

            $eventData = [
                'venue_id'     => null,
                'booking_date' => null,
                'period'       => null,
                'external_venue_name'     => $request->external_venue_name,
                'external_venue_location' => $request->external_venue_location,
                'booking_proof_path'      => $proofPath,
                'start_time'   => $request->start_time,
                'end_time'     => $request->end_time,
            ];
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('events', 'public');
        }

        $eventData = array_merge($eventData, [
            'title'       => $request->title,
            'description' => $request->description,
            'event_type'  => $request->event_type,
            'capacity'    => $request->capacity,
            'status'      => 'pending',
            'created_by'  => $request->user()->id,
            'image'       => $imagePath,
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
}
