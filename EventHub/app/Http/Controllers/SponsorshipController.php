<?php

namespace App\Http\Controllers;

use App\Models\SponsorshipRequest;
use App\Models\Event;
use Illuminate\Http\Request;

class SponsorshipController extends Controller
{
    // POST /api/sponsorship  – Event Manager creates a request
    public function store(Request $request)
    {
        if ($request->user()->role !== 'Event Manager') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'event_id' => 'required|exists:events,id',
            'message'  => 'nullable|string|max:1000',
        ]);

        // Ensure the event belongs to this manager
        $event = Event::where('id', $request->event_id)
            ->where('created_by', $request->user()->id)
            ->first();

        if (!$event) {
            return response()->json(['message' => 'Event not found or not yours'], 404);
        }

        // Get first available sponsor (or allow manager to specify)
        $sreq = SponsorshipRequest::create([
            'event_id'   => $request->event_id,
            'sponsor_id' => $request->sponsor_id ?? null, // null = open request
            'message'    => $request->message,
            'status'     => 'pending',
        ]);

        return response()->json($sreq, 201);
    }

    // GET /api/sponsorship  – Sponsor browses open (pending) requests
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->role === 'Sponsor') {
            // Sponsors see pending requests OR their own
            $requests = SponsorshipRequest::with(['event.venue', 'event.creator'])
                ->where(function ($q) use ($user) {
                    $q->where('status', 'pending')
                      ->orWhere('sponsor_id', $user->id);
                })
                ->latest()
                ->get();
        } elseif ($user->role === 'Event Manager') {
            // Managers see their own event requests
            $myEventIds = Event::where('created_by', $user->id)->pluck('id');
            $requests = SponsorshipRequest::with(['event', 'sponsor'])
                ->whereIn('event_id', $myEventIds)
                ->latest()
                ->get();
        } elseif ($user->role === 'Admin') {
            $requests = SponsorshipRequest::with(['event', 'sponsor'])->latest()->get();
        } else {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($requests);
    }

    // PUT /api/sponsorship/{id}  – Sponsor accepts or rejects
    public function update(Request $request, $id)
    {
        $user = $request->user();

        $sreq = SponsorshipRequest::findOrFail($id);

        if ($user->role === 'Sponsor') {
            $request->validate(['status' => 'required|in:accepted,rejected']);
            $sreq->sponsor_id = $user->id;
            $sreq->status = $request->status;
            $sreq->save();

            // Generate Agreement PDF
            if ($request->status === 'accepted') {
                $sreq->load(['event.venue', 'event.creator']);
                $pdfData = [
                    'event' => $sreq->event,
                    'sponsor' => $user,
                    'manager' => $sreq->event->creator,
                    'date' => now()->format('Y-m-d')
                ];
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.agreement', $pdfData);
                $filename = 'agreements/agreement_' . $sreq->id . '.pdf';
                \Illuminate\Support\Facades\Storage::disk('public')->put($filename, $pdf->output());
            }

            return response()->json($sreq);
        }

        return response()->json(['message' => 'Unauthorized'], 403);
    }
}
