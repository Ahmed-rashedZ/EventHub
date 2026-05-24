<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\AttendanceLog;
use Illuminate\Http\Request;

class CheckinController extends Controller
{
    // POST /api/checkin  – Assistant scans QR code (supports multi-day events)
    public function checkin(Request $request)
    {
        $assistant = $request->user();

        if ($assistant->role !== 'Assistant') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'qr_code' => 'required|string',
            'event_id' => 'nullable|integer',
        ]);

        $ticket = Ticket::with('event')->where('qr_code', $request->qr_code)->first();

        if (!$ticket) {
            return response()->json(['message' => 'Invalid QR code'], 404);
        }

        if ($request->filled('event_id') && (int) $ticket->event_id !== (int) $request->event_id) {
            return response()->json(['message' => 'This ticket belongs to another event.'], 422);
        }

        // Check if assistant has access to this event (new invitation system + old fallback)
        if (!$assistant->hasAccessToEvent($ticket->event_id)) {
            return response()->json(['message' => 'Unauthorized: You are not assigned to this event'], 403);
        }

        if ($ticket->event->time_status !== 'live') {
            return response()->json([
                'message' => 'Cannot scan tickets: The event is not live.',
            ], 422);
        }

        // ── Multi-day support: check if ticket was already scanned TODAY ──
        $alreadyScannedToday = AttendanceLog::where('ticket_id', $ticket->id)
            ->whereDate('scanned_at', now()->toDateString())
            ->exists();

        if ($alreadyScannedToday) {
            return response()->json([
                'message' => 'تم تسجيل حضور هذه التذكرة اليوم بالفعل',
                'ticket'  => $ticket,
            ], 422);
        }

        // Mark ticket as used (stays used once first scanned — for analytics/ratings)
        if ($ticket->status !== 'used') {
            $ticket->status = 'used';
            $ticket->save();
        }

        // Log the attendance for today
        $log = AttendanceLog::create([
            'ticket_id'  => $ticket->id,
            'scanned_by' => $assistant->id,
            'scanned_at' => now(),
        ]);

        return response()->json([
            'message'    => 'Check-in successful',
            'ticket'     => $ticket,
            'log'        => $log,
        ]);
    }

    // GET /api/checkin/event/{id}  – list participants for an event (attended + not yet)
    public function eventParticipants(Request $request, $eventId)
    {
        $role = $request->user()->role;
        if (!in_array($role, ['Assistant', 'Event Manager', 'Admin'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $today = now()->toDateString();

        $tickets = Ticket::with(['user', 'attendanceLogs.scanner:id,name'])
            ->where('event_id', $eventId)
            ->get()
            ->map(function ($ticket) use ($today) {
                $ticket->scanned_today = $ticket->attendanceLogs
                    ->contains(fn($log) => $log->scanned_at->toDateString() === $today);
                $ticket->total_days_attended = $ticket->attendanceLogs
                    ->pluck('scanned_at')
                    ->map(fn($d) => $d->toDateString())
                    ->unique()
                    ->count();
                return $ticket;
            });

        return response()->json($tickets);
    }
}
