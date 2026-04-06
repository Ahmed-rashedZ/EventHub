<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Ticket;
use App\Models\User;
use App\Models\AttendanceLog;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    // GET /api/analytics/event/{id}  – stats for a specific event
    public function event(Request $request, $id)
    {
        $user = $request->user();
        $allowedRoles = ['Admin', 'Event Manager'];

        if (!in_array($user->role, $allowedRoles)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $event = Event::with('venue')->findOrFail($id);

        // If manager, ensure it's their event
        if ($user->role === 'Event Manager' && $event->created_by !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $registeredCount = Ticket::where('event_id', $id)->count();
        $attendedCount   = Ticket::where('event_id', $id)->where('status', 'used')->count();

        return response()->json([
            'event'            => $event,
            'registered_count' => $registeredCount,
            'attended_count'   => $attendedCount,
            'attendance_rate'  => $registeredCount > 0
                ? round(($attendedCount / $registeredCount) * 100, 1)
                : 0,
        ]);
    }

    // GET /api/analytics/system  – system-wide stats (Admin only)
    public function system(Request $request)
    {
        if ($request->user()->role !== 'Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json([
            'total_users'      => User::count(),
            'total_events'     => Event::count(),
            'approved_events'  => Event::where('status', 'approved')->count(),
            'pending_events'   => Event::where('status', 'pending')->count(),
            'total_tickets'    => Ticket::count(),
            'used_tickets'     => Ticket::where('status', 'used')->count(),
            'total_attendance' => AttendanceLog::count(),
        ]);
    }

    // GET /api/analytics/users  – user list for admin
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
        if ($request->user()->role !== 'Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        if ($request->user()->id == $id) {
            return response()->json(['message' => 'Cannot suspend yourself'], 422);
        }
        $user = User::findOrFail($id);
        $user->is_active = !$user->is_active;
        $user->save();
        
        if (!$user->is_active) {
            $user->tokens()->delete();
        }
        
        return response()->json(['message' => 'User status updated']);
    }

    // DELETE /api/analytics/users/{id}
    public function deleteUser(Request $request, $id)
    {
        if ($request->user()->role !== 'Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        if ($request->user()->id == $id) {
            return response()->json(['message' => 'Cannot delete yourself'], 422);
        }
        User::findOrFail($id)->delete();
        return response()->json(['message' => 'User deleted']);
    }
}
