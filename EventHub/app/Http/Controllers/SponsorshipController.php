<?php

namespace App\Http\Controllers;

use App\Models\SponsorshipRequest;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use App\Notifications\SystemNotification;

class SponsorshipController extends Controller
{
    // POST /api/sponsorship  – Create a request (Bidirectional)
    public function store(Request $request)
    {
        $user = $request->user();

        if (!in_array($user->role, ['Event Manager', 'Sponsor'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'event_id' => 'required|exists:events,id',
            'sponsor_id' => $user->role === 'Event Manager' ? 'required|exists:users,id' : 'nullable|exists:users,id',
            'message'  => 'nullable|string|max:1000',
        ]);

        $event = Event::find($request->event_id);
        
        // 1. EVENT MANAGER INITIATED
        if ($user->role === 'Event Manager') {
            if ($event->created_by !== $user->id) {
                return response()->json(['message' => 'Event not found or not yours'], 404);
            }
            
            $targetSponsor = \App\Models\User::with('profile')->find($request->sponsor_id);
            if ($targetSponsor->role !== 'Sponsor' || !$targetSponsor->profile?->is_available) {
                return response()->json(['message' => 'Sponsor is not available'], 400);
            }
            
            // Duplicate Check
            if (SponsorshipRequest::where('event_id', $event->id)->where('sponsor_id', $targetSponsor->id)->exists()) {
                return response()->json(['message' => 'A sponsorship request already exists between this event and sponsor'], 400);
            }
            
            $sreq = SponsorshipRequest::create([
                'event_id'   => $event->id,
                'sponsor_id' => $targetSponsor->id,
                'event_manager_id' => $user->id,
                'message'    => $request->message,
                'status'     => 'pending',
                'initiator'  => 'event_manager',
            ]);

            // ── Notify the Sponsor about the invitation ──
            $targetSponsor->notify(new SystemNotification(
                'Sponsorship Invitation 💼',
                "You received a sponsorship invitation for \"{$event->title}\" from {$user->name}.",
                'sponsorship',
                '💼',
                '/sponsor/requests',
                $event->id
            ));

            return response()->json($sreq, 201);
        }

        // 2. SPONSOR INITIATED
        if ($user->role === 'Sponsor') {
            $profile = $user->profile;
            if (!$profile || !$profile->is_available) {
                return response()->json(['message' => 'You must be available to send requests'], 403);
            }
            
            if (!$event->is_sponsorship_open) {
                return response()->json(['message' => 'This event is currently closed to new sponsorship requests.'], 403);
            }

            // Duplicate Check
            if (SponsorshipRequest::where('event_id', $event->id)->where('sponsor_id', $user->id)->exists()) {
                return response()->json(['message' => 'You have already sent a request for this event'], 400);
            }

            $sreq = SponsorshipRequest::create([
                'event_id'   => $event->id,
                'sponsor_id' => $user->id,
                'event_manager_id' => $event->created_by,
                'initiator'  => 'sponsor',
                'message'    => $request->message,
                'status'     => 'pending',
            ]);

            // ── Notify the Event Manager about the request ──
            $manager = User::find($event->created_by);
            if ($manager) {
                $sponsorName = $user->name;
                $manager->notify(new SystemNotification(
                    'New Sponsorship Request 🤝',
                    "{$sponsorName} sent a sponsorship request for \"{$event->title}\".",
                    'sponsorship',
                    '🤝',
                    '/manager/sponsorship',
                    $event->id
                ));
            }

            return response()->json($sreq, 201);
        }
    }

    // GET /api/sponsorship  – Browse requests
    public function index(Request $request)
    {
        $user = $request->user();

        // Auto-reject pending sponsorship requests if the event has already started
        SponsorshipRequest::where('status', 'pending')
            ->whereHas('event', function($query) {
                $query->where('start_time', '<=', now());
            })
            ->update(['status' => 'rejected']);

        if ($user->role === 'Sponsor') {
            $requests = SponsorshipRequest::with(['event.venue', 'manager'])
                ->where('sponsor_id', $user->id)
                ->latest()
                ->get();
        } elseif ($user->role === 'Event Manager') {
            $requests = SponsorshipRequest::with(['event.sponsors', 'sponsor'])
                ->where('event_manager_id', $user->id)
                ->get()
                ->sortBy(function($req) {
                    // Sort by Event Title alphabetically, then accepted first, then ID
                    return strtolower($req->event->title ?? 'zzz') . '-' . ($req->status === 'accepted' ? '0' : '1') . '-' . $req->id;
                })
                ->values();
        } elseif ($user->role === 'Admin') {
            $requests = SponsorshipRequest::with(['event', 'sponsor', 'manager'])->latest()->get();
        } else {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($requests);
    }

    // PUT /api/sponsorship/{id}  – Accept or reject
    public function update(Request $request, $id)
    {
        $user = $request->user();
        $sreq = SponsorshipRequest::findOrFail($id);

        $request->validate(['status' => 'required|in:accepted,rejected']);

        // Check bidirectional permissions
        if ($sreq->initiator === 'sponsor' && $user->role !== 'Event Manager') {
            return response()->json(['message' => 'Only the Event Manager can respond to this request'], 403);
        }
        
        if ($sreq->initiator === 'event_manager' && $user->role !== 'Sponsor') {
            return response()->json(['message' => 'Only the Sponsor can respond to this request'], 403);
        }

        // Validate ownership
        if ($user->role === 'Sponsor' && $sreq->sponsor_id !== $user->id) {
            return response()->json(['message' => 'Not your request'], 403);
        }
        if ($user->role === 'Event Manager' && $sreq->event_manager_id !== $user->id) {
            return response()->json(['message' => 'Not your request'], 403);
        }

        // Update status
        $sreq->status = $request->status;
        if ($sreq->status === 'accepted') {
            if ($user->role === 'Sponsor') {
                // Sponsor accepting a manager-initiated request → unranked (manager decides tier later)
                $tier = null;
            } else {
                // Manager accepting a sponsor-initiated request → optional tier
                $tier = $request->tier ?: null; // null means unranked
                
                // Validate unique tier per event (only for non-null tiers)
                if ($tier !== null) {
                    $existingRank = \App\Models\EventSponsor::where('event_id', $sreq->event_id)
                        ->where('tier', $tier)
                        ->where('sponsor_id', '!=', $sreq->sponsor_id)
                        ->exists();
                        
                    if ($existingRank) {
                        return response()->json(['message' => 'This rank ('.ucfirst($tier).') is already assigned to another sponsor for this event. Please select a different rank.'], 400);
                    }
                }
            }

            // Attach sponsor to event
            \App\Models\EventSponsor::updateOrCreate([
                'event_id' => $sreq->event_id,
                'sponsor_id' => $sreq->sponsor_id,
            ], [
                'tier' => $tier,
                'contribution_amount' => $request->contribution_amount ?? 0
            ]);

            // Generate Agreement PDF
            $sreq->load(['event.venue', 'manager', 'sponsor']);
            $pdfData = [
                'event' => $sreq->event,
                'sponsor' => $sreq->sponsor,
                'manager' => $sreq->manager,
                'date' => now()->format('Y-m-d')
            ];
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.agreement', $pdfData);
            $filename = 'agreements/agreement_' . $sreq->id . '.pdf';
            \Illuminate\Support\Facades\Storage::disk('public')->put($filename, $pdf->output());
        }
        
        $sreq->save();

        // ── Notify the other party about the decision ──
        $sreq->load('event');
        $eventTitle = $sreq->event->title ?? 'Unknown Event';

        if ($sreq->initiator === 'sponsor') {
            // Manager responded to sponsor’s request → notify sponsor
            $sponsor = User::find($sreq->sponsor_id);
            if ($sponsor) {
                $statusText = $request->status === 'accepted' ? 'accepted ✅' : 'rejected ❌';
                $sponsor->notify(new SystemNotification(
                    "Sponsorship {$statusText}",
                    "Your sponsorship request for \"{$eventTitle}\" has been {$request->status}.",
                    'sponsorship',
                    $request->status === 'accepted' ? '✅' : '❌',
                    '/sponsor/requests',
                    $sreq->event_id
                ));
            }
        } else {
            // Sponsor responded to manager’s invitation → notify manager
            $manager = User::find($sreq->event_manager_id);
            if ($manager) {
                $sponsorName = $sreq->sponsor->name ?? 'A sponsor';
                $statusText = $request->status === 'accepted' ? 'accepted ✅' : 'rejected ❌';
                $manager->notify(new SystemNotification(
                    "Invitation {$statusText}",
                    "{$sponsorName} has {$request->status} your sponsorship invitation for \"{$eventTitle}\".",
                    'sponsorship',
                    $request->status === 'accepted' ? '✅' : '❌',
                    '/manager/sponsorship',
                    $sreq->event_id
                ));
            }
        }

        return response()->json($sreq);
    }

    // PATCH /api/sponsorship/{id}/tier  – Update sponsor rank
    public function updateTier(Request $request, $id)
    {
        $user = $request->user();
        if ($user->role !== 'Event Manager') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate(['tier' => 'nullable|in:diamond,gold,silver,bronze']);

        $sreq = SponsorshipRequest::findOrFail($id);

        if ($sreq->event_manager_id !== $user->id) {
            return response()->json(['message' => 'Not your request'], 403);
        }

        if ($sreq->status !== 'accepted') {
            return response()->json(['message' => 'Can only update tier for accepted sponsorships'], 400);
        }

        $tier = $request->tier ?: null; // empty string or null → unranked

        // Validate unique tier per event (only for non-null tiers)
        if ($tier !== null) {
            $existingRank = \App\Models\EventSponsor::where('event_id', $sreq->event_id)
                ->where('tier', $tier)
                ->where('sponsor_id', '!=', $sreq->sponsor_id)
                ->exists();
                
            if ($existingRank) {
                return response()->json(['message' => 'This rank ('.ucfirst($tier).') is already assigned to another sponsor for this event. Please select an available rank.'], 400);
            }
        }

        \App\Models\EventSponsor::updateOrCreate(
            ['event_id' => $sreq->event_id, 'sponsor_id' => $sreq->sponsor_id],
            ['tier' => $tier]
        );

        return response()->json(['message' => 'Sponsor rank updated successfully']);
    }
}
