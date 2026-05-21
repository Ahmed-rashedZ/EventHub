<?php

namespace App\Http\Controllers;

use App\Models\ExhibitionApplication;
use App\Models\AgreementNegotiation;
use App\Models\AgreementVersion;
use App\Models\Event;
use App\Models\User;
use App\Services\AgreementWordService;
use Illuminate\Http\Request;
use App\Notifications\SystemNotification;

class ExhibitionController extends Controller
{
    // POST /api/exhibition — Company applies or Event Manager invites
    public function store(Request $request)
    {
        $user = $request->user();

        if (!in_array($user->role, ['Company', 'Event Manager'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'event_id'         => 'required|exists:events,id',
            'company_id'       => $user->role === 'Event Manager' ? 'required|exists:users,id' : 'nullable',
            'message'          => 'nullable|string|max:1000',
            'product_category' => 'nullable|string|max:255',
        ]);

        $event = Event::find($request->event_id);

        if (!$event->is_exhibition) {
            return response()->json(['message' => 'This event is not an exhibition'], 400);
        }

        if ($event->status !== 'approved') {
            return response()->json(['message' => 'Applications can only be submitted for approved events'], 400);
        }

        // 1. EVENT MANAGER INITIATED
        if ($user->role === 'Event Manager') {
            if ($event->created_by !== $user->id) {
                return response()->json(['message' => 'Event not found or not yours'], 404);
            }

            $targetCompany = User::with('profile')->find($request->company_id);
            if (!$targetCompany || $targetCompany->role !== 'Company' || !$targetCompany->profile?->is_available) {
                return response()->json(['message' => 'Company is not available'], 400);
            }

            // Duplicate check
            if (ExhibitionApplication::where('event_id', $event->id)->where('company_id', $targetCompany->id)->exists()) {
                return response()->json(['message' => 'An application already exists between this event and company'], 400);
            }

            $app = ExhibitionApplication::create([
                'event_id'         => $event->id,
                'company_id'       => $targetCompany->id,
                'event_manager_id' => $user->id,
                'initiator'        => 'event_manager',
                'message'          => $request->message,
                'booth_preference' => $request->booth_preference,
                'product_category' => $request->product_category,
                'status'           => 'pending',
            ]);

            // Notify company
            $targetCompany->notify(new SystemNotification(
                'دعوة مشاركة في معرض 🏛️',
                "تلقيت دعوة مشاركة في معرض \"{$event->title}\" من {$user->name}.",
                'exhibition',
                '🏛️',
                '/company/applications',
                $event->id
            ));

            return response()->json($app, 201);
        }

        // 2. COMPANY INITIATED
        if ($user->role === 'Company') {
            if ($user->verification_status !== 'verified') {
                return response()->json(['message' => 'Your account must be verified before applying'], 403);
            }

            $profile = $user->profile;
            if (!$profile || !$profile->is_available) {
                return response()->json(['message' => 'You must be available to send applications'], 403);
            }

            if (!$event->canAcceptExhibitorApplications()) {
                return response()->json(['message' => 'Exhibition registration is currently closed or the deadline has passed'], 403);
            }

            // Duplicate check
            if (ExhibitionApplication::where('event_id', $event->id)->where('company_id', $user->id)->exists()) {
                return response()->json(['message' => 'You have already applied for this exhibition'], 400);
            }

            $app = ExhibitionApplication::create([
                'event_id'         => $event->id,
                'company_id'       => $user->id,
                'event_manager_id' => $event->created_by,
                'initiator'        => 'company',
                'message'          => $request->message,
                'booth_preference' => $request->booth_preference,
                'product_category' => $request->product_category,
                'status'           => 'pending',
            ]);

            // Notify event manager
            $manager = User::find($event->created_by);
            if ($manager) {
                $manager->notify(new SystemNotification(
                    'طلب مشاركة جديد في المعرض 🏪',
                    "{$user->name} قدّم طلب مشاركة في معرض \"{$event->title}\".",
                    'exhibition',
                    '🏪',
                    '/manager/exhibition',
                    $event->id
                ));
            }

            return response()->json($app, 201);
        }
    }

    // GET /api/exhibition — List applications by role
    public function index(Request $request)
    {
        $user = $request->user();

        // Auto-reject pending applications for events that have already started
        ExhibitionApplication::where('status', 'pending')
            ->whereHas('event', function ($query) {
                $query->where('start_time', '<=', now());
            })
            ->update(['status' => 'rejected']);

        if ($user->role === 'Company') {
            $apps = ExhibitionApplication::with(['event.venue', 'manager', 'negotiation'])
                ->where('company_id', $user->id)
                ->latest()
                ->get();
        } elseif ($user->role === 'Event Manager') {
            $apps = ExhibitionApplication::with(['event.venue', 'company.profile', 'negotiation', 'booth.zone'])
                ->where('event_manager_id', $user->id)
                ->get()
                ->sortBy(function ($app) {
                    return strtolower($app->event->title ?? 'zzz') . '-' . ($app->status === 'accepted' ? '0' : '1') . '-' . $app->id;
                })
                ->values();
        } elseif ($user->role === 'Admin') {
            $apps = ExhibitionApplication::with(['event', 'company', 'manager', 'negotiation'])->latest()->get();
        } else {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($apps);
    }

    // GET /api/exhibition/{id} — Single application details
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $app = ExhibitionApplication::with(['event.venue', 'company.profile', 'manager', 'negotiation'])->findOrFail($id);

        // Authorization
        if ($user->role === 'Company' && $app->company_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        if ($user->role === 'Event Manager' && $app->event_manager_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($app);
    }

    // PUT /api/exhibition/{id} — Accept or reject
    public function update(Request $request, $id)
    {
        $user = $request->user();
        $app = ExhibitionApplication::findOrFail($id);

        $request->validate(['status' => 'required|in:accepted,rejected']);

        // Bidirectional permission checks
        if ($app->initiator === 'company' && $user->role !== 'Event Manager') {
            return response()->json(['message' => 'Only the Event Manager can respond to this application'], 403);
        }
        if ($app->initiator === 'event_manager' && $user->role !== 'Company') {
            return response()->json(['message' => 'Only the Company can respond to this invitation'], 403);
        }

        // Ownership validation
        if ($user->role === 'Company' && $app->company_id !== $user->id) {
            return response()->json(['message' => 'Not your application'], 403);
        }
        if ($user->role === 'Event Manager' && $app->event_manager_id !== $user->id) {
            return response()->json(['message' => 'Not your application'], 403);
        }

        if ($request->status === 'accepted') {
            // Preliminary acceptance → negotiating + auto-generate agreement
            $app->status = 'negotiating';

            $app->load(['event.venue', 'manager', 'company']);
            $existingNeg = AgreementNegotiation::where('exhibition_application_id', $app->id)->first();
            if (!$existingNeg) {
                $wordPath = AgreementWordService::generateExhibition($app);

                $negotiation = AgreementNegotiation::create([
                    'exhibition_application_id' => $app->id,
                    'status'                    => 'draft',
                    'last_submitted_by'         => $user->id,
                ]);

                AgreementVersion::create([
                    'negotiation_id' => $negotiation->id,
                    'version_number' => 1,
                    'file_path'      => $wordPath,
                    'uploaded_by'    => $user->id,
                    'action'         => 'uploaded',
                    'message'        => 'تم توليد عقد المشاركة الأولي تلقائياً',
                ]);
            }
        } else {
            $app->status = $request->status;
        }

        $app->save();

        // Notify the other party
        $app->load('event');
        $eventTitle = $app->event->title ?? 'Unknown Event';

        if ($app->initiator === 'company') {
            // Manager responded → notify company
            $company = User::find($app->company_id);
            if ($company) {
                $statusText = $request->status === 'accepted' ? 'تم قبول طلبك في المعرض ✅' : 'تم رفض طلبك في المعرض ❌';
                $company->notify(new SystemNotification(
                    $statusText,
                    "طلب مشاركتك في معرض \"{$eventTitle}\" تم " . ($request->status === 'accepted' ? 'قبوله' : 'رفضه') . ".",
                    'exhibition',
                    $request->status === 'accepted' ? '✅' : '❌',
                    '/company/applications',
                    $app->event_id
                ));
            }
        } else {
            // Company responded to manager's invitation → notify manager
            $manager = User::find($app->event_manager_id);
            if ($manager) {
                $companyName = $app->company->name ?? 'شركة';
                $statusText = $request->status === 'accepted' ? 'شركة قبلت الدعوة ✅' : 'شركة رفضت الدعوة ❌';
                $manager->notify(new SystemNotification(
                    $statusText,
                    "{$companyName} " . ($request->status === 'accepted' ? 'قبلت' : 'رفضت') . " دعوة المشاركة في معرض \"{$eventTitle}\".",
                    'exhibition',
                    $request->status === 'accepted' ? '✅' : '❌',
                    '/manager/exhibition',
                    $app->event_id
                ));
            }
        }

        return response()->json($app);
    }

    public function assignBooth(Request $request, $id)
    {
        $user = $request->user();
        if ($user->role !== 'Event Manager') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $app = ExhibitionApplication::with('event')->findOrFail($id);
        if ($app->event_manager_id !== $user->id) {
            return response()->json(['message' => 'Not your application'], 403);
        }

        // New Rule: If already has a booth and within 14 days, block changing/removing
        $currentBoothId = \App\Models\ExhibitionBooth::where('exhibition_application_id', $app->id)->value('id');
        if ($currentBoothId && $app->event->start_time && now()->diffInDays($app->event->start_time, false) < 14) {
             return response()->json(['message' => 'Booth assignment cannot be changed with less than 14 days remaining before the event.'], 400);
        }

        if ($app->status !== 'accepted') {
            return response()->json(['message' => 'Booth can only be assigned after the application is accepted (signed contract)'], 400);
        }

        $request->validate([
            'booth_id' => 'nullable|exists:exhibition_booths,id',
        ]);

        // Clear existing booth assignment for this application
        \App\Models\ExhibitionBooth::where('exhibition_application_id', $app->id)->update(['exhibition_application_id' => null]);

        if ($request->booth_id) {
            $booth = \App\Models\ExhibitionBooth::findOrFail($request->booth_id);
            if ($booth->exhibition_application_id && $booth->exhibition_application_id != $app->id) {
                return response()->json(['message' => 'Booth is already assigned to another company'], 400);
            }
            $booth->update(['exhibition_application_id' => $app->id]);
            
            // Sync legacy fields for compatibility
            $app->update([
                'booth_number' => $booth->booth_number,
                'booth_size'   => $booth->size
            ]);
        } else {
            $app->update([
                'booth_number' => null,
                'booth_size'   => null
            ]);
        }

        return response()->json([
            'message' => 'Booth information updated successfully',
            'app' => $app->load('booth.zone')
        ]);
    }
}
