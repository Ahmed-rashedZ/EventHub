<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Notifications\SystemNotification;

class TicketController extends Controller
{
    // POST /api/tickets  – User books a ticket
    public function store(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'event_id' => 'required|exists:events,id',
        ]);

        if ($user->role !== 'Attendee') {
            return response()->json(['message' => 'Only attendees can book tickets'], 403);
        }

        $event = Event::findOrFail($request->event_id);

        if ($event->status !== 'approved') {
            return response()->json(['message' => 'Event is not available for booking'], 422);
        }

        if (!$event->is_tickets_open) {
            return response()->json(['message' => 'Ticket sales are currently suspended for this event.'], 422);
        }

        // One ticket per user per event
        if (Ticket::where('event_id', $event->id)->where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'You already have a ticket for this event'], 422);
        }

        // Check capacity and get the next ticket number
        $bookedCount = Ticket::where('event_id', $event->id)->count();
        if ($event->capacity !== null && $bookedCount >= $event->capacity) {
            return response()->json(['message' => 'Event is fully booked'], 422);
        }

        // Get the highest current ticket number for this event and add 1
        $maxNumber = Ticket::where('event_id', $event->id)->max('ticket_number');
        $ticketNumber = ($maxNumber ?? 0) + 1;

        // Generate unique QR code token
        $qrCode = strtoupper(Str::random(10)) . '-' . $event->id . '-' . $user->id;

        $ticket = Ticket::create([
            'event_id'      => $event->id,
            'user_id'       => $user->id,
            'qr_code'       => $qrCode,
            'status'        => 'unused',
            'ticket_number' => $ticketNumber,
        ]);

        // ── Notify Event Manager about new ticket booking ──
        $manager = User::find($event->created_by);
        if ($manager && $manager->id !== $user->id) {
            $bookedNow = Ticket::where('event_id', $event->id)->count();
            $manager->notify(new SystemNotification(
                'New Ticket Booked 🎟️',
                "{$user->name} booked a ticket for \"{$event->title}\" ({$bookedNow}/" . ($event->capacity ?? '∞') . ")",
                'ticket',
                '🎟️',
                '/manager/event-stats/' . $event->id,
                $event->id
            ));
        }

        return response()->json([
            'ticket'  => $ticket,
            'event'   => $event->load('venue'),
            'qr_url'  => "https://api.qrserver.com/v1/create-qr-code/?data={$qrCode}&size=200x200",
        ], 201);
    }

    // GET /api/my-tickets – User views their tickets (with multi-day attendance info)
    public function myTickets(Request $request)
    {
        $today = now()->toDateString();

        $tickets = Ticket::with(['event.venue', 'attendanceLogs'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->get()
            ->map(function ($ticket) use ($today) {
                // If ticket_number is null, fallback to calculating it based on creation order for this event
                if (is_null($ticket->ticket_number)) {
                    $ticket->ticket_number = Ticket::where('event_id', $ticket->event_id)
                        ->where('id', '<=', $ticket->id)
                        ->count();
                }
                $ticket->qr_url = "https://api.qrserver.com/v1/create-qr-code/?data={$ticket->qr_code}&size=200x200";
                $ticket->scanned_today = $ticket->attendanceLogs
                    ->contains(fn($log) => $log->scanned_at->toDateString() === $today);
                $ticket->total_days_attended = $ticket->attendanceLogs
                    ->pluck('scanned_at')
                    ->map(fn($d) => $d->toDateString())
                    ->unique()
                    ->count();
                // Clean up the attendanceLogs from the response (optional: keep it lean)
                unset($ticket->attendanceLogs);
                return $ticket;
            });

        return response()->json($tickets);
    }
}
