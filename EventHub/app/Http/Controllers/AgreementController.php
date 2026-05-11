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
    /**
     * GET /api/agreements/{sponsorship_id}
     * Fetch negotiation details + all versions for a sponsorship request.
     */
    public function show(Request $request, $sponsorshipId)
    {
        $user = $request->user();
        $sreq = SponsorshipRequest::findOrFail($sponsorshipId);

        // Authorization: only the involved sponsor, manager, or admin
        if (!$this->canAccess($user, $sreq)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $negotiation = AgreementNegotiation::with(['versions.uploader', 'lastSubmitter'])
            ->where('sponsorship_request_id', $sponsorshipId)
            ->first();

        if (!$negotiation) {
            return response()->json(['message' => 'No agreement negotiation found'], 404);
        }

        return response()->json([
            'negotiation' => $negotiation,
            'sponsorship_request' => $sreq->load(['event', 'sponsor', 'manager']),
        ]);
    }

    /**
     * POST /api/agreements/{sponsorship_id}/generate
     * Generate the initial Word agreement after preliminary acceptance.
     */
    public function generate(Request $request, $sponsorshipId)
    {
        $user = $request->user();
        $sreq = SponsorshipRequest::with(['event.venue', 'sponsor', 'manager'])->findOrFail($sponsorshipId);

        if (!$this->canAccess($user, $sreq)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!in_array($sreq->status, ['accepted', 'negotiating'])) {
            return response()->json(['message' => 'Agreement can only be generated for accepted sponsorships'], 400);
        }

        // Check if negotiation already exists
        $existing = AgreementNegotiation::where('sponsorship_request_id', $sponsorshipId)->first();
        if ($existing) {
            return response()->json(['message' => 'Agreement already generated', 'negotiation' => $existing->load('versions.uploader')], 200);
        }

        // Generate Word document
        $filePath = AgreementWordService::generate($sreq);

        // Create negotiation record
        $negotiation = AgreementNegotiation::create([
            'sponsorship_request_id' => $sreq->id,
            'status'                 => 'draft',
            'last_submitted_by'      => $user->id,
        ]);

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
        $otherUserId = ($user->id === $sreq->sponsor_id) ? $sreq->event_manager_id : $sreq->sponsor_id;
        $otherUser = User::find($otherUserId);
        if ($otherUser) {
            $otherUser->notify(new SystemNotification(
                'عقد رعاية جديد 📋',
                "تم إنشاء عقد رعاية لحدث \"{$sreq->event->title}\". يرجى مراجعة العقد وتعديل البنود.",
                'agreement',
                '📋',
                $otherUser->role === 'Sponsor' ? '/sponsor/requests' : '/manager/sponsorship',
                $sreq->event_id
            ));
        }

        return response()->json($negotiation, 201);
    }

    /**
     * GET /api/agreements/{sponsorship_id}/download/{version?}
     * Download a specific version or the latest.
     */
    public function download(Request $request, $sponsorshipId, $version = null)
    {
        $user = $request->user();
        $sreq = SponsorshipRequest::findOrFail($sponsorshipId);

        if (!$this->canAccess($user, $sreq)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $negotiation = AgreementNegotiation::where('sponsorship_request_id', $sponsorshipId)->firstOrFail();

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

    /**
     * GET /api/agreements/{sponsorship_id}/download-final
     * Download the final contract — returns the LAST UPLOADED file (with all negotiated modifications).
     */
    public function downloadFinal(Request $request, $sponsorshipId)
    {
        $user = $request->user();
        $sreq = SponsorshipRequest::findOrFail($sponsorshipId);

        if (!$this->canAccess($user, $sreq)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $negotiation = AgreementNegotiation::where('sponsorship_request_id', $sponsorshipId)->firstOrFail();

        if ($negotiation->status !== 'accepted') {
            return response()->json(['message' => 'Agreement not finalized yet'], 400);
        }

        // Priority 1: Return the LAST UPLOADED file directly (this is the file with all negotiated edits)
        $lastUploadedVersion = $negotiation->versions()
            ->where('action', 'uploaded')
            ->whereNotNull('file_path')
            ->where('file_path', '!=', '')
            ->reorder()
            ->orderByDesc('version_number')
            ->first();

        if ($lastUploadedVersion && Storage::disk('public')->exists($lastUploadedVersion->file_path)) {
            $ext = strtolower(pathinfo($lastUploadedVersion->file_path, PATHINFO_EXTENSION));
            $downloadName = 'agreement_' . $sponsorshipId . '_final.' . $ext;
            return Storage::disk('public')->download($lastUploadedVersion->file_path, $downloadName);
        }

        // Priority 2: Return the generated final PDF (if it exists from generateFinalPdf)
        $finalPath = 'agreements/agreement_' . $sponsorshipId . '_final.pdf';
        if (Storage::disk('public')->exists($finalPath)) {
            return Storage::disk('public')->download($finalPath);
        }

        return response()->json(['message' => 'Final contract not found'], 404);
    }

    /**
     * POST /api/agreements/{sponsorship_id}/upload
     * Upload a modified agreement and send review request.
     */
    public function upload(Request $request, $sponsorshipId)
    {
        $user = $request->user();
        $sreq = SponsorshipRequest::with('event')->findOrFail($sponsorshipId);

        if (!$this->canAccess($user, $sreq)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'file'    => 'required|file|mimes:docx,doc,pdf|max:10240',
            'message' => 'nullable|string|max:2000',
        ]);

        $negotiation = AgreementNegotiation::where('sponsorship_request_id', $sponsorshipId)->firstOrFail();

        if ($negotiation->status === 'accepted') {
            return response()->json(['message' => 'Agreement already finalized, cannot upload new versions'], 400);
        }

        if ($negotiation->status === 'rejected') {
            return response()->json(['message' => 'Agreement was rejected, cannot upload new versions'], 400);
        }

        // Get next version number
        $lastVersion = $negotiation->versions()->max('version_number') ?? 0;
        $newVersionNum = $lastVersion + 1;

        // Store file
        $dir = 'agreements';
        $fileName = 'agreement_' . $sponsorshipId . '_v' . $newVersionNum . '.' . $request->file('file')->getClientOriginalExtension();
        $filePath = $request->file('file')->storeAs($dir, $fileName, 'public');

        // Create version record
        AgreementVersion::create([
            'negotiation_id' => $negotiation->id,
            'version_number' => $newVersionNum,
            'file_path'      => $filePath,
            'uploaded_by'    => $user->id,
            'action'         => 'uploaded',
            'message'        => $request->message,
        ]);

        // Update negotiation status
        $negotiation->update([
            'status'            => 'pending_review',
            'last_submitted_by' => $user->id,
        ]);

        $negotiation->load('versions.uploader');

        // Notify the other party
        $otherUserId = ($user->id === $sreq->sponsor_id) ? $sreq->event_manager_id : $sreq->sponsor_id;
        $otherUser = User::find($otherUserId);
        if ($otherUser) {
            $otherUser->notify(new SystemNotification(
                'عقد رعاية محدث 📝',
                "{$user->name} رفع نسخة جديدة من عقد \"{$sreq->event->title}\". يرجى مراجعة التعديلات.",
                'agreement',
                '📝',
                $otherUser->role === 'Sponsor' ? '/sponsor/requests' : '/manager/sponsorship',
                $sreq->event_id
            ));
        }

        return response()->json($negotiation);
    }

    /**
     * PUT /api/agreements/{sponsorship_id}/respond
     * Accept, reject, or request revision on the agreement.
     */
    public function respond(Request $request, $sponsorshipId)
    {
        $user = $request->user();
        $sreq = SponsorshipRequest::with('event')->findOrFail($sponsorshipId);

        if (!$this->canAccess($user, $sreq)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'action'  => 'required|in:accepted,rejected,revision_requested',
            'message' => 'nullable|string|max:2000',
        ]);

        $negotiation = AgreementNegotiation::where('sponsorship_request_id', $sponsorshipId)->firstOrFail();

        // Cannot accept own submission
        if ($negotiation->last_submitted_by === $user->id && $request->action === 'accepted') {
            return response()->json(['message' => 'You cannot accept your own submission. Wait for the other party to review.'], 400);
        }

        if ($negotiation->status === 'accepted') {
            return response()->json(['message' => 'Agreement already finalized'], 400);
        }

        // Get latest version number for the response record
        $lastVersion = $negotiation->versions()->max('version_number') ?? 0;
        $latestVersionModel = $negotiation->latestVersion;

        // Create response version record
        AgreementVersion::create([
            'negotiation_id' => $negotiation->id,
            'version_number' => $lastVersion, // Same version number, different action
            'file_path'      => $latestVersionModel ? $latestVersionModel->file_path : '',
            'uploaded_by'    => $user->id,
            'action'         => $request->action,
            'message'        => $request->message,
        ]);

        // Update negotiation status
        $newStatus = $request->action;
        if ($request->action === 'accepted') {
            $newStatus = 'accepted';
        } elseif ($request->action === 'rejected') {
            $newStatus = 'rejected';
        } else {
            $newStatus = 'revision_requested';
        }

        $negotiation->update([
            'status'      => $newStatus,
            'final_notes' => $request->message,
        ]);

        // If accepted, finalize: attach sponsor to event + generate final PDF
        if ($request->action === 'accepted') {
            $sreq->load(['event.venue', 'manager', 'sponsor']);

            // Update sponsorship request to OFFICIALLY accepted
            $sreq->status = 'accepted';
            $sreq->save();

            // NOW attach the sponsor to the event (only after contract is finalized)
            \App\Models\EventSponsor::updateOrCreate([
                'event_id'   => $sreq->event_id,
                'sponsor_id' => $sreq->sponsor_id,
            ], [
                'tier'                => null,
                'contribution_amount' => 0,
            ]);

            // Generate final PDF
            AgreementWordService::generateFinalPdf($sreq, $negotiation);
        }

        $negotiation->load('versions.uploader');

        // Notify the other party
        $otherUserId = ($user->id === $sreq->sponsor_id) ? $sreq->event_manager_id : $sreq->sponsor_id;
        $otherUser = User::find($otherUserId);
        if ($otherUser) {
            $actionText = match ($request->action) {
                'accepted'           => 'قبل العقد ✅',
                'rejected'           => 'رفض العقد ❌',
                'revision_requested' => 'طلب تعديلات على العقد 🔄',
            };
            $icon = match ($request->action) {
                'accepted' => '✅',
                'rejected' => '❌',
                'revision_requested' => '🔄',
            };

            $otherUser->notify(new SystemNotification(
                "رد على العقد {$icon}",
                "{$user->name} {$actionText} لحدث \"{$sreq->event->title}\"." .
                    ($request->message ? "\nالرسالة: \"{$request->message}\"" : ''),
                'agreement',
                $icon,
                $otherUser->role === 'Sponsor' ? '/sponsor/requests' : '/manager/sponsorship',
                $sreq->event_id
            ));
        }

        return response()->json($negotiation);
    }

    /**
     * PUT /api/agreements/{sponsorship_id}/cancel
     * Cancel the agreement during negotiation phase. Requires a message.
     */
    public function cancel(Request $request, $sponsorshipId)
    {
        $user = $request->user();
        $sreq = SponsorshipRequest::with('event')->findOrFail($sponsorshipId);

        if (!$this->canAccess($user, $sreq)) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'message' => 'required|string|min:5|max:2000',
        ]);

        // Only allow cancellation during negotiating phase
        if ($sreq->status !== 'negotiating') {
            return response()->json(['message' => 'Cannot cancel — agreement is already finalized or not in negotiation'], 400);
        }

        // Update sponsorship request to cancelled
        $sreq->status = 'cancelled';
        $sreq->save();

        // Update negotiation if exists
        $negotiation = AgreementNegotiation::where('sponsorship_request_id', $sponsorshipId)->first();
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

            $negotiation->update([
                'status'      => 'rejected',
                'final_notes' => $request->message,
            ]);
        }

        // Notify the other party
        $otherUserId = ($user->id === $sreq->sponsor_id) ? $sreq->event_manager_id : $sreq->sponsor_id;
        $otherUser = User::find($otherUserId);
        if ($otherUser) {
            $otherUser->notify(new SystemNotification(
                'تم إلغاء اتفاقية الرعاية 🚫',
                "{$user->name} ألغى اتفاقية الرعاية لحدث \"{$sreq->event->title}\".\nالسبب: \"{$request->message}\"",
                'agreement',
                '🚫',
                $otherUser->role === 'Sponsor' ? '/sponsor/requests' : '/manager/sponsorship',
                $sreq->event_id
            ));
        }

        return response()->json(['message' => 'Agreement cancelled successfully']);
    }

    /**
     * Check if user can access this sponsorship's agreement.
     */
    private function canAccess($user, $sreq): bool
    {
        return $user->role === 'Admin'
            || $user->id === $sreq->sponsor_id
            || $user->id === $sreq->event_manager_id;
    }
}
