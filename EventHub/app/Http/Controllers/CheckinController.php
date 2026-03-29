<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\AttendanceLog;
use Illuminate\Http\Request;

class CheckinController extends Controller
{
    // POST /api/checkin  – Assistant scans QR code
    public function checkin(Request $request)
    {
        $assistant = $request->user();

        if ($assistant->role !== 'Assistant') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'qr_code' => 'required|string',
        ]);

        $ticket = Ticket::with('event')->where('qr_code', $request->qr_code)->first();

        if (!$ticket) {
            return response()->json(['message' => 'Invalid QR code'], 404);
        }

        // 🔥 New: Check if Assistant is assigned to THIS event
        if ($assistant->event_id && $assistant->event_id != $ticket->event_id) {
            return response()->json(['message' => 'Unauthorized: You are not assigned to this event'], 403);
        }

        if ($ticket->status === 'used') {
            return response()->json([
                'message' => 'Ticket already used',
                'ticket'  => $ticket,
            ], 422);
        }

        // Mark ticket as used
        $ticket->status = 'used';
        $ticket->save();

        // Log the attendance
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

        $tickets = Ticket::with(['user', 'attendanceLog'])
            ->where('event_id', $eventId)
            ->get();

        return response()->json($tickets);
    }
}
