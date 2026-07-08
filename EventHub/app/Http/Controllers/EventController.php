<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\User;
use Illuminate\Http\Request;
use App\Notifications\SystemNotification;
use App\Http\Traits\ChecksDocumentVerification;
use Carbon\Carbon;

class EventController extends Controller
{
    use ChecksDocumentVerification;
    // GET /api/events  – public approved+published events
    public function index(Request $request)
    {
        $events = Event::with('venue', 'creator:id,name', 'sponsors.profile', 'schedule', 'externalVenue', 'review')
            ->withCount('tickets')
            ->withAvg('ratings', 'rating')
            ->where('status', 'approved')
            ->where('is_published', true)
            ->where(function ($query) {
                $query->where('is_tickets_open', true)
                      ->orWhere('is_exhibitor_registration_open', true);
            })
            ->orderBy('start_time')
            ->get();

        $events->transform(function ($event) {
            return $this->applyPublishedSchedule($event);
        });

        return response()->json($events);
    }

    public function categories()
    {
        return response()->json([
            'مؤتمر', 'ندوة', 'ورشة عمل', 'دورة تدريبية', 'ترفيه', 
            'ملتقى علمي', 'رياضة', 'تقنية', 'اجتماعية', 'معرض'
        ]);
    }

    // GET /api/events/pending  – admin sees pending events
    public function pending(Request $request)
    {
        if ($request->user()->role !== 'Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json(
            Event::with('venue', 'creator:id,name', 'schedule', 'externalVenue', 'review')
                ->withAvg('ratings', 'rating')
                ->where('status', 'pending')
                ->orderBy('created_at', 'desc')
                ->get()
        );
    }

    // GET /api/events/my  – manager's own events
    public function myEvents(Request $request)
    {
        if ($request->user()->role !== 'Event Manager') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json(
            Event::with('venue', 'sponsors.profile', 'schedule', 'externalVenue', 'review')
                ->withCount('tickets')
                ->withAvg('ratings', 'rating')
                ->where('created_by', $request->user()->id)
                ->orderBy('created_at', 'desc')
                ->get()
        );
    }

    // POST /api/events  – Event Manager creates an event
    public function store(Request $request)
    {
        if ($request->user()->role !== 'Event Manager') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Block event creation if partner documents are not fully verified
        if ($request->user()->verification_status !== 'verified') {
            return response()->json([
                'message' => 'لا يمكنك إنشاء فعالية حتى يتم اعتماد جميع وثائقك من قبل الإدارة.',
                'verification_status' => $request->user()->verification_status,
            ], 403);
        }

        // Even if account status is verified, check individual documents
        if (!$this->hasAllDocumentsApproved($request->user())) {
            return $this->ownDocumentsNotApprovedResponse('create_event');
        }

        $request->validate([
            'title'              => 'required|string|max:255',
            'description'        => 'required|string',
            'event_type'         => 'required|string|in:مؤتمر,ندوة,ورشة عمل,دورة تدريبية,ترفيه,ملتقى علمي,رياضة,تقنية,اجتماعية,معرض',
            'location_type'      => 'required|in:internal,external',
            'capacity'           => 'nullable|integer|min:1',
            'image'              => 'required|image|max:2048',
            'ministry_document'  => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'agenda'             => 'required|json',
            'event_objective'    => 'required|string',
            'target_audience'    => 'required|string',
        ]);

        if ($request->location_type === 'internal') {
            $request->validate([
                'venue_id'          => 'required|exists:venues,id',
                'internal_schedule' => 'required|json',
            ]);

            $venue = \App\Models\Venue::find($request->venue_id);
            if ($venue && $request->capacity && $request->capacity > $venue->capacity) {
                return response()->json([
                    'message' => "السعة لا يمكنها أن تتجاوز السعة الإجمالية للمكان {$venue->capacity}.",
                    'errors' => ['capacity' => ["أقصى سعة مسموحة هي {$venue->capacity}."]]
                ], 422);
            }

            $schedule = json_decode($request->internal_schedule, true);
            if (!is_array($schedule) || count($schedule) === 0) {
                return response()->json([
                    'message' => 'Please provide a valid schedule.',
                    'errors' => ['internal_schedule' => ['Schedule cannot be empty.']]
                ], 422);
            }

            $overallStart = null;
            $overallEnd = null;

            foreach ($schedule as &$slot) {
                if (!isset($slot['date'], $slot['period'])) {
                    return response()->json(['message' => 'Invalid schedule format.'], 422);
                }

                $venueMorningStart = $venue->morning_start;
                $venueEveningEnd   = $venue->evening_end;

                if (isset($slot['start_time'], $slot['end_time'])) {
                    $slotStart = \Carbon\Carbon::parse("{$slot['date']} {$slot['start_time']}");
                    $slotEnd   = \Carbon\Carbon::parse("{$slot['date']} {$slot['end_time']}");
                } else {
                    if ($slot['period'] === 'morning') {
                        $slotStart = \Carbon\Carbon::parse("{$slot['date']} {$venue->morning_start}");
                        $slotEnd   = \Carbon\Carbon::parse("{$slot['date']} {$venue->morning_end}");
                    } elseif ($slot['period'] === 'evening') {
                        $slotStart = \Carbon\Carbon::parse("{$slot['date']} {$venue->evening_start}");
                        $slotEnd   = \Carbon\Carbon::parse("{$slot['date']} {$venue->evening_end}");
                    } else {
                        $slotStart = \Carbon\Carbon::parse("{$slot['date']} {$venue->morning_start}");
                        $slotEnd   = \Carbon\Carbon::parse("{$slot['date']} {$venue->evening_end}");
                    }
                }

                if ($slotStart >= $slotEnd) {
                    return response()->json(['message' => "Invalid time range on {$slot['date']}. Start time must be before end time."], 422);
                }

                // Update slot for storage
                $slot['start_time'] = $slotStart->format('H:i');
                $slot['end_time'] = $slotEnd->format('H:i');

                // Venue overlap conflict check
                $overlapping = Event::where('venue_id', $request->venue_id)
                    ->whereIn('status', ['pending', 'approved'])
                    ->where(function ($query) use ($slotStart, $slotEnd) {
                        // For old events (no schedule child) or simple bounds check
                        $query->where(function ($q) use ($slotStart, $slotEnd) {
                            $q->whereDoesntHave('schedule', function ($sq) {
                                  $sq->whereNotNull('internal_schedule');
                              })
                              ->where('start_time', '<', $slotEnd)
                              ->where('end_time', '>', $slotStart);
                        })
                        // For events with internal_schedule in child table
                        ->orWhere(function ($q) use ($slotStart, $slotEnd) {
                            $q->whereHas('schedule', function ($sq) {
                                  $sq->whereNotNull('internal_schedule');
                              })
                              ->where('start_time', '<=', $slotEnd)
                              ->where('end_time', '>=', $slotStart);
                        });
                    })
                    ->with('schedule')
                    ->get();

                foreach ($overlapping as $overlapEvent) {
                    $overlapSchedule = $overlapEvent->schedule?->internal_schedule;
                    if ($overlapSchedule && is_array($overlapSchedule)) {
                        foreach ($overlapSchedule as $exSlot) {
                            if ($exSlot['date'] === $slot['date']) {
                                // Same day, check overlap
                                $exStart = \Carbon\Carbon::parse("{$exSlot['date']} {$exSlot['start_time']}");
                                $exEnd = \Carbon\Carbon::parse("{$exSlot['date']} {$exSlot['end_time']}");
                                if ($slotStart < $exEnd && $slotEnd > $exStart) {
                                    return response()->json([
                                        'message' => 'The selected venue is already booked or requested for another event during this time period.',
                                        'errors' => ['venue_id' => ["Venue is unavailable on {$slot['date']} ({$slot['period']})"]]
                                    ], 422);
                                }
                            }
                        }
                    } else {
                        // It overlapped the old style query
                        return response()->json([
                            'message' => 'The selected venue is already booked or requested for another event during this time period.',
                            'errors' => ['venue_id' => ["Venue is unavailable on {$slot['date']}. (Conflict with existing single-day event)"]]
                        ], 422);
                    }
                }

                if (is_null($overallStart) || $slotStart < $overallStart) {
                    $overallStart = $slotStart;
                }
                if (is_null($overallEnd) || $slotEnd > $overallEnd) {
                    $overallEnd = $slotEnd;
                }
            }

            if ($overallStart < now()->addDays(60)->startOfDay()) {
                return response()->json([
                    'message' => 'Events must be booked at least 60 days in advance.',
                    'errors' => ['internal_schedule' => ['Cannot book earlier than 60 days from today.']]
                ], 422);
            }

            $eventData = [
                'venue_id'     => $request->venue_id,
                'start_time'   => $overallStart,
                'end_time'     => $overallEnd,
            ];

            $scheduleData = [
                'internal_schedule' => $schedule,
            ];
            $externalVenueData = null;

        } else {
            // External Venue
            $request->validate([
                'external_venue_name'     => 'required|string|max:255',
                'external_venue_location' => 'nullable|url|max:500',
                'booking_proof'           => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
                'external_schedule'       => 'required|json',
            ]);

            $schedule = json_decode($request->external_schedule, true);
            if (!is_array($schedule) || count($schedule) === 0) {
                return response()->json([
                    'message' => 'Please provide a valid schedule.',
                    'errors' => ['external_schedule' => ['Schedule cannot be empty.']]
                ], 422);
            }

            $overallStart = null;
            $overallEnd = null;

            foreach ($schedule as $slot) {
                if (!isset($slot['date'], $slot['start_time'], $slot['end_time'])) {
                    return response()->json(['message' => 'Invalid schedule format.'], 422);
                }
                
                $slotStart = \Carbon\Carbon::parse("{$slot['date']} {$slot['start_time']}");
                $slotEnd = \Carbon\Carbon::parse("{$slot['date']} {$slot['end_time']}");

                if ($slotStart >= $slotEnd) {
                    return response()->json(['message' => 'End time must be after start time for each day.', 'errors' => ['external_schedule' => ['Invalid time range on ' . $slot['date']]]], 422);
                }

                if (is_null($overallStart) || $slotStart < $overallStart) {
                    $overallStart = $slotStart;
                }
                if (is_null($overallEnd) || $slotEnd > $overallEnd) {
                    $overallEnd = $slotEnd;
                }
            }

            if ($overallStart < now()->addDays(60)->startOfDay()) {
                return response()->json(['message' => 'Events must be booked at least 60 days in advance.', 'errors' => ['external_schedule' => ['Cannot book earlier than 60 days from today.']]], 422);
            }

            // External venue overlap conflict check (via child table)
            $overlappingEvents = Event::whereHas('externalVenue', function ($q) use ($request) {
                    $q->where('venue_name', $request->external_venue_name);
                })
                ->whereIn('status', ['pending', 'approved'])
                ->where('start_time', '<', $overallEnd)
                ->where('end_time', '>', $overallStart)
                ->with(['schedule', 'externalVenue'])
                ->get();

            foreach ($overlappingEvents as $overlapEvent) {
                $overlapExtSchedule = $overlapEvent->schedule?->external_schedule;
                if ($overlapExtSchedule) {
                    foreach ($overlapExtSchedule as $exSlot) {
                        $exStart = \Carbon\Carbon::parse("{$exSlot['date']} {$exSlot['start_time']}");
                        $exEnd = \Carbon\Carbon::parse("{$exSlot['date']} {$exSlot['end_time']}");
                        
                        foreach ($schedule as $slot) {
                            $slotStart = \Carbon\Carbon::parse("{$slot['date']} {$slot['start_time']}");
                            $slotEnd = \Carbon\Carbon::parse("{$slot['date']} {$slot['end_time']}");
                            
                            if ($slotStart < $exEnd && $slotEnd > $exStart) {
                                return response()->json([
                                    'message' => 'This external hall is already booked or requested for another event during this time period.',
                                    'errors' => ['external_schedule' => ['External hall is unavailable on ' . $slot['date'] . '.']]
                                ], 422);
                            }
                        }
                    }
                } else {
                    foreach ($schedule as $slot) {
                        $slotStart = \Carbon\Carbon::parse("{$slot['date']} {$slot['start_time']}");
                        $slotEnd = \Carbon\Carbon::parse("{$slot['date']} {$slot['end_time']}");
                        
                        if ($slotStart < $overlapEvent->end_time && $slotEnd > $overlapEvent->start_time) {
                            return response()->json([
                                'message' => 'This external hall is already booked or requested for another event during this time period.',
                                'errors' => ['external_schedule' => ['External hall is unavailable on ' . $slot['date'] . '.']]
                            ], 422);
                        }
                    }
                }
            }

            $proofPath = $request->file('booking_proof')->store('proofs', 'public');

            $eventData = [
                'venue_id'     => null,
                'start_time'   => $overallStart,
                'end_time'     => $overallEnd,
            ];

            $scheduleData = [
                'external_schedule' => $schedule,
            ];

            $externalVenueData = [
                'venue_name'        => $request->external_venue_name,
                'venue_location'    => $request->external_venue_location,
                'booking_proof_path' => $proofPath,
            ];
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('events', 'public');
        }

        // Ministry document (required for all events)
        $ministryPath = $request->file('ministry_document')->store('ministry_docs', 'public');
        $scheduleData['ministry_document_path'] = $ministryPath;

        $agendaData = null;
        if ($request->has('agenda') && $request->agenda) {
            $agendaData = json_decode($request->agenda, true);
            $validation = $this->validateAgenda($agendaData, $schedule);
            if ($validation) return $validation;
        }
        $scheduleData['agenda'] = $agendaData;

        $eventData = array_merge($eventData, [
            'title'           => $request->title,
            'description'     => $request->description,
            'event_type'      => $request->event_type,
            'capacity'        => $request->capacity,
            'event_objective' => $request->event_objective,
            'target_audience' => $request->target_audience,
            'status'          => 'pending',
            'created_by'      => $request->user()->id,
            'image'           => $imagePath,
            'is_exhibition'   => ($request->event_type === 'معرض'),
        ]);

        $event = Event::create($eventData);

        // Create child records in normalized tables
        $event->schedule()->create($scheduleData);

        if ($externalVenueData) {
            $event->externalVenue()->create($externalVenueData);
        }

        // ── Notify all Admins about new pending event ──
        $admins = User::where('role', 'Admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new SystemNotification(
                'New Event Pending',
                "Event \"{$event->title}\" was submitted by {$request->user()->name} and needs your approval.",
                'event',
                '📋',
                '/admin/events?eventId=' . $event->id,
                $event->id
            ));
        }

        return response()->json($event->load('venue', 'schedule', 'externalVenue'), 201);
    }

    // PUT /api/events/{id}/approve  – Admin approves
    public function approve($id, Request $request)
    {
        if ($request->user()->role !== 'Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $event = Event::findOrFail($id);
        $event->status = 'approved';
        $event->save();

        if ($event->is_published) {
            $this->notifyInterestedUsers($event);
        }

        // ── Notify the Event Manager ──
        $manager = User::find($event->created_by);
        if ($manager) {
            $manager->notify(new SystemNotification(
                'Event Approved ✅',
                "Your event \"{$event->title}\" has been approved and is now live!",
                'event',
                '✅',
                '/manager/events',
                $event->id
            ));
        }

        return response()->json(['message' => 'Event approved', 'event' => $event]);
    }

    // PUT /api/events/{id}/reject  – Admin rejects
    public function reject($id, Request $request)
    {
        if ($request->user()->role !== 'Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $event = Event::findOrFail($id);
        $event->status = 'rejected';
        $event->save();

        // Store rejection reason in event_reviews table
        $event->review()->updateOrCreate(
            ['event_id' => $event->id],
            ['rejection_reason' => $request->input('rejection_reason')]
        );

        // ── Notify the Event Manager ──
        $manager = User::find($event->created_by);
        if ($manager) {
            $reason = $request->input('rejection_reason') ? ": {$request->input('rejection_reason')}" : '.';
            $manager->notify(new SystemNotification(
                'Event Rejected ❌',
                "Your event \"{$event->title}\" has been rejected{$reason}",
                'event',
                '❌',
                '/manager/events',
                $event->id
            ));
        }

        return response()->json(['message' => 'Event rejected', 'event' => $event]);
    }

    // GET /api/events/{id}  – single event details
    public function show($id)
    {
        $event = Event::with('venue', 'creator:id,name', 'sponsors.profile', 'exhibitors', 'schedule', 'externalVenue', 'review')
            ->withCount('tickets')
            ->withAvg('ratings', 'rating')
            ->findOrFail($id);
            
        // Append sponsorship_request_id to sponsors for contract downloading
        if ($event->relationLoaded('sponsors')) {
            $event->sponsors->map(function ($sponsor) use ($event) {
                $req = \App\Models\SponsorshipRequest::where('event_id', $event->id)
                    ->where('sponsor_id', $sponsor->id)
                    ->where('status', 'accepted')
                    ->first();
                $sponsor->sponsorship_request_id = $req ? $req->id : null;
                return $sponsor;
            });
        }
            
        $event = $this->applyPublishedSchedule($event);

        // Fetch and attach accepted assistants if the user is an Event Manager
        $user = request()->user() ?? auth('sanctum')->user();
        if ($user && $user->role === 'Event Manager') {
            $assistants = \App\Models\AssistanceRequest::where('event_id', $event->id)
                ->where('status', 'accepted')
                ->with('assistant.profile')
                ->get()
                ->map(function ($req) {
                    if (!$req->assistant) return null;
                    return [
                        'id' => $req->assistant->id,
                        'name' => $req->assistant->name,
                        'email' => $req->assistant->email,
                        'logo' => $req->assistant->profile->logo ?? null,
                    ];
                })
                ->filter()
                ->values();

            $event->setAttribute('assistants', $assistants);
        }

        return response()->json($event);
    }

    // GET /api/events/all  – Admin sees all events
    public function all(Request $request)
    {
        if ($request->user()->role !== 'Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json(
            Event::with('venue', 'creator:id,name', 'schedule', 'externalVenue', 'review')
                ->withAvg('ratings', 'rating')
                ->orderBy('created_at', 'desc')
                ->get()
        );
    }
    
    // PATCH /api/events/{id}/toggle-sponsorship
    public function toggleSponsorship($id, Request $request)
    {
        if ($request->user()->role !== 'Event Manager') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $event = Event::findOrFail($id);
        
        // Ensure event belongs to manager
        if ($event->created_by !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        if ($event->status !== 'approved') {
            return response()->json(['message' => 'Cannot open sponsorship for an event that is not approved.'], 400);
        }
        
        $event->is_sponsorship_open = !$event->is_sponsorship_open;
        $event->save();
        
        return response()->json([
            'event' => $event,
            'can_accept_exhibitors' => $event->canAcceptExhibitorApplications()
        ]);
    }

    // PATCH /api/events/{id}/toggle-exhibitor-registration
    public function toggleExhibitorRegistration($id, Request $request)
    {
        if ($request->user()->role !== 'Event Manager') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $event = Event::findOrFail($id);

        if ($event->created_by !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!$event->is_exhibition) {
            return response()->json(['message' => 'This event is not an exhibition'], 400);
        }

        if ($event->status !== 'approved') {
            return response()->json(['message' => 'Cannot toggle registration for an event that is not approved.'], 400);
        }

        // Automatic 30-day block check (Manager can try to open, but logic will block if < 30 days)
        if (!$event->is_exhibitor_registration_open && now()->diffInDays($event->start_time, false) < 30) {
            return response()->json([
                'message' => 'Registration cannot be opened. The 30-day deadline before the event has passed.',
                'can_accept_exhibitors' => false
            ], 400);
        }

        $event->is_exhibitor_registration_open = !$event->is_exhibitor_registration_open;
        $event->save();

        return response()->json([
            'event' => $event,
            'can_accept_exhibitors' => $event->canAcceptExhibitorApplications()
        ]);
    }

    // POST /api/events/{id}/rate  – User rates an event

    // PATCH /api/events/{id}/toggle-applications
    public function toggleApplications($id, Request $request)
    {
        if ($request->user()->role !== 'Event Manager') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $event = Event::findOrFail($id);

        if ($event->created_by !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!$event->is_exhibition) {
            return response()->json(['message' => 'This event is not an exhibition'], 400);
        }

        if ($event->status !== 'approved') {
            return response()->json(['message' => 'Cannot toggle applications for an event that is not approved.'], 400);
        }

        $event->is_applications_open = !$event->is_applications_open;
        $event->save();

        return response()->json($event);
    }


    private function applyPublishedSchedule($event)
    {
        if ($event->published_schedule && is_array($event->published_schedule) && count($event->published_schedule) > 0) {
            $dates = collect($event->published_schedule)->pluck('date')->sort()->values();
            if ($dates->count() > 0) {
                if ($event->agenda && is_array($event->agenda)) {
                    $filteredAgenda = [];
                    foreach ($event->agenda as $date => $items) {
                        if (\Carbon\Carbon::hasFormat($date, 'Y-m-d')) {
                            if ($dates->contains($date)) {
                                $filteredAgenda[$date] = $items;
                            }
                        } else {
                            $filteredAgenda[$date] = $items;
                        }
                    }
                    if (count(array_filter(array_keys($event->agenda), fn($k) => \Carbon\Carbon::hasFormat($k, 'Y-m-d'))) > 0) {
                        $event->agenda = (object)$filteredAgenda;
                    }
                }
            }
        }
        return $event;
    }

    private function validateAgenda($agenda, $schedule = null)
    {
        if (is_array($agenda)) {
            foreach ($agenda as $day => $items) {
                if (is_array($items)) {
                    // Find schedule bounds for this day
                    $daySchedule = null;
                    if ($schedule && is_array($schedule)) {
                        foreach ($schedule as $slot) {
                            if (isset($slot['date']) && $slot['date'] === $day) {
                                $daySchedule = $slot;
                                break;
                            }
                        }
                    }

                    usort($items, function($a, $b) {
                        if (!isset($a['start_time'], $b['start_time'])) return 0;
                        return strcmp($a['start_time'], $b['start_time']);
                    });
                    $prevEnd = null;
                    foreach ($items as $item) {
                        if (!isset($item['start_time'], $item['end_time'])) continue;
                        if ($item['start_time'] >= $item['end_time']) {
                            return response()->json(['message' => "Invalid time in agenda for $day. Start time must be before end time."], 422);
                        }

                        // Bounds check against daily schedule
                        if ($daySchedule && isset($daySchedule['start_time'], $daySchedule['end_time'])) {
                            if ($item['start_time'] < $daySchedule['start_time'] || $item['end_time'] > $daySchedule['end_time']) {
                                $activityName = $item['title'] ?? 'Activity';
                                return response()->json(['message' => "Agenda item \"{$activityName}\" on $day is outside exhibition hours ({$daySchedule['start_time']} - {$daySchedule['end_time']})."], 422);
                            }
                        }

                        if ($prevEnd !== null && $item['start_time'] < $prevEnd) {
                            return response()->json(['message' => "عناصر جدول الأعمال المتداخلة في $day غير مسموحة."], 422);
                        }
                        $prevEnd = $item['end_time'];
                    }
                }
            }
        }
        return null;
    }

    public function rate($id, Request $request)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review_text' => 'nullable|string|max:1000',
        ]);

        $event = Event::findOrFail($id);
        $user = $request->user();

        // Check if user has attended (ticket must have been scanned at least once)
        $ticket = \App\Models\Ticket::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->first();

        $hasAttended = $ticket && (
            $ticket->status === 'used' ||
            \App\Models\AttendanceLog::where('ticket_id', $ticket->id)->exists()
        );

        if (!$hasAttended) {
            return response()->json(['message' => 'You cannot review an event unless you have attended and your ticket has been scanned.'], 403);
        }

        // Check if event has started or passed
        if ($event->time_status === 'upcoming') {
            return response()->json(['message' => 'You cannot review an event that has not started yet.'], 403);
        }

        $rating = \App\Models\Rating::updateOrCreate(
            ['event_id' => $event->id, 'user_id' => $user->id],
            [
                'rating' => $request->rating,
                'review_text' => $request->review_text
            ]
        );

        // ── Notify the Event Manager about new rating ──
        $manager = User::find($event->created_by);
        if ($manager && $manager->id !== $user->id) {
            $stars = str_repeat('⭐', $request->rating);
            $manager->notify(new SystemNotification(
                'New Rating Received',
                "Your event \"{$event->title}\" received a {$request->rating}-star rating {$stars}",
                'event',
                '⭐',
                '/manager/event-stats/' . $event->id,
                $event->id
            ));
        }

        return response()->json([
            'message' => 'Rating submitted successfully',
            'rating' => $rating,
            'average_rating' => $event->fresh()->average_rating
        ]);
    }

    // DELETE /api/events/{id}/rate - Delete user's rating
    public function deleteRating(Request $request, $id)
    {
        $event = Event::findOrFail($id);
        $user = $request->user();

        $rating = \App\Models\Rating::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->first();

        if ($rating) {
            $rating->delete();
            return response()->json([
                'message' => 'Rating deleted successfully',
                'average_rating' => $event->fresh()->average_rating
            ]);
        }

        return response()->json(['message' => 'Rating not found'], 404);
    }


    // GET /api/events/{id}/reviews  – Get reviews for an event
    public function reviews($id)
    {
        $event = Event::withAvg('ratings', 'rating')->findOrFail($id);
        $allRatings = $event->ratings()->with('user:id,name,image,avatar')->orderBy('updated_at', 'desc')->get();
        
        return response()->json([
            'average_rating' => $event->average_rating,
            'reviews' => $allRatings
        ]);
    }

    // PUT /api/events/{id}/review — Admin sends review with field requirements
    public function sendReview($id, Request $request)
    {
        if ($request->user()->role !== 'Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'review_message' => 'required|string|max:1000',
            'review_fields'  => 'required|array|min:1',
            'review_fields.*' => 'string|in:title,description,event_type,capacity,image,ministry_document,booking_proof,venue,dates,agenda',
        ]);

        $event = Event::findOrFail($id);

        if ($event->status !== 'pending') {
            return response()->json(['message' => 'Only pending events can be reviewed.'], 422);
        }

        // Store review data in event_reviews table
        $event->review()->updateOrCreate(
            ['event_id' => $event->id],
            [
                'review_message' => $request->review_message,
                'review_fields'  => $request->review_fields,
                'review_status'  => 'needs_review',
            ]
        );

        // Notify Event Manager
        $manager = User::find($event->created_by);
        if ($manager) {
            $manager->notify(new SystemNotification(
                'Event Review Required 📝',
                "Your event \"{$event->title}\" needs changes: {$request->review_message}",
                'event',
                '📝',
                '/manager/events',
                $event->id
            ));
        }

        return response()->json(['message' => 'Review sent successfully', 'event' => $event]);
    }

    // PUT /api/events/{id}/update-pending — Manager updates pending event (only allowed fields)
    public function updatePending($id, Request $request)
    {
        if ($request->user()->role !== 'Event Manager') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $event = Event::findOrFail($id);

        if ($event->created_by !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($event->status !== 'pending') {
            return response()->json(['message' => 'Only pending events can be updated.'], 422);
        }

        $eventReview = $event->review;
        if (!$eventReview || $eventReview->review_status !== 'needs_review') {
            return response()->json(['message' => 'No review changes requested for this event.'], 422);
        }

        $allowedFields = $eventReview->review_fields ?? [];
        $updateData = [];

        // Process each allowed field
        if (in_array('title', $allowedFields) && $request->has('title')) {
            $updateData['title'] = $request->title;
        }
        if (in_array('description', $allowedFields) && $request->has('description')) {
            $updateData['description'] = $request->description;
        }
        if (in_array('event_type', $allowedFields) && $request->has('event_type')) {
            $updateData['event_type'] = $request->event_type;
        }
        if (in_array('capacity', $allowedFields) && $request->has('capacity')) {
            $updateData['capacity'] = $request->capacity ? (int) $request->capacity : null;
        }
        if (in_array('event_objective', $allowedFields) && $request->has('event_objective')) {
            $updateData['event_objective'] = $request->event_objective;
        }
        if (in_array('target_audience', $allowedFields) && $request->has('target_audience')) {
            $updateData['target_audience'] = $request->target_audience;
        }
        if (in_array('image', $allowedFields) && $request->hasFile('image')) {
            $updateData['image'] = $request->file('image')->store('events', 'public');
        }
        $hasChildUpdates = false;
        if (in_array('ministry_document', $allowedFields) && $request->hasFile('ministry_document')) {
            $ministryPath = $request->file('ministry_document')->store('ministry_docs', 'public');
            if ($event->schedule) {
                $event->schedule->update(['ministry_document_path' => $ministryPath]);
            } else {
                $event->schedule()->create(['ministry_document_path' => $ministryPath]);
            }
            $hasChildUpdates = true;
        }
        if (in_array('booking_proof', $allowedFields) && $request->hasFile('booking_proof')) {
            $proofPath = $request->file('booking_proof')->store('proofs', 'public');
            if ($event->externalVenue) {
                $event->externalVenue->update(['booking_proof_path' => $proofPath]);
            } else {
                $event->externalVenue()->create(['booking_proof_path' => $proofPath]);
            }
            $hasChildUpdates = true;
        }
        if (in_array('agenda', $allowedFields) && $request->has('agenda')) {
            $agendaData = $request->agenda ? json_decode($request->agenda, true) : null;
            $eventSchedule = $event->schedule;
            $schedule = $eventSchedule ? ($eventSchedule->internal_schedule ?? $eventSchedule->external_schedule) : null;
            $validation = $this->validateAgenda($agendaData, $schedule);
            if ($validation) return $validation;
            if ($event->schedule) {
                $event->schedule->update(['agenda' => $agendaData]);
            } else {
                $event->schedule()->create(['agenda' => $agendaData]);
            }
            $hasChildUpdates = true;
        }

        if (empty($updateData) && !$hasChildUpdates) {
            return response()->json(['message' => 'No valid updates provided.'], 422);
        }

        if (!empty($updateData)) {
            $event->update($updateData);
        }

        // Mark review as completed
        if ($eventReview) {
            $eventReview->update(['review_status' => 'reviewed']);
        }

        // Notify Admins
        $admins = User::where('role', 'Admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new SystemNotification(
                'Event Updated 🔄',
                "Event \"{$event->title}\" was updated by {$request->user()->name} after review.",
                'event',
                '🔄',
                '/admin/events?eventId=' . $event->id,
                $event->id
            ));
        }

        return response()->json(['message' => 'Event updated successfully', 'event' => $event->fresh()->load('venue')]);
    }

    // DELETE /api/events/{id}  – Event Manager deletes a pending event
    public function destroy($id, Request $request)
    {
        if ($request->user()->role !== 'Event Manager') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $event = Event::findOrFail($id);

        if ($event->created_by !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($event->status !== 'pending') {
            return response()->json(['message' => 'You can only delete events that are pending approval.'], 400);
        }

        // Delete associated files if needed (Optional but good practice)
        if ($event->image) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($event->image);
        }
        if ($event->schedule?->ministry_document_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($event->schedule->ministry_document_path);
        }
        if ($event->externalVenue?->booking_proof_path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($event->externalVenue->booking_proof_path);
        }

        $event->delete();

        return response()->json(['message' => 'Event deleted successfully.']);
    }

    // PUT /api/events/{id}/agenda  – Manager updates event agenda
    public function updateAgenda($id, Request $request)
    {
        if ($request->user()->role !== 'Event Manager') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $event = Event::findOrFail($id);

        if ($event->created_by !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($event->time_status === 'ended') {
            return response()->json(['message' => 'Cannot update agenda of an ended event.'], 422);
        }

        $request->validate([
            'agenda' => 'present|nullable',
        ]);

        $agenda = $request->agenda;

        $eventSchedule = $event->schedule;
        $schedule = $eventSchedule ? ($eventSchedule->internal_schedule ?? $eventSchedule->external_schedule) : null;
        $validation = $this->validateAgenda($agenda, $schedule);
        if ($validation) return $validation;

        // Normalize: if it's a flat array (legacy), wrap it
        if (is_array($agenda) && !empty($agenda) && isset($agenda[0])) {
            // Legacy flat format — keep as-is for backward compat
        }

        if ($eventSchedule) {
            $eventSchedule->update(['agenda' => $agenda]);
        } else {
            $event->schedule()->create(['agenda' => $agenda]);
        }

        return response()->json(['message' => 'Agenda updated successfully', 'event' => $event->fresh()->load('venue', 'schedule', 'externalVenue')]);
    }

    public function downloadDocument($id, $type, Request $request)
    {
        $event = Event::with(['schedule', 'externalVenue'])->findOrFail($id);
        $user = $request->user();

        // Security check: Only Admin, or the Manager who created the event, or a Sponsor of this event can download
        $isCreator = $event->created_by === $user->id;
        $isAdmin = $user->role === 'Admin';
        $isSponsor = $event->sponsors()->where('sponsor_id', $user->id)->exists();

        if (!$isAdmin && !$isCreator && !$isSponsor) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $path = null;
        if ($type === 'ministry_document') {
            $path = $event->schedule?->ministry_document_path;
        } elseif ($type === 'booking_proof') {
            $path = $event->externalVenue?->booking_proof_path;
        }

        if (!$path || !\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
            return response()->json(['message' => 'Document not found'], 404);
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->download($path);
    }

    // POST /api/events/{id}/cancel-request  – Manager requests cancellation
    public function requestCancellation($id, Request $request)
    {
        if ($request->user()->role !== 'Event Manager') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'cancellation_reason' => 'required|string|max:2000',
        ]);

        $event = Event::findOrFail($id);

        if ($event->created_by !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($event->status !== 'approved') {
            return response()->json(['message' => 'Only approved events can be cancelled.'], 400);
        }

        // Restriction: Cannot cancel if less than 30 days away
        $eventDate = Carbon::parse($event->start_time);
        if ($eventDate->isPast()) {
             return response()->json(['message' => app()->getLocale() == 'ar' ? 'لا يمكن إلغاء فعالية بدأت بالفعل.' : 'Cannot cancel an event that has already started.'], 400);
        }
        
        if (now()->diffInDays($eventDate, absolute: true) < 30) {
            return response()->json(['message' => app()->getLocale() == 'ar' ? 'لا يمكن طلب إلغاء الفعاليات التي تبدأ بعد أقل من 30 يوماً.' : 'Cannot request cancellation for events starting in less than 30 days.'], 400);
        }

        $event->update([
            'status' => 'cancellation_requested',
            'is_tickets_open' => false, // Suspend ticket sales immediately
            'is_sponsorship_open' => false, // Suspend sponsorship too
        ]);

        // Store cancellation reason in event_reviews table
        $event->review()->updateOrCreate(
            ['event_id' => $event->id],
            ['cancellation_reason' => $request->cancellation_reason]
        );

        // Notify Admins
        $admins = User::where('role', 'Admin')->get();
        foreach ($admins as $admin) {
            $admin->notify(new SystemNotification(
                'Cancellation Requested ⚠️',
                "Manager {$request->user()->name} requested to cancel \"{$event->title}\".",
                'event',
                '⚠️',
                '/admin/events?eventId=' . $event->id,
                $event->id
            ));
        }

        return response()->json(['message' => 'Cancellation request submitted', 'event' => $event]);
    }

    // PUT /api/events/{id}/cancel-approve  – Admin approves cancellation
    public function approveCancellation($id, Request $request)
    {
        if ($request->user()->role !== 'Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $event = Event::findOrFail($id);

        if ($event->status !== 'cancellation_requested') {
            return response()->json(['message' => 'Event is not in cancellation request state.'], 400);
        }

        $event->update([
            'status' => 'cancelled',
        ]);

        // Notify Manager
        $manager = User::find($event->created_by);
        if ($manager) {
            $manager->notify(new SystemNotification(
                'Cancellation Approved 🚫',
                "Your cancellation request for \"{$event->title}\" has been approved.",
                'event',
                '🚫',
                '/manager/events',
                $event->id
            ));
        }

        // Notify and update Sponsors
        $sponsorshipRequests = \App\Models\SponsorshipRequest::where('event_id', $event->id)
            ->whereIn('status', ['accepted', 'negotiating', 'pending'])
            ->get();

        foreach ($sponsorshipRequests as $sreq) {
            $sreq->update(['status' => 'cancelled']);
            
            $sponsor = User::find($sreq->sponsor_id);
            if ($sponsor) {
                $sponsor->notify(new SystemNotification(
                    'Event Cancelled 🚫',
                    "The event \"{$event->title}\" which you were sponsoring has been cancelled.",
                    'event',
                    '🚫',
                    '/sponsor/requests',
                    $event->id
                ));
            }
        }

        // Notify and update Ticket Holders
        $tickets = \App\Models\Ticket::where('event_id', $event->id)
            ->where('status', '!=', 'cancelled')
            ->get();

        foreach ($tickets as $ticket) {
            $ticket->update(['status' => 'cancelled']);
            
            $attendee = User::find($ticket->user_id);
            if ($attendee) {
                $attendee->notify(new SystemNotification(
                    'Event Cancelled 🚫',
                    "We're sorry, the event \"{$event->title}\" for which you have a ticket has been cancelled.",
                    'event',
                    '🚫',
                    '/user/my-tickets',
                    $event->id
                ));
            }
        }

        return response()->json(['message' => 'Event cancelled successfully', 'event' => $event]);
    }

    // PUT /api/events/{id}/cancel-reject  – Admin rejects cancellation
    public function rejectCancellation($id, Request $request)
    {
        if ($request->user()->role !== 'Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:2000',
        ]);

        $event = Event::findOrFail($id);

        if ($event->status !== 'cancellation_requested') {
            return response()->json(['message' => 'Event is not in cancellation request state.'], 400);
        }

        $event->update([
            'status' => 'approved', // Return to approved
            // is_tickets_open stays false!
        ]);

        // Store rejection reason in event_reviews table
        $event->review()->updateOrCreate(
            ['event_id' => $event->id],
            ['cancellation_rejection_reason' => $request->rejection_reason]
        );

        // Notify Manager
        $manager = User::find($event->created_by);
        if ($manager) {
            $manager->notify(new SystemNotification(
                'Cancellation Rejected ⚠️',
                "Your cancellation request for \"{$event->title}\" was rejected: {$request->rejection_reason}",
                'event',
                '⚠️',
                '/manager/events',
                $event->id
            ));
        }

        return response()->json(['message' => 'Cancellation request rejected', 'event' => $event]);
    }

    // PATCH /api/events/{id}/toggle-tickets
    public function toggleTickets($id, Request $request)
    {
        if ($request->user()->role !== 'Event Manager') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $event = Event::findOrFail($id);

        if ($event->created_by !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($event->time_status === 'ended') {
            return response()->json(['message' => 'Cannot toggle tickets for an ended event.'], 400);
        }

        if (!in_array($event->status, ['approved', 'cancellation_requested'])) {
            return response()->json(['message' => 'Cannot toggle tickets for this event status.'], 400);
        }

        $event->is_tickets_open = !$event->is_tickets_open;
        $event->save();

        return response()->json($event);
    }

    public function updatePublishedSchedule(Request $request, $id)
    {
        $event = Event::findOrFail($id);
        
        if ($request->user()->id !== $event->created_by && $request->user()->role !== 'Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($event->time_status === 'ended') {
            return response()->json(['message' => 'Cannot update published schedule of an ended event.'], 422);
        }

        $request->validate([
            'published_schedule' => 'required|array',
            'publish' => 'sometimes|boolean',
        ]);

        $updateData = ['published_schedule' => $request->published_schedule];
        $wasPublished = $event->is_published;

        // Update published_schedule in the schedule child table
        $eventSchedule = $event->schedule;
        if ($eventSchedule) {
            $eventSchedule->update(['published_schedule' => $request->published_schedule]);
        } else {
            $event->schedule()->create(['published_schedule' => $request->published_schedule]);
        }

        // Calculate and update the overall start_time and end_time in the events table
        $dates = collect($request->published_schedule)->pluck('date')->sort()->values();
        if ($dates->count() > 0) {
            $firstDate = $dates->first();
            $lastDate = $dates->last();
            
            $firstSlot = collect($request->published_schedule)->where('date', $firstDate)->first();
            $lastSlot = collect($request->published_schedule)->where('date', $lastDate)->first();
            
            $startTimeStr = isset($firstSlot['start_time']) ? $firstSlot['start_time'] : Carbon::parse($event->start_time)->format('H:i:s');
            $endTimeStr = isset($lastSlot['end_time']) ? $lastSlot['end_time'] : Carbon::parse($event->end_time)->format('H:i:s');

            $newStartTime = Carbon::parse($firstDate . ' ' . $startTimeStr);
            $newEndTime = Carbon::parse($lastDate . ' ' . $endTimeStr);
            
            $event->update([
                'start_time' => $newStartTime,
                'end_time' => $newEndTime,
            ]);

            // Clear previously sent event reminders so that they can be resent at the new schedule times
            \App\Models\EventReminder::where('event_id', $event->id)->delete();
        }

        // is_published stays on the parent event
        if ($request->has('publish')) {
            $event->update(['is_published' => $request->publish]);
        }

        if ($request->has('publish') && $request->publish && !$wasPublished && $event->status === 'approved') {
            $this->notifyInterestedUsers($event);
        }

        return response()->json([
            'message' => 'Published schedule updated successfully.',
            'event' => $event->fresh()->load('schedule')
        ]);
    }

    /**
     * Update only the capacity of an event (Expansion feature).
     * Accessible by the manager or Admin.
     */
    public function updateCapacity($id, Request $request)
    {
        $request->validate([
            'capacity' => 'nullable|integer|min:1',
        ]);

        $event = Event::with('venue')->findOrFail($id);
        $user = $request->user();

        // Check ownership or admin role
        if ($event->created_by !== $user->id && $user->role !== 'Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($event->time_status === 'ended') {
            return response()->json(['message' => 'Cannot update capacity of an ended event.'], 422);
        }

        $newCapacity = $request->capacity !== null ? (int) $request->capacity : null;
        $bookedCount = $event->tickets()->count();

        // Validate against booked tickets
        if ($newCapacity !== null && $newCapacity < $bookedCount) {
            return response()->json([
                'message' => "Cannot decrease capacity below the number of booked tickets ({$bookedCount}).",
                'errors' => ['capacity' => ["Minimum required capacity is {$bookedCount}."]]
            ], 422);
        }

        // Validate against venue capacity (if internal venue)
        if ($event->venue && $newCapacity !== null && $newCapacity > $event->venue->capacity) {
            return response()->json([
                'message' => "Capacity cannot exceed the venue's total capacity of {$event->venue->capacity}.",
                'errors' => ['capacity' => ["Maximum allowed capacity for this venue is {$event->venue->capacity}."]]
            ], 422);
        }

        $event->update(['capacity' => $newCapacity]);

        return response()->json([
            'message' => 'Capacity updated successfully.',
            'event' => $event->loadCount('tickets')
        ]);
    }

    private function notifyInterestedUsers(Event $event)
    {
        $users = \App\Models\User::whereNotNull('interests')->get();
            
        foreach ($users as $user) {
            $interests = is_array($user->interests) ? $user->interests : [];
            if (!in_array($event->event_type, $interests)) continue;

            $user->notify(new SystemNotification(
                'فعالية جديدة تهمك! 🎉',
                "تم نشر فعالية جديدة \"{$event->title}\" من نوع {$event->event_type}.",
                'event',
                '🎉',
                '/user/events/' . $event->id,
                $event->id
            ));
        }
    }

    /**
     * Map Arabic event types (used in Laravel) to English types (used in the AI model).
     */
    private function mapEventTypeToAI(string $arabicType): string
    {
        $map = [
            'مؤتمر'        => 'Conference',
            'ندوة'          => 'Seminar',
            'ورشة عمل'     => 'Workshop',
            'دورة تدريبية' => 'Course',
            'ترفيه'         => 'Entertainment',
            'معرض'          => 'Exhibition',
            'ملتقى علمي'   => 'Conference',   // closest match
            'رياضة'         => 'Entertainment', // closest match
            'تقنية'         => 'Conference',    // closest match
            'اجتماعية'      => 'Meeting',       // closest match
        ];

        return $map[$arabicType] ?? 'Conference';
    }

    /**
     * POST /api/events/predict-attendance
     * 
     * Calls the Python AI microservice to predict attendance based on
     * event type, total days, weekend inclusion, time period, and capacity.
     */
    public function predictAttendance(Request $request)
    {
        $request->validate([
            'event_type'       => 'required|string',
            'total_days'       => 'required|integer|min:1',
            'includes_weekend' => 'required|integer|in:0,1',
            'time_period'      => 'required|string|in:Morning,Evening',
        ]);

        $aiUrl = env('EVENTHUB_AI_URL', 'http://127.0.0.1:8001');

        // Map Arabic event type to English for the AI model
        $eventTypeEN = $this->mapEventTypeToAI($request->event_type);

        $payload = [
            'Event_Type'       => $eventTypeEN,
            'Total_Days'       => $request->total_days,
            'Includes_Weekend' => $request->includes_weekend,
            'Time_Period'      => $request->time_period,
        ];

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(10)
                ->post("{$aiUrl}/predict", $payload);

            if ($response->successful()) {
                return response()->json([
                    'status'               => 'success',
                    'predicted_attendance'  => $response->json('predicted_attendance'),
                    'predicted_lower'      => $response->json('predicted_lower'),
                    'predicted_upper'      => $response->json('predicted_upper'),
                    'event_type_mapped'    => $eventTypeEN,
                ]);
            }

            return response()->json([
                'status'  => 'error',
                'message' => 'AI service returned an error.',
                'details' => $response->json(),
            ], 502);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Could not connect to AI service. Make sure it is running.',
                'error'   => $e->getMessage(),
            ], 503);
        }
    }

    /**
     * POST /api/events/generate-description
     * 
     * Calls the Python AI microservice to generate an event description
     * based on the title using Groq/Llama API.
     */
    public function generateDescription(Request $request)
    {
        $request->validate([
            'title'      => 'required|string|min:2|max:200',
            'event_type' => 'nullable|string|max:50',
        ]);

        $aiUrl = env('EVENTHUB_AI_URL', 'http://127.0.0.1:8001');

        $payload = [
            'title' => $request->title,
        ];

        if ($request->event_type) {
            $payload['event_type'] = $request->event_type;
        }

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(90)
                ->post("{$aiUrl}/generate-description", $payload);

            if ($response->successful()) {
                return response()->json([
                    'status'      => 'success',
                    'description' => $response->json('description'),
                    'title'       => $request->title,
                ]);
            }

            // Pass through rate limit errors from AI service
            $statusCode = $response->status() === 429 ? 429 : 502;
            $detail = $response->json('detail') ?? 'AI service returned an error.';

            return response()->json([
                'status'  => 'error',
                'message' => $detail,
                'detail'  => $detail,
            ], $statusCode);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Could not connect to AI service. Make sure it is running.',
                'error'   => $e->getMessage(),
            ], 503);
        }
    }
}
