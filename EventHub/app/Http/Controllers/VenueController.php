<?php

namespace App\Http\Controllers;

use App\Models\Venue;
use App\Models\Event;
use App\Models\VenueMaintenancePeriod;
use App\Notifications\SystemNotification;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class VenueController extends Controller
{
    // Public list of venues
    public function index(Request $request)
    {
        $user = auth('sanctum')->user();
        if ($user && $user->role === 'Admin') {
            $venues = Venue::with('maintenancePeriods')->orderBy('name')->get();
        } else {
            $venues = Venue::where('status', 'available')
                ->with('maintenancePeriods')
                ->orderBy('name')
                ->get();
        }
        return response()->json($venues);
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

    /**
     * Get bookings for a venue (event bookings + maintenance periods).
     */
    public function bookings($id)
    {
        $venue = Venue::findOrFail($id);
        $events = Event::where('venue_id', $id)
            ->whereIn('status', ['approved', 'pending'])
            ->select('booking_date', 'period', 'start_time', 'end_time')
            ->get();
            
        $bookings = collect();

        foreach ($events as $event) {
            if ($event->booking_date && $event->period) {
                $bookings->push([
                    'booking_date' => Carbon::parse($event->booking_date)->format('Y-m-d'),
                    'period' => $event->period,
                    'type'   => 'booking',
                ]);
            } elseif ($event->start_time && $event->end_time) {
                // Old system compatibility
                $start = Carbon::parse($event->start_time)->startOfDay();
                $end = Carbon::parse($event->end_time)->startOfDay();
                
                for ($date = $start; $date->lte($end); $date->addDay()) {
                    $bookings->push([
                        'booking_date' => $date->format('Y-m-d'),
                        'period' => 'full_day',
                        'type'   => 'booking',
                    ]);
                }
            }
        }

        // Add maintenance dates
        $maintenanceDates = $venue->getMaintenanceDates();
        foreach ($maintenanceDates as $mDate) {
            $bookings->push([
                'booking_date' => $mDate,
                'period'       => 'full_day',
                'type'         => 'maintenance',
            ]);
        }
        
        return response()->json($bookings);
    }

    // ── Maintenance Period Endpoints ──────────────────────────────────────

    /**
     * Get all maintenance periods for a venue.
     */
    public function getMaintenancePeriods($id)
    {
        $venue = Venue::findOrFail($id);
        $periods = $venue->maintenancePeriods()
            ->orderBy('start_date')
            ->get();
        return response()->json($periods);
    }

    /**
     * Add a maintenance period and notify affected event managers.
     */
    public function addMaintenancePeriod(Request $request, $id)
    {
        $this->requireRole($request, 'Admin');

        $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date'   => 'required|date|after_or_equal:start_date',
            'reason'     => 'nullable|string|max:500',
        ]);

        $venue = Venue::findOrFail($id);

        $period = $venue->maintenancePeriods()->create([
            'start_date' => $request->start_date,
            'end_date'   => $request->end_date,
            'reason'     => $request->reason,
        ]);

        // ── Notify affected event managers ──
        $this->notifyConflictingManagers($venue, $period);

        return response()->json($period, 201);
    }

    /**
     * Delete a maintenance period.
     */
    public function deleteMaintenancePeriod(Request $request, $venueId, $periodId)
    {
        $this->requireRole($request, 'Admin');

        $period = VenueMaintenancePeriod::where('venue_id', $venueId)
            ->findOrFail($periodId);
        $period->delete();

        return response()->json(['message' => 'Maintenance period deleted']);
    }

    /**
     * Find events that conflict with a maintenance period and notify their managers.
     */
    private function notifyConflictingManagers(Venue $venue, VenueMaintenancePeriod $period): void
    {
        // Find events booked at this venue during the maintenance period
        $conflictingEvents = Event::where('venue_id', $venue->id)
            ->whereIn('status', ['approved', 'pending'])
            ->where(function ($query) use ($period) {
                // Events with booking_date system
                $query->where(function ($q) use ($period) {
                    $q->whereNotNull('booking_date')
                      ->where('booking_date', '>=', $period->start_date)
                      ->where('booking_date', '<=', $period->end_date);
                })
                // Events with start_time/end_time system (legacy)
                ->orWhere(function ($q) use ($period) {
                    $q->whereNull('booking_date')
                      ->whereNotNull('start_time')
                      ->where('start_time', '<=', $period->end_date->endOfDay())
                      ->where('end_time', '>=', $period->start_date->startOfDay());
                });
            })
            ->with('creator')
            ->get();

        if ($conflictingEvents->isEmpty()) {
            return;
        }

        $startFormatted = Carbon::parse($period->start_date)->format('Y-m-d');
        $endFormatted   = Carbon::parse($period->end_date)->format('Y-m-d');
        $reasonText     = $period->reason ? " (السبب: {$period->reason})" : '';

        // Group by creator to send one notification per manager
        $grouped = $conflictingEvents->groupBy('created_by');

        foreach ($grouped as $managerId => $events) {
            $manager = $events->first()->creator;
            if (!$manager) continue;

            $eventTitles = $events->pluck('title')->implode('، ');
            $count = $events->count();

            $title = "⚠️ تعارض صيانة قاعة";
            $message = "تم جدولة صيانة لقاعة \"{$venue->name}\" من {$startFormatted} إلى {$endFormatted}{$reasonText}. ";
            $message .= "لديك {$count} فعالية متأثرة: {$eventTitles}. ";
            $message .= "يرجى مراجعة التواريخ والتواصل مع الإدارة.";

            $manager->notify(new SystemNotification(
                $title,
                $message,
                'system',
                '🔧',
                '/manager/events',
                $venue->id
            ));
        }
    }

    private function requireRole(Request $request, string $role)
    {
        if ($request->user()->role !== $role) {
            abort(403, 'Unauthorized');
        }
    }
}
