<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Notifications\SystemNotification;

class VerificationController extends Controller
{
    private const DOC_TYPES = [
        'doc_commercial_register',
        'doc_tax_number',
        'doc_articles_of_association',
        'doc_practice_license',
    ];

    private const SPONSOR_DOC_TYPES = [
        'doc_commercial_register',
        'doc_tax_number',
    ];

    private const DOC_LABELS = [
        'doc_commercial_register'     => 'Commercial Register',
        'doc_tax_number'              => 'Tax Number Certificate',
        'doc_articles_of_association' => 'Articles of Association',
        'doc_practice_license'        => 'Practice License',
    ];

    private function getDocTypesForUser(User $user): array
    {
        return $user->role === 'Sponsor' ? self::SPONSOR_DOC_TYPES : self::DOC_TYPES;
    }

    /**
     * List pending/changes_requested + verified-with-pending_update users for admin.
     */
    public function pendingRequests(Request $request)
    {
        if ($request->user()->role !== 'Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $requests = User::whereIn('role', ['Event Manager', 'Sponsor'])
            ->where(function ($query) {
                $query->whereIn('verification_status', ['pending', 'changes_requested'])
                      ->orWhere(function ($q) {
                          $q->where('verification_status', 'verified')
                            ->where(function ($inner) {
                                $inner->where('doc_commercial_register_status', 'pending_update')
                                      ->orWhere('doc_tax_number_status', 'pending_update')
                                      ->orWhere('doc_articles_of_association_status', 'pending_update')
                                      ->orWhere('doc_practice_license_status', 'pending_update');
                            });
                      });
            })
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($requests);
    }

    /**
     * Review individual documents per-document approve/reject.
     * For verified users: only updates individual doc statuses.
     * For others: standard first-time verification flow.
     */
    public function reviewDocuments(Request $request, $id)
    {
        if ($request->user()->role !== 'Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'documents' => 'required|array',
            'documents.*.status' => 'required|string|in:approved,rejected',
            'documents.*.note' => 'nullable|string|max:1000',
        ]);

        $user = User::findOrFail($id);
        $isVerifiedUpdate = $user->verification_status === 'verified';
        $rejectedDocs = [];
        $approvedDocs = [];

        foreach ($request->documents as $docType => $decision) {
            if (!in_array($docType, self::DOC_TYPES)) {
                continue;
            }

            $user->{$docType . '_status'} = $decision['status'];

            if ($decision['status'] === 'rejected') {
                $note = $decision['note'] ?? '';
                $user->{$docType . '_note'} = $note;
                $rejectedDocs[] = (self::DOC_LABELS[$docType] ?? $docType) . ': ' . $note;
            } else {
                $user->{$docType . '_note'} = null;
                $approvedDocs[] = self::DOC_LABELS[$docType] ?? $docType;
            }
        }

        // ── Verified user document update: don't change overall status ──
        if ($isVerifiedUpdate) {
            $user->save();

            if (count($rejectedDocs) > 0) {
                $user->notify(new SystemNotification(
                    'Document Update Rejected ❌',
                    "Your updated documents were rejected:\n" . implode("\n", $rejectedDocs),
                    'verification',
                    '❌',
                    '/profile'
                ));
            }
            if (count($approvedDocs) > 0) {
                $user->notify(new SystemNotification(
                    'Document Update Approved ✅',
                    'Your updated documents have been approved: ' . implode(', ', $approvedDocs),
                    'verification',
                    '✅',
                    '/profile'
                ));
            }

            return response()->json(['message' => 'Document update reviewed successfully.']);
        }

        // ── First-time verification ──
        $userDocTypes = $this->getDocTypesForUser($user);
        $allApproved = true;
        foreach ($userDocTypes as $dt) {
            if ($user->{$dt . '_status'} !== 'approved') {
                $allApproved = false;
                break;
            }
        }

        if ($allApproved) {
            $user->verification_status = 'verified';
            $user->verification_notes = null;
            $user->save();

            $user->notify(new SystemNotification(
                'Verification Approved ✅',
                'All your documents have been approved! You now have full access to the platform.',
                'verification',
                '✅',
                $user->role === 'Event Manager' ? '/manager/dashboard' : '/sponsor/dashboard'
            ));

            return response()->json(['message' => 'All documents approved. User verified.']);
        }

        $user->verification_status = 'changes_requested';
        $user->verification_notes = implode("\n", $rejectedDocs);
        $user->save();

        $user->notify(new SystemNotification(
            'Documents Need Revision ⚠️',
            "Some documents were rejected:\n" . implode("\n", $rejectedDocs),
            'verification',
            '⚠️',
            '/pending-verification'
        ));

        return response()->json(['message' => 'Review submitted. Partner notified of rejected documents.']);
    }

    /**
     * Reject the entire application.
     */
    public function reject(Request $request, $id)
    {
        if ($request->user()->role !== 'Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'notes' => 'required|string|max:1000'
        ]);

        $user = User::findOrFail($id);
        $user->verification_status = 'rejected';
        $user->verification_notes = $request->notes;
        $user->save();

        $user->notify(new SystemNotification(
            'Verification Rejected ❌',
            "Your verification was rejected: {$request->notes}",
            'verification',
            '❌',
            '/pending-verification'
        ));

        return response()->json(['message' => 'User verification rejected.']);
    }

    /**
     * Download a document (Admin only via API, or owner via web route viewMyDocument).
     */
    public function downloadDocument(Request $request, $id, $type)
    {
        $authUser = $request->user();

        if ($authUser->role !== 'Admin' && (string)$authUser->id !== (string)$id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!in_array($type, self::DOC_TYPES)) {
            return response()->json(['message' => 'Invalid document type'], 400);
        }

        $user = User::findOrFail($id);
        $docPath = $user->{$type};

        if (!$docPath || !Storage::exists($docPath)) {
            return response()->json(['message' => 'Document not found'], 404);
        }

        return Storage::download($docPath);
    }

    /**
     * Serve the current user's own document inline via web session auth.
     * Used by the profile page View button — no Bearer token / popup issues.
     */
    public function viewMyDocument(Request $request, string $type)
    {
        $user = $request->user();

        if (!$user || !in_array($user->role, ['Event Manager', 'Sponsor'])) {
            abort(403);
        }

        if (!in_array($type, self::DOC_TYPES)) {
            abort(400, 'Invalid document type');
        }

        $docPath = $user->{$type};

        if (!$docPath || !Storage::exists($docPath)) {
            abort(404, 'Document not found');
        }

        return Storage::response($docPath);
    }

    /**
     * Re-upload / update documents (partner side).
     * Verified users: sets individual doc to 'pending_update', overall status unchanged.
     * Non-verified users: sets overall status to 'pending'.
     */
    public function reuploadDocument(Request $request)
    {
        $user = $request->user();

        if (!in_array($user->role, ['Event Manager', 'Sponsor'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'doc_commercial_register'     => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'doc_tax_number'              => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'doc_articles_of_association' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'doc_practice_license'        => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $reuploadedCount = 0;
        $isVerified = $user->verification_status === 'verified';
        $userDocTypes = $this->getDocTypesForUser($user);
        $updatedDocNames = [];

        foreach ($userDocTypes as $docType) {
            if ($request->hasFile($docType)) {
                if ($user->{$docType} && Storage::exists($user->{$docType})) {
                    Storage::delete($user->{$docType});
                }

                $user->{$docType} = $request->file($docType)->store('verifications');
                $user->{$docType . '_status'} = $isVerified ? 'pending_update' : 'pending';
                $user->{$docType . '_note'} = null;
                $updatedDocNames[] = self::DOC_LABELS[$docType] ?? $docType;
                $reuploadedCount++;
            }
        }

        if ($reuploadedCount > 0) {
            if (!$isVerified) {
                $user->verification_status = 'pending';
            }
            $user->save();

            $admins = User::where('role', 'Admin')->get();
            $docList = implode(', ', $updatedDocNames);
            foreach ($admins as $admin) {
                $admin->notify(new SystemNotification(
                    $isVerified ? 'Document Update Request 📄' : 'Verification Document Updated',
                    $isVerified
                        ? "Partner {$user->name} has submitted updated documents for review: {$docList}"
                        : "Partner {$user->name} has resubmitted verification documents and requires review.",
                    'verification',
                    '🛡️',
                    '/admin/verifications'
                ));
            }
        }

        $user = User::find($user->id);

        return response()->json(['message' => 'Documents submitted for review successfully.', 'user' => $user]);
    }

    /**
     * Get current user's document statuses (for partner profile page).
     */
    public function myDocuments(Request $request)
    {
        $user = $request->user();

        if (!in_array($user->role, ['Event Manager', 'Sponsor'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $userDocTypes = $this->getDocTypesForUser($user);
        $documents = [];

        foreach ($userDocTypes as $docType) {
            $documents[] = [
                'key'      => $docType,
                'label'    => self::DOC_LABELS[$docType] ?? $docType,
                'has_file' => !empty($user->{$docType}),
                'status'   => $user->{$docType . '_status'} ?? 'pending',
                'note'     => $user->{$docType . '_note'} ?? null,
            ];
        }

        return response()->json([
            'verification_status' => $user->verification_status,
            'documents'           => $documents,
        ]);
    }
}
