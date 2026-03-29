<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TicketController extends Controller
{
    // POST /api/tickets  – User books a ticket
    public function store(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'event_id' => 'required|exists:events,id',
        ]);

        $event = Event::findOrFail($request->event_id);

        if ($event->status !== 'approved') {
            return response()->json(['message' => 'Event is not available for booking'], 422);
        }

        // One ticket per user per event
        if (Ticket::where('event_id', $event->id)->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'You already have a ticket for this event'], 422);
        }

        // Check capacity
        $bookedCount = Ticket::where('event_id', $event->id)->count();
        if ($bookedCount >= $event->capacity) {
            return response()->json(['message' => 'Event is fully booked'], 422);
        }

        // Generate unique QR code token
        $qrCode = strtoupper(Str::random(10)) . '-' . $event->id . '-' . $user->id;

        $ticket = Ticket::create([
            'event_id' => $event->id,
            'user_id'  => $user->id,
            'qr_code'  => $qrCode,
            'status'   => 'unused',
        ]);

        return response()->json([
            'ticket'  => $ticket,
            'event'   => $event->load('venue'),
            'qr_url'  => "https://api.qrserver.com/v1/create-qr-code/?data={$qrCode}&size=200x200",
        ], 201);
    }

    // GET /api/my-tickets – User views their tickets
    public function myTickets(Request $request)
    {
        $tickets = Ticket::with(['event.venue'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get()
            ->map(function ($ticket) {
                $ticket->qr_url = "https://api.qrserver.com/v1/create-qr-code/?data={$ticket->qr_code}&size=200x200";
                return $ticket;
            });

        return response()->json($tickets);
    }
}
