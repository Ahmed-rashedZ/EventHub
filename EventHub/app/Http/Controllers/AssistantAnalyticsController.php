<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AttendanceLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssistantAnalyticsController extends Controller
{
    // GET /api/assistants/{id}/history - Detailed list of people scanned by this assistant
    public function getHistory(Request $request, $id)
    {
        $authUser = $request->user();
        if ($authUser->role !== 'Event Manager') return response()->json(['message' => 'Unauthorized'], 403);

        // Verify manager owns the event this assistant belongs to
        $assistant = User::where('id', $id)
            ->where('role', 'Assistant')
            ->whereHas('event', function ($q) use ($authUser) {
                $q->where('created_by', $authUser->id);
            })->firstOrFail();

        $history = AttendanceLog::where('scanned_by', $id)
            ->with(['ticket.user:id,name,avatar,image'])
            ->orderBy('scanned_at', 'desc')
            ->paginate(20);

        return response()->json($history);
    }

    // GET /api/assistants/{id}/stats - Aggregated stats for the assistant
    public function getStats(Request $request, $id)
    {
        $authUser = $request->user();
        if ($authUser->role !== 'Event Manager') return response()->json(['message' => 'Unauthorized'], 403);

        $assistant = User::where('id', $id)
            ->where('role', 'Assistant')
            ->whereHas('event', function ($q) use ($authUser) {
                $q->where('created_by', $authUser->id);
            })->firstOrFail();

        // 1. Scans per event (if they were reassigned)
        $scansByEvent = AttendanceLog::where('scanned_by', $id)
            ->join('tickets', 'attendance_logs.ticket_id', '=', 'tickets.id')
            ->join('events', 'tickets.event_id', '=', 'events.id')
            ->select('events.title', DB::raw('count(*) as total'))
            ->groupBy('events.id', 'events.title')
            ->get();

        // 2. Hourly distribution of scans (last 24h or total)
        $hourlyStats = AttendanceLog::where('scanned_by', $id)
            ->selectRaw('HOUR(scanned_at) as hour, count(*) as total')
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        return response()->json([
            'assistant' => $assistant->only(['id', 'name', 'email', 'phone']),
            'total_scans' => AttendanceLog::where('scanned_by', $id)->count(),
            'scans_by_event' => $scansByEvent,
            'hourly_distribution' => $hourlyStats,
        ]);
    }
}
