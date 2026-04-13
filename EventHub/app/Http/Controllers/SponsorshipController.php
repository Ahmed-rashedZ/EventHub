<?php

namespace App\Http\Controllers;

use App\Models\SponsorshipRequest;
use App\Models\Event;
use Illuminate\Http\Request;

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
                'initiator'  => 'event_manager',
                'message'    => $request->message,
                'status'     => 'pending',
            ]);
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
            return response()->json($sreq, 201);
        }
    }

    // GET /api/sponsorship  – Browse requests
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'Sponsor') {
            $requests = SponsorshipRequest::with(['event.venue', 'manager'])
                ->where('sponsor_id', $user->id)
                ->latest()
                ->get();
        } elseif ($user->role === 'Event Manager') {
            $requests = SponsorshipRequest::with(['event', 'sponsor'])
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
                // If Sponsor is accepting, auto-find an available tier
                $usedTiers = \App\Models\EventSponsor::where('event_id', $sreq->event_id)->pluck('tier')->toArray();
                $availableTiers = array_diff(['diamond', 'gold', 'silver', 'bronze'], $usedTiers);
                if (empty($availableTiers)) {
                    return response()->json(['message' => 'This event has reached its max limit of ranked sponsors (all 4 ranks taken).'], 400);
                }
                $tier = array_pop($availableTiers); // defaults to lowest available (bronze)
            } else {
                $tier = $request->tier ?? 'bronze';
                // Validate unique tier per event
                $existingRank = \App\Models\EventSponsor::where('event_id', $sreq->event_id)
                    ->where('tier', $tier)
                    ->where('sponsor_id', '!=', $sreq->sponsor_id)
                    ->exists();
                    
                if ($existingRank) {
                    return response()->json(['message' => 'This rank ('.ucfirst($tier).') is already assigned to another sponsor for this event. Please select a different rank.'], 400);
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

        return response()->json($sreq);
    }

    // PATCH /api/sponsorship/{id}/tier  – Update sponsor rank
    public function updateTier(Request $request, $id)
    {
        $user = $request->user();
        if ($user->role !== 'Event Manager') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate(['tier' => 'required|in:diamond,gold,silver,bronze']);

        $sreq = SponsorshipRequest::findOrFail($id);

        if ($sreq->event_manager_id !== $user->id) {
            return response()->json(['message' => 'Not your request'], 403);
        }

        if ($sreq->status !== 'accepted') {
            return response()->json(['message' => 'Can only update tier for accepted sponsorships'], 400);
        }

        // Validate unique tier per event
        $existingRank = \App\Models\EventSponsor::where('event_id', $sreq->event_id)
            ->where('tier', $request->tier)
            ->where('sponsor_id', '!=', $sreq->sponsor_id)
            ->exists();
            
        if ($existingRank) {
            return response()->json(['message' => 'This rank ('.ucfirst($request->tier).') is already assigned to another sponsor for this event. Please select an available rank.'], 400);
        }

        \App\Models\EventSponsor::updateOrCreate(
            ['event_id' => $sreq->event_id, 'sponsor_id' => $sreq->sponsor_id],
            ['tier' => $request->tier]
        );

        return response()->json(['message' => 'Sponsor rank updated successfully']);
    }
}
