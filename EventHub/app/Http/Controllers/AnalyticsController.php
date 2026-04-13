<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Ticket;
use App\Models\User;
use App\Models\AttendanceLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    // GET /api/analytics/system – rich system stats (Admin)
    public function system(Request $request)
    {
        if ($request->user()->role !== 'Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $totalTickets = Ticket::count();
        $usedTickets  = Ticket::where('status', 'used')->count();
        $allEvents    = Event::withCount('tickets')->get();

        $eventsByStatus = $allEvents->groupBy('status')->map->count();
        $eventsByType   = $allEvents->groupBy(fn($e) => $e->event_type ?: 'Other')->map->count();
        $usersByRole    = User::all()->groupBy('role')->map->count();

        $monthlyRegs = Ticket::selectRaw("DATE_FORMAT(created_at, '%Y-%m') as month, count(*) as total")
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('month')->orderBy('month')
            ->pluck('total', 'month');

        $topEvents = $allEvents->where('status', 'approved')
            ->sortByDesc('tickets_count')->take(5)->values()
            ->map(fn($e) => [
                'id' => $e->id, 'title' => $e->title,
                'event_type' => $e->event_type ?: 'Other',
                'capacity' => $e->capacity, 'tickets_count' => $e->tickets_count,
                'fill_rate' => $e->capacity > 0 ? round(($e->tickets_count / $e->capacity) * 100, 1) : 0,
                'start_time' => $e->start_time,
            ]);

        return response()->json([
            'total_users'      => User::count(),
            'total_events'     => $allEvents->count(),
            'approved_events'  => $eventsByStatus->get('approved', 0),
            'pending_events'   => $eventsByStatus->get('pending', 0),
            'rejected_events'  => $eventsByStatus->get('rejected', 0),
            'total_tickets'    => $totalTickets,
            'used_tickets'     => $usedTickets,
            'total_attendance' => AttendanceLog::count(),
            'attendance_rate'  => $totalTickets > 0 ? round(($usedTickets / $totalTickets) * 100, 1) : 0,
            'events_by_status' => $eventsByStatus,
            'events_by_type'   => $eventsByType,
            'users_by_role'    => $usersByRole,
            'monthly_registrations' => $monthlyRegs,
            'top_events'       => $topEvents,
        ]);
    }

    // GET /api/analytics/manager – manager overview
    public function managerOverview(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'Event Manager') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $events   = Event::withCount('tickets')->where('created_by', $user->id)->get();
        $eventIds = $events->pluck('id');

        $totalTickets  = Ticket::whereIn('event_id', $eventIds)->count();
        $usedTickets   = Ticket::whereIn('event_id', $eventIds)->where('status', 'used')->count();
        $totalCapacity = $events->where('status', 'approved')->sum('capacity');

        $eventsByStatus = $events->groupBy('status')->map->count();
        $eventsByType   = $events->groupBy(fn($e) => $e->event_type ?: 'Other')->map->count();

        $ticketStats = Ticket::whereIn('event_id', $eventIds)
            ->selectRaw("event_id, count(*) as total, SUM(status='used') as used")
            ->groupBy('event_id')->get()->keyBy('event_id');

        $eventsData = $events->map(function ($ev) use ($ticketStats) {
            $s  = $ticketStats->get($ev->id);
            $tc = $s ? (int)$s->total : 0;
            $ac = $s ? (int)$s->used : 0;
            return [
                'id' => $ev->id, 'title' => $ev->title,
                'event_type' => $ev->event_type ?: 'Other',
                'status' => $ev->status, 'time_status' => $ev->time_status,
                'capacity' => $ev->capacity, 'tickets_count' => $tc,
                'attended_count' => $ac,
                'attendance_rate' => $tc > 0 ? round(($ac / $tc) * 100, 1) : 0,
                'fill_rate' => $ev->capacity > 0 ? round(($tc / $ev->capacity) * 100, 1) : 0,
                'start_time' => $ev->start_time, 'image' => $ev->image,
            ];
        })->values();

        return response()->json([
            'total_events' => $events->count(),
            'approved_events' => $eventsByStatus->get('approved', 0),
            'pending_events'  => $eventsByStatus->get('pending', 0),
            'rejected_events' => $eventsByStatus->get('rejected', 0),
            'total_tickets' => $totalTickets, 'used_tickets' => $usedTickets,
            'total_capacity' => $totalCapacity,
            'attendance_rate' => $totalTickets > 0 ? round(($usedTickets / $totalTickets) * 100, 1) : 0,
            'capacity_utilization' => $totalCapacity > 0 ? round(($totalTickets / $totalCapacity) * 100, 1) : 0,
            'events_by_status' => $eventsByStatus,
            'events_by_type'   => $eventsByType,
            'events' => $eventsData,
        ]);
    }

    // GET /api/analytics/event/{id}
    public function event(Request $request, $id)
    {
        $user = $request->user();
        if (!in_array($user->role, ['Admin', 'Event Manager', 'Sponsor'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $event = Event::with('venue')->findOrFail($id);
        if ($user->role === 'Event Manager' && $event->created_by !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        if ($user->role === 'Sponsor') {
            $isSponsor = \App\Models\SponsorshipRequest::where('event_id', $id)
                ->where('sponsor_id', $user->id)
                ->where('status', 'accepted')
                ->exists();
            if (!$isSponsor) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        }
        $registered = Ticket::where('event_id', $id)->count();
        $attended   = Ticket::where('event_id', $id)->where('status', 'used')->count();

        return response()->json([
            'event' => $event, 'registered_count' => $registered,
            'attended_count' => $attended,
            'attendance_rate' => $registered > 0 ? round(($attended / $registered) * 100, 1) : 0,
        ]);
    }

    // GET /api/analytics/users
    public function users(Request $request)
    {
        if ($request->user()->role !== 'Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        return response()->json(User::with('profile')->orderBy('created_at', 'desc')->get());
    }

    // PATCH /api/analytics/users/{id}/status
    public function toggleStatus(Request $request, $id)
    {
        if ($request->user()->role !== 'Admin') return response()->json(['message' => 'Unauthorized'], 403);
        if ($request->user()->id == $id) return response()->json(['message' => 'Cannot suspend yourself'], 422);
        $user = User::findOrFail($id);
        $user->is_active = !$user->is_active;
        $user->save();
        if (!$user->is_active) $user->tokens()->delete();
        return response()->json(['message' => 'User status updated']);
    }

    // DELETE /api/analytics/users/{id}
    public function deleteUser(Request $request, $id)
    {
        if ($request->user()->role !== 'Admin') return response()->json(['message' => 'Unauthorized'], 403);
        if ($request->user()->id == $id) return response()->json(['message' => 'Cannot delete yourself'], 422);
        User::findOrFail($id)->delete();
        return response()->json(['message' => 'User deleted']);
    }
}
