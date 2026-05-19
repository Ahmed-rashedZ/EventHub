<?php

namespace App\Http\Controllers;

use App\Models\AssistanceRequest;
use App\Models\AttendanceLog;
use App\Models\Profile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssistantController extends Controller
{
    /**
     * GET /api/assistant/requests
     * Pending invitations for the authenticated assistant.
     */
    public function getRequests(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'Assistant') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $requests = AssistanceRequest::where('assistant_id', $user->id)
            ->where('status', 'pending')
            ->with([
                'event:id,title,description,image,start_time,end_time,capacity',
                'event.venue:id,name,location',
                'manager:id,name,email',
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($requests);
    }

    /**
     * POST /api/assistant/requests/{id}/respond
     * Accept or reject an invitation.
     * Body: { "status": "accepted" | "rejected" }
     */
    public function respondToRequest(Request $request, $id)
    {
        $user = $request->user();
        if ($user->role !== 'Assistant') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'status' => 'required|string|in:accepted,rejected',
        ]);

        $assistanceRequest = AssistanceRequest::where('id', $id)
            ->where('assistant_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if (!$assistanceRequest) {
            return response()->json(['message' => 'Request not found or already responded'], 404);
        }

        $assistanceRequest->status = $request->status;
        $assistanceRequest->responded_at = now();
        $assistanceRequest->save();

        $message = $request->status === 'accepted'
            ? 'Invitation accepted successfully'
            : 'Invitation rejected';

        return response()->json([
            'message' => $message,
            'request' => $assistanceRequest->load(['event:id,title', 'manager:id,name']),
        ]);
    }

    /**
     * PATCH /api/assistant/availability
     * Toggle assistant availability for new invitations.
     * Body: { "is_available": true | false }
     */
    public function toggleAvailability(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'Assistant') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'is_available' => 'required|boolean',
        ]);

        $isAvailable = $request->boolean('is_available');

        $profile = Profile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'is_available' => $isAvailable,
                'profile_type' => 'individual',
            ]
        );

        $profile->refresh();

        return response()->json([
            'message' => 'Availability updated',
            'is_available' => (bool) $profile->is_available,
        ]);
    }

    /**
     * GET /api/assistant/work
     * Accepted events that haven't ended yet (current + upcoming).
     */
    public function getAcceptedEvents(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'Assistant') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $now = now();

        $events = AssistanceRequest::where('assistant_id', $user->id)
            ->where('status', 'accepted')
            ->whereHas('event', function ($q) use ($now) {
                // Events not yet ended (or no end_time set)
                $q->where(function ($q2) use ($now) {
                    $q2->whereNull('end_time')
                       ->orWhere('end_time', '>=', $now);
                });
            })
            ->with([
                'event:id,title,description,image,start_time,end_time,capacity,created_by',
                'event.venue:id,name,location',
                'event.creator:id,name',
            ])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($req) use ($now) {
                $event = $req->event;
                if (!$event) return null;

                $startDt = $event->start_time ? \Carbon\Carbon::parse($event->start_time) : null;
                $endDt = $event->end_time ? \Carbon\Carbon::parse($event->end_time) : null;

                $timeStatus = 'upcoming';
                if ($startDt && $startDt->lte($now)) {
                    $timeStatus = ($endDt && $endDt->lte($now)) ? 'ended' : 'live';
                }

                // Ticket counts
                $totalTickets = DB::table('tickets')->where('event_id', $event->id)->count();
                $scannedTickets = DB::table('tickets')->where('event_id', $event->id)->where('status', 'used')->count();

                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'description' => $event->description,
                    'image' => $event->image,
                    'start_time' => $event->start_time,
                    'end_time' => $event->end_time,
                    'venue' => $event->venue,
                    'time_status' => $timeStatus,
                    'total_tickets' => $totalTickets,
                    'scanned_tickets' => $scannedTickets,
                    'creator' => $event->creator ? [
                        'id' => $event->creator->id,
                        'name' => $event->creator->name,
                    ] : null,
                ];
            })
            ->filter()
            ->values();

        return response()->json($events);
    }

    /**
     * GET /api/assistant/work/{id}
     * Event work details + participants list with scan info.
     */
    public function getEventWorkDetails(Request $request, $id)
    {
        $user = $request->user();
        if ($user->role !== 'Assistant') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Verify assistant has access to this event
        if (!$user->hasAccessToEvent((int) $id)) {
            return response()->json(['message' => 'Unauthorized: No access to this event'], 403);
        }

        // Fetch event
        $event = \App\Models\Event::with('venue:id,name,location')->findOrFail($id);

        // Fetch participants (tickets with user + attendance log)
        $tickets = \App\Models\Ticket::with(['user:id,name,email', 'attendanceLog.scanner:id,name'])
            ->where('event_id', $id)
            ->get()
            ->map(function ($ticket) {
                $log = $ticket->attendanceLog;
                return [
                    'user_name' => $ticket->user->name ?? 'Unknown',
                    'user_email' => $ticket->user->email ?? '',
                    'qr_code' => $ticket->qr_code,
                    'ticket_status' => $ticket->status, // 'valid' or 'used'
                    'scanned_by' => $log ? ($log->scanner->name ?? 'Unknown') : null,
                    'scanned_at' => $log ? $log->scanned_at?->toIso8601String() : null,
                ];
            });

        // Stats
        $totalTickets = $tickets->count();
        $totalScanned = $tickets->where('ticket_status', 'used')->count();
        $myScans = AttendanceLog::where('scanned_by', $user->id)
            ->whereHas('ticket', function ($q) use ($id) {
                $q->where('event_id', $id);
            })
            ->count();

        return response()->json([
            'event' => $event,
            'participants' => $tickets,
            'stats' => [
                'total_tickets' => $totalTickets,
                'total_scanned' => $totalScanned,
                'my_scans' => $myScans,
            ],
        ]);
    }

    /**
     * GET /api/assistant/history
     * Past events the assistant participated in (ended events).
     * Supports search by event name or venue name.
     */
    public function getHistory(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'Assistant') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $now = now();
        $search = $request->input('search');

        $query = AssistanceRequest::where('assistant_id', $user->id)
            ->where('status', 'accepted')
            ->whereHas('event', function ($q) use ($now) {
                $q->where('end_time', '<', $now);
            })
            ->with([
                'event:id,title,description,image,start_time,end_time,capacity',
                'event.venue:id,name,location',
            ]);

        // Search filter
        if ($search) {
            $query->whereHas('event', function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                  ->orWhereHas('venue', function ($q2) use ($search) {
                      $q2->where('name', 'LIKE', "%{$search}%");
                  });
            });
        }

        $events = $query->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($req) use ($user) {
                $event = $req->event;
                if (!$event) return null;

                // Count scans by this assistant for this event
                $myScans = AttendanceLog::where('scanned_by', $user->id)
                    ->whereHas('ticket', function ($q) use ($event) {
                        $q->where('event_id', $event->id);
                    })
                    ->count();

                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'image' => $event->image,
                    'start_time' => $event->start_time,
                    'end_time' => $event->end_time,
                    'venue' => $event->venue,
                    'my_scans' => $myScans,
                ];
            })
            ->filter()
            ->values();

        return response()->json($events);
    }

    /**
     * GET /api/assistant/history/{id}/stats
     * Detailed stats for a past event this assistant participated in.
     */
    public function getEventStats(Request $request, $id)
    {
        $user = $request->user();
        if ($user->role !== 'Assistant') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Verify access
        if (!$user->hasAccessToEvent((int) $id)) {
            return response()->json(['message' => 'Unauthorized: No access to this event'], 403);
        }

        $event = \App\Models\Event::with('venue:id,name,location')->findOrFail($id);

        $totalBooked = DB::table('tickets')->where('event_id', $id)->count();
        $totalScanned = DB::table('tickets')->where('event_id', $id)->where('status', 'used')->count();

        // Scans by this assistant
        $myScans = AttendanceLog::where('scanned_by', $user->id)
            ->whereHas('ticket', function ($q) use ($id) {
                $q->where('event_id', $id);
            })
            ->with(['ticket.user:id,name,email'])
            ->orderBy('scanned_at', 'desc')
            ->get()
            ->map(function ($log) {
                return [
                    'user_name' => $log->ticket->user->name ?? 'Unknown',
                    'qr_code' => $log->ticket->qr_code ?? '',
                    'scanned_at' => $log->scanned_at?->toIso8601String(),
                ];
            });

        return response()->json([
            'event' => $event,
            'total_booked' => $totalBooked,
            'total_scanned' => $totalScanned,
            'my_scans_count' => $myScans->count(),
            'my_scans' => $myScans,
        ]);
    }

    // ═══════════════════════════════════════════════════════════════════════════
    //  MANAGER-FACING  ENDPOINTS  (New Invitation System)
    // ═══════════════════════════════════════════════════════════════════════════

    /**
     * GET /api/manager/available-assistants
     * List all assistants with is_available = true who can be invited.
     */
    public function getAvailableAssistants(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'Event Manager') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $search = $request->input('search');
        $eventId = $request->input('event_id');

        $query = \App\Models\User::where('role', 'Assistant')
            ->where('is_active', true)
            ->whereHas('profile', function ($q) {
                $q->where('is_available', true);
            })
            ->with('profile:id,user_id,logo');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        $assistants = $query->get(['id', 'name', 'email'])->map(function ($a) use ($user, $eventId) {
            // Count past events with this manager
            $pastEvents = AssistanceRequest::where('assistant_id', $a->id)
                ->where('manager_id', $user->id)
                ->where('status', 'accepted')
                ->count();

            // Check invitation status for the specific event if provided
            $invitationStatus = null;
            if ($eventId) {
                $existingReq = AssistanceRequest::where('assistant_id', $a->id)
                    ->where('event_id', $eventId)
                    ->first();
                if ($existingReq) {
                    $invitationStatus = $existingReq->status;
                } else {
                    // Check if assistant has a time conflict with this event
                    $currentEvent = \App\Models\Event::find($eventId);
                    if ($currentEvent) {
                        $eventStart = \Carbon\Carbon::parse($currentEvent->start_time);
                        $eventEnd = $currentEvent->end_time 
                            ? \Carbon\Carbon::parse($currentEvent->end_time) 
                            : $eventStart->copy()->addHours(3);

                        $now = now();
                        $acceptedRequests = AssistanceRequest::where('assistant_id', $a->id)
                            ->where('status', 'accepted')
                            ->whereHas('event', function ($q) use ($now) {
                                $q->where(function ($q2) use ($now) {
                                    $q2->whereNull('end_time')
                                       ->orWhere('end_time', '>=', $now);
                                });
                            })
                            ->with('event')
                            ->get();

                        foreach ($acceptedRequests as $req) {
                            $otherEvent = $req->event;
                            if (!$otherEvent) continue;

                            $otherStart = \Carbon\Carbon::parse($otherEvent->start_time);
                            $otherEnd = $otherEvent->end_time 
                                ? \Carbon\Carbon::parse($otherEvent->end_time) 
                                : $otherStart->copy()->addHours(3);

                            if ($eventStart->lt($otherEnd) && $otherStart->lt($eventEnd)) {
                                $invitationStatus = 'busy';
                                break;
                            }
                        }
                    }
                }
            }

            return [
                'id' => $a->id,
                'name' => $a->name,
                'email' => $a->email,
                'logo' => $a->profile->logo ?? null,
                'past_events_with_me' => $pastEvents,
                'invitation_status' => $invitationStatus,
            ];
        });

        return response()->json($assistants);
    }

    /**
     * POST /api/manager/invite-assistant
     * Send an invitation to an assistant for a specific event.
     * Body: { "assistant_id": int, "event_id": int, "message": string|null }
     */
    public function sendInvitation(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'Event Manager') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'assistant_id' => 'required|integer|exists:users,id',
            'event_id' => 'required|integer|exists:events,id',
            'message' => 'nullable|string|max:500',
        ]);

        // Verify the event belongs to this manager
        $event = \App\Models\Event::where('id', $request->event_id)
            ->where('created_by', $user->id)
            ->first();

        if (!$event) {
            return response()->json(['message' => 'Event not found or not owned by you'], 404);
        }

        // Prevent invitations to ended events
        if ($event->time_status === 'ended') {
            return response()->json(['message' => 'Cannot invite assistants to an ended event'], 422);
        }

        // Verify the assistant exists and is available
        $assistant = \App\Models\User::where('id', $request->assistant_id)
            ->where('role', 'Assistant')
            ->where('is_active', true)
            ->first();

        if (!$assistant) {
            return response()->json(['message' => 'Assistant not found or inactive'], 404);
        }

        // Check if already invited
        $existing = AssistanceRequest::where('assistant_id', $assistant->id)
            ->where('event_id', $event->id)
            ->first();

        if ($existing) {
            return response()->json(['message' => 'This assistant has already been invited to this event (status: ' . $existing->status . ')'], 422);
        }

        // Check if the assistant has a time conflict (overlapping accepted event)
        $eventStart = \Carbon\Carbon::parse($event->start_time);
        $eventEnd = $event->end_time 
            ? \Carbon\Carbon::parse($event->end_time) 
            : $eventStart->copy()->addHours(3);

        $now = now();
        $acceptedRequests = AssistanceRequest::where('assistant_id', $assistant->id)
            ->where('status', 'accepted')
            ->whereHas('event', function ($q) use ($now) {
                $q->where(function ($q2) use ($now) {
                    $q2->whereNull('end_time')
                       ->orWhere('end_time', '>=', $now);
                });
            })
            ->with('event')
            ->get();

        foreach ($acceptedRequests as $req) {
            $otherEvent = $req->event;
            if (!$otherEvent) continue;

            $otherStart = \Carbon\Carbon::parse($otherEvent->start_time);
            $otherEnd = $otherEvent->end_time 
                ? \Carbon\Carbon::parse($otherEvent->end_time) 
                : $otherStart->copy()->addHours(3);

            if ($eventStart->lt($otherEnd) && $otherStart->lt($eventEnd)) {
                return response()->json([
                    'message' => "هذا المساعد مشغول بالفعل بحدث آخر (\"{$otherEvent->title}\") خلال هذا الوقت."
                ], 422);
            }
        }

        $invitation = AssistanceRequest::create([
            'assistant_id' => $assistant->id,
            'event_id' => $event->id,
            'manager_id' => $user->id,
            'message' => $request->message,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Invitation sent successfully',
            'invitation' => $invitation->load(['assistant:id,name,email', 'event:id,title']),
        ], 201);
    }

    /**
     * GET /api/manager/invitations
     * All invitations sent by this manager, optionally filtered by event_id or status.
     */
    public function getManagerInvitations(Request $request)
    {
        $user = $request->user();
        if ($user->role !== 'Event Manager') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $query = AssistanceRequest::where('manager_id', $user->id)
            ->with([
                'assistant:id,name,email',
                'assistant.profile:id,user_id,logo',
                'event:id,title,start_time,end_time,image',
                'event.venue:id,name',
            ])
            ->orderBy('created_at', 'desc');

        if ($request->has('event_id') && $request->event_id) {
            $query->where('event_id', $request->event_id);
        }

        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        $invitations = $query->get();

        // Also gather per-assistant scan counts for accepted invitations
        $invitations = $invitations->map(function ($inv) {
            $data = $inv->toArray();

            if ($inv->status === 'accepted') {
                $data['scans_count'] = \App\Models\AttendanceLog::where('scanned_by', $inv->assistant_id)
                    ->whereHas('ticket', function ($q) use ($inv) {
                        $q->where('event_id', $inv->event_id);
                    })
                    ->count();
            } else {
                $data['scans_count'] = 0;
            }

            return $data;
        });

        return response()->json($invitations);
    }

    /**
     * DELETE /api/manager/invitations/{id}
     * Cancel a pending invitation.
     */
    public function cancelInvitation(Request $request, $id)
    {
        $user = $request->user();
        if ($user->role !== 'Event Manager') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $invitation = AssistanceRequest::where('id', $id)
            ->where('manager_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if (!$invitation) {
            return response()->json(['message' => 'Invitation not found or cannot be cancelled'], 404);
        }

        $invitation->delete();

        return response()->json(['message' => 'Invitation cancelled']);
    }
}
