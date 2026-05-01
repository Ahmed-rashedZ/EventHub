<?php

namespace App\Http\Controllers;

use App\Models\Venue;
use Illuminate\Http\Request;

class VenueController extends Controller
{
    // Public list of venues
    public function index(Request $request)
    {
        $user = auth('sanctum')->user();
        if ($user && $user->role === 'Admin') {
            return response()->json(Venue::orderBy('name')->get());
        }
        return response()->json(Venue::where('status', 'available')->orderBy('name')->get());
    }

    public function store(Request $request)
    {
        $this->requireRole($request, 'Admin');

        $request->validate([
            'name'          => 'required|string|max:255',
            'location'      => 'required|url|max:500',
            'capacity'      => 'required|integer|min:1',
            'morning_start' => 'required|date_format:H:i',
            'morning_end'   => 'required|date_format:H:i|after:morning_start',
            'evening_start' => 'required|date_format:H:i|after:morning_end',
            'evening_end'   => 'required|date_format:H:i|after:evening_start',
        ]);

        // Unique name check
        if (Venue::where('name', $request->name)->exists()) {
            return response()->json(['message' => 'Venue name already exists'], 422);
        }

        $venue = Venue::create($request->only('name', 'location', 'capacity', 'status', 'morning_start', 'morning_end', 'evening_start', 'evening_end'));
        return response()->json($venue, 201);
    }

    public function update(Request $request, $id)
    {
        $this->requireRole($request, 'Admin');

        $request->validate([
            'morning_start' => 'sometimes|date_format:H:i',
            'morning_end'   => 'sometimes|date_format:H:i|after:morning_start',
            'evening_start' => 'sometimes|date_format:H:i|after:morning_end',
            'evening_end'   => 'sometimes|date_format:H:i|after:evening_start',
        ]);

        $venue = Venue::findOrFail($id);
        $venue->update($request->only('name', 'location', 'capacity', 'status', 'morning_start', 'morning_end', 'evening_start', 'evening_end'));
        return response()->json($venue);
    }

    public function destroy(Request $request, $id)
    {
        return response()->json(['message' => 'Venue deletion is disabled to preserve archive records'], 403);
    }

    public function bookings($id)
    {
        $events = \App\Models\Event::where('venue_id', $id)
            ->whereIn('status', ['approved', 'pending'])
            ->select('booking_date', 'period', 'start_time', 'end_time')
            ->get();
            
        $bookings = collect();

        foreach ($events as $event) {
            if ($event->booking_date && $event->period) {
                $bookings->push([
                    'booking_date' => \Carbon\Carbon::parse($event->booking_date)->format('Y-m-d'),
                    'period' => $event->period
                ]);
            } elseif ($event->start_time && $event->end_time) {
                // Old system compatibility
                $start = \Carbon\Carbon::parse($event->start_time)->startOfDay();
                $end = \Carbon\Carbon::parse($event->end_time)->startOfDay();
                
                for ($date = $start; $date->lte($end); $date->addDay()) {
                    $bookings->push([
                        'booking_date' => $date->format('Y-m-d'),
                        'period' => 'full_day'
                    ]);
                }
            }
        }
        
        return response()->json($bookings);
    }

    private function requireRole(Request $request, string $role)
    {
        if ($request->user()->role !== $role) {
            abort(403, 'Unauthorized');
        }
    }
}
