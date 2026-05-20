<?php

namespace App\Http\Controllers;

use App\Models\AgreementNegotiation;
use App\Models\AgreementVersion;
use App\Models\SponsorshipRequest;
use App\Models\User;
use App\Notifications\SystemNotification;
use App\Services\AgreementWordService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AgreementController extends Controller
{
    public function show(Request $request, $id)
    {
        $user = $request->user();
        $type = $request->query('type', 'sponsor');
        $target = $this->getTarget($id, $type);

        if (!$target) return response()->json(['message' => 'Target not found'], 404);
        if (!$this->canAccess($user, $target)) return response()->json(['message' => 'Unauthorized'], 403);

        $query = AgreementNegotiation::with(['versions.uploader', 'lastSubmitter']);
        if ($type === 'exhibition') {
            $query->where('exhibition_application_id', $id);
        } else {
            $query->where('sponsorship_request_id', $id);
        }
        $negotiation = $query->first();

        if (!$negotiation) {
            return response()->json(['message' => 'No agreement negotiation found'], 404);
        }

        return response()->json([
            'negotiation' => $negotiation,
            'sponsorship_request' => $type === 'sponsor' ? $target->load(['event', 'sponsor', 'manager']) : null,
            'exhibition_application' => $type === 'exhibition' ? $target->load(['event', 'company', 'manager']) : null,
        ]);
    }

    public function generate(Request $request, $id)
    {
        $user = $request->user();
        $type = $request->query('type', 'sponsor');
        $target = $this->getTarget($id, $type);

        if (!$target) return response()->json(['message' => 'Target not found'], 404);
        if (!$this->canAccess($user, $target)) return response()->json(['message' => 'Unauthorized'], 403);

        if (!in_array($target->status, ['accepted', 'negotiating'])) {
            return response()->json(['message' => 'Agreement can only be generated for accepted targets'], 400);
        }

        // Check if negotiation already exists
        $query = AgreementNegotiation::query();
        if ($type === 'exhibition') {
            $query->where('exhibition_application_id', $id);
        } else {
            $query->where('sponsorship_request_id', $id);
        }
        $existing = $query->first();

        if ($existing) {
            return response()->json(['message' => 'Agreement already generated', 'negotiation' => $existing->load('versions.uploader')], 200);
        }

        // Generate Word document
        if ($type === 'exhibition') {
            $filePath = AgreementWordService::generateExhibition($target);
        } else {
            $filePath = AgreementWordService::generate($target);
        }

        // Create negotiation record
        $negotiationData = [
            'status'            => 'draft',
            'last_submitted_by' => $user->id,
        ];
        if ($type === 'exhibition') {
            $negotiationData['exhibition_application_id'] = $id;
        } else {
            $negotiationData['sponsorship_request_id'] = $id;
        }
        $negotiation = AgreementNegotiation::create($negotiationData);

        // Create first version
        AgreementVersion::create([
            'negotiation_id' => $negotiation->id,
            'version_number' => 1,
            'file_path'      => $filePath,
            'uploaded_by'    => $user->id,
            'action'         => 'uploaded',
            'message'        => 'تم توليد العقد الأولي تلقائياً',
        ]);

        $negotiation->load('versions.uploader');

        // Notify the other party
        $managerId = $target->event_manager_id;
        $partnerId = ($type === 'exhibition') ? $target->company_id : $target->sponsor_id;
        $otherUserId = ($user->id === $partnerId) ? $managerId : $partnerId;
        $otherUser = User::find($otherUserId);
        
        if ($otherUser) {
            $otherUser->notify(new SystemNotification(
                'عقد جديد 📋',
                "تم إنشاء عقد لحدث \"{$target->event->title}\". يرجى مراجعة العقد وتعديل البنود.",
                'agreement',
                '📋',
                $otherUser->role === 'Event Manager' ? '/manager/sponsorship' : ($type === 'exhibition' ? '/company/applications' : '/sponsor/requests'),
                $target->event_id
            ));
        }

        return response()->json($negotiation, 201);
    }

    public function download(Request $request, $id, $version = null)
    {
        $user = $request->user();
        $type = $request->query('type', 'sponsor');
        $target = $this->getTarget($id, $type);

        if (!$target) return response()->json(['message' => 'Target not found'], 404);
        if (!$this->canAccess($user, $target)) return response()->json(['message' => 'Unauthorized'], 403);

        $query = AgreementNegotiation::query();
        if ($type === 'exhibition') {
            $query->where('exhibition_application_id', $id);
        } else {
            $query->where('sponsorship_request_id', $id);
        }
        $negotiation = $query->firstOrFail();

        if ($version) {
            $ver = AgreementVersion::where('negotiation_id', $negotiation->id)
                ->where('version_number', $version)
                ->firstOrFail();
        } else {
            $ver = $negotiation->latestVersion;
        }

        if (!$ver || !Storage::disk('public')->exists($ver->file_path)) {
            return response()->json(['message' => 'File not found'], 404);
        }

        return Storage::disk('public')->download($ver->file_path);
    }

    public function downloadFinal(Request $request, $id)
    {
        $user = $request->user();
        $type = $request->query('type', 'sponsor');
        $target = $this->getTarget($id, $type);

        if (!$target) return response()->json(['message' => 'Target not found'], 404);
        if (!$this->canAccess($user, $target)) return response()->json(['message' => 'Unauthorized'], 403);

        $query = AgreementNegotiation::query();
        if ($type === 'exhibition') {
            $query->where('exhibition_application_id', $id);
        } else {
            $query->where('sponsorship_request_id', $id);
        }
        $negotiation = $query->firstOrFail();

        if ($negotiation->status !== 'accepted') {
            return response()->json(['message' => 'Agreement not finalized yet'], 400);
        }

        $lastUploadedVersion = $negotiation->versions()
            ->where('action', 'uploaded')
            ->whereNotNull('file_path')
            ->where('file_path', '!=', '')
            ->reorder()
            ->orderByDesc('version_number')
            ->first();

        if ($lastUploadedVersion && Storage::disk('public')->exists($lastUploadedVersion->file_path)) {
            $ext = strtolower(pathinfo($lastUploadedVersion->file_path, PATHINFO_EXTENSION));
            $downloadName = 'agreement_' . $id . '_final.' . $ext;
            return Storage::disk('public')->download($lastUploadedVersion->file_path, $downloadName);
        }

        $finalPath = 'agreements/agreement_' . ($type === 'exhibition' ? 'exhib_' : '') . $id . '_final.pdf';
        if (Storage::disk('public')->exists($finalPath)) {
            return Storage::disk('public')->download($finalPath);
        }

        return response()->json(['message' => 'Final contract not found'], 404);
    }

    public function upload(Request $request, $id)
    {
        $user = $request->user();
        $type = $request->query('type', 'sponsor');
        $target = $this->getTarget($id, $type);

        if (!$target) return response()->json(['message' => 'Target not found'], 404);
        if (!$this->canAccess($user, $target)) return response()->json(['message' => 'Unauthorized'], 403);

        $request->validate([
            'file'    => 'required|file|mimes:docx,doc,pdf|max:10240',
            'message' => 'nullable|string|max:2000',
        ]);

        $query = AgreementNegotiation::query();
        if ($type === 'exhibition') {
            $query->where('exhibition_application_id', $id);
        } else {
            $query->where('sponsorship_request_id', $id);
        }
        $negotiation = $query->firstOrFail();

        if ($negotiation->status === 'accepted' || $negotiation->status === 'rejected') {
            return response()->json(['message' => 'Agreement already finalized or rejected'], 400);
        }

        $lastVersion = $negotiation->versions()->max('version_number') ?? 0;
        $newVersionNum = $lastVersion + 1;

        $dir = 'agreements';
        $fileName = 'agreement_' . ($type === 'exhibition' ? 'exhib_' : '') . $id . '_v' . $newVersionNum . '.' . $request->file('file')->getClientOriginalExtension();
        $filePath = $request->file('file')->storeAs($dir, $fileName, 'public');

        AgreementVersion::create([
            'negotiation_id' => $negotiation->id,
            'version_number' => $newVersionNum,
            'file_path'      => $filePath,
            'uploaded_by'    => $user->id,
            'action'         => 'uploaded',
            'message'        => $request->message,
        ]);

        $negotiation->update([
            'status'            => 'pending_review',
            'last_submitted_by' => $user->id,
        ]);

        // Notify other party
        $managerId = $target->event_manager_id;
        $partnerId = ($type === 'exhibition') ? $target->company_id : $target->sponsor_id;
        $otherUserId = ($user->id === $partnerId) ? $managerId : $partnerId;
        $otherUser = User::find($otherUserId);
        
        if ($otherUser) {
            $otherUser->notify(new SystemNotification(
                'عقد محدوث 📝',
                "{$user->name} رفع نسخة جديدة من عقد \"{$target->event->title}\".",
                'agreement',
                '📝',
                $otherUser->role === 'Event Manager' ? '/manager/sponsorship' : ($type === 'exhibition' ? '/company/applications' : '/sponsor/requests'),
                $target->event_id
            ));
        }

        return response()->json($negotiation);
    }

    public function respond(Request $request, $id)
    {
        $user = $request->user();
        $type = $request->query('type', 'sponsor');
        $target = $this->getTarget($id, $type);

        if (!$target) return response()->json(['message' => 'Target not found'], 404);
        if (!$this->canAccess($user, $target)) return response()->json(['message' => 'Unauthorized'], 403);

        $request->validate([
            'action'  => 'required|in:accepted,rejected,revision_requested',
            'message' => 'nullable|string|max:2000',
        ]);

        $query = AgreementNegotiation::query();
        if ($type === 'exhibition') {
            $query->where('exhibition_application_id', $id);
        } else {
            $query->where('sponsorship_request_id', $id);
        }
        $negotiation = $query->firstOrFail();

        if ($negotiation->last_submitted_by === $user->id && $request->action === 'accepted') {
            return response()->json(['message' => 'You cannot accept your own submission.'], 400);
        }

        if ($negotiation->status === 'accepted') {
            return response()->json(['message' => 'Agreement already finalized'], 400);
        }

        $lastVersion = $negotiation->versions()->max('version_number') ?? 0;
        $latestVersionModel = $negotiation->latestVersion;

        AgreementVersion::create([
            'negotiation_id' => $negotiation->id,
            'version_number' => $lastVersion,
            'file_path'      => $latestVersionModel ? $latestVersionModel->file_path : '',
            'uploaded_by'    => $user->id,
            'action'         => $request->action,
            'message'        => $request->message,
        ]);

        $negotiation->update([
            'status'      => $request->action,
            'final_notes' => $request->message,
        ]);

        if ($request->action === 'accepted') {
            $target->status = 'accepted';
            $target->save();

            if ($type === 'sponsor') {
                \App\Models\EventSponsor::updateOrCreate([
                    'event_id'   => $target->event_id,
                    'sponsor_id' => $target->sponsor_id,
                ], ['tier' => null, 'contribution_amount' => 0]);
            }

            if ($type === 'exhibition') {
                AgreementWordService::generateExhibitionFinalPdf($target, $negotiation);
            } else {
                AgreementWordService::generateFinalPdf($target, $negotiation);
            }
        }

        // Notify other party
        $managerId = $target->event_manager_id;
        $partnerId = ($type === 'exhibition') ? $target->company_id : $target->sponsor_id;
        $otherUserId = ($user->id === $partnerId) ? $managerId : $partnerId;
        $otherUser = User::find($otherUserId);
        
        if ($otherUser) {
            $icon = match($request->action) { 'accepted'=>'✅', 'rejected'=>'❌', default=>'🔄' };
            $otherUser->notify(new SystemNotification(
                "رد على العقد {$icon}",
                "{$user->name} " . ($request->action === 'accepted' ? 'قبل العقد' : ($request->action === 'rejected' ? 'رفض العقد' : 'طلب تعديلات')) . " لحدث \"{$target->event->title}\".",
                'agreement',
                $icon,
                $otherUser->role === 'Event Manager' ? '/manager/sponsorship' : ($type === 'exhibition' ? '/company/applications' : '/sponsor/requests'),
                $target->event_id
            ));
        }

        return response()->json($negotiation);
    }

    public function cancel(Request $request, $id)
    {
        $user = $request->user();
        $type = $request->query('type', 'sponsor');
        $target = $this->getTarget($id, $type);

        if (!$target) return response()->json(['message' => 'Target not found'], 404);
        if (!$this->canAccess($user, $target)) return response()->json(['message' => 'Unauthorized'], 403);

        $request->validate(['message' => 'required|string|min:5|max:2000']);

        if ($target->status !== 'negotiating') {
            return response()->json(['message' => 'Cannot cancel — not in negotiation'], 400);
        }

        $target->status = 'cancelled';
        $target->save();

        $query = AgreementNegotiation::query();
        if ($type === 'exhibition') {
            $query->where('exhibition_application_id', $id);
        } else {
            $query->where('sponsorship_request_id', $id);
        }
        $negotiation = $query->first();
        
        if ($negotiation) {
            $lastVersion = $negotiation->versions()->max('version_number') ?? 0;
            AgreementVersion::create([
                'negotiation_id' => $negotiation->id,
                'version_number' => $lastVersion,
                'file_path'      => '',
                'uploaded_by'    => $user->id,
                'action'         => 'rejected',
                'message'        => '🚫 تم إلغاء الاتفاقية: ' . $request->message,
            ]);
            $negotiation->update(['status' => 'rejected', 'final_notes' => $request->message]);
        }

        // Notify other party
        $managerId = $target->event_manager_id;
        $partnerId = ($type === 'exhibition') ? $target->company_id : $target->sponsor_id;
        $otherUserId = ($user->id === $partnerId) ? $managerId : $partnerId;
        $otherUser = User::find($otherUserId);
        
        if ($otherUser) {
            $otherUser->notify(new SystemNotification(
                'تم إلغاء الاتفاقية 🚫',
                "{$user->name} ألغى الاتفاقية لحدث \"{$target->event->title}\".\nالسبب: \"{$request->message}\"",
                'agreement',
                '🚫',
                $otherUser->role === 'Event Manager' ? '/manager/sponsorship' : ($type === 'exhibition' ? '/company/applications' : '/sponsor/requests'),
                $target->event_id
            ));
        }

        return response()->json(['message' => 'Agreement cancelled successfully']);
    }

    /**
     * Get the target model (SponsorshipRequest or ExhibitionApplication).
     */
    private function getTarget($id, $type)
    {
        if ($type === 'exhibition') {
            return \App\Models\ExhibitionApplication::find($id);
        }
        return SponsorshipRequest::find($id);
    }

    /**
     * Check if user can access this agreement.
     */
    private function canAccess($user, $target): bool
    {
        if ($user->role === 'Admin') return true;
        
        if ($target instanceof SponsorshipRequest) {
            return $user->id === $target->sponsor_id || $user->id === $target->event_manager_id;
        }
        
        if ($target instanceof \App\Models\ExhibitionApplication) {
            return $user->id === $target->company_id || $user->id === $target->event_manager_id;
        }

        return false;
    }
}
