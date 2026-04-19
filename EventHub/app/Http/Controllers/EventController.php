<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

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
            'title'       => 'required|string|max:255',
            'description' => 'required|string',
            'event_type'  => 'required|string|in:مؤتمر,ندوة,ورشة عمل,دورة تدريبية,ترفيه,ملتقى علمي,رياضة,تقنية,اجتماعية',
            'venue_id'    => 'required|exists:venues,id',
            'start_time'  => 'required|date|after:now',
            'end_time'    => 'required|date|after:start_time',
            'capacity'    => 'required|integer|min:1',
            'image'       => 'nullable|image|max:2048',
        ]);

        $venue = \App\Models\Venue::find($request->venue_id);
        if ($venue && $request->capacity > $venue->capacity) {
            return response()->json([
                'message' => "Capacity cannot exceed the venue's total capacity of {$venue->capacity}.",
                'errors' => ['capacity' => ["Maximum allowed capacity is {$venue->capacity}."]]
            ], 422);
        }

        // Venue overlap conflict check
        $overlapping = Event::where('venue_id', $request->venue_id)
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($query) use ($request) {
                // Formula: existing start < new end AND existing end > new start
                $query->where('start_time', '<', $request->end_time)
                      ->where('end_time', '>', $request->start_time);
            })->exists();

        if ($overlapping) {
            return response()->json([
                'message' => 'The selected venue is already booked or requested for another event during this time period.',
                'errors' => ['venue_id' => ['Venue is unavailable during this time period.']]
            ], 422);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('events', 'public');
        }

        $event = Event::create([
            'title'       => $request->title,
            'description' => $request->description,
            'event_type'  => $request->event_type,
            'venue_id'    => $request->venue_id,
            'start_time'  => $request->start_time,
            'end_time'    => $request->end_time,
            'capacity'    => $request->capacity,
            'status'      => 'pending',
            'created_by'  => $request->user()->id,
            'image'       => $imagePath,
        ]);

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
