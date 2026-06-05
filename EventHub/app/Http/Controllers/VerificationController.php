<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Notifications\SystemNotification;

class VerificationController extends Controller
{
    private const DOC_TYPES = [
        'commercial_register',
        'tax_number',
        'articles_of_association',
        'practice_license',
    ];

    private const SPONSOR_DOC_TYPES = [
        'commercial_register',
        'tax_number',
    ];

    private const DOC_LABELS = [
        'commercial_register'     => 'Commercial Register',
        'tax_number'              => 'Tax Number Certificate',
        'articles_of_association' => 'Articles of Association',
        'practice_license'        => 'Practice License',
    ];

    // Legacy key mapping for backward compat with frontend form field names
    private const DOC_LEGACY_KEYS = [
        'doc_commercial_register'     => 'commercial_register',
        'doc_tax_number'              => 'tax_number',
        'doc_articles_of_association' => 'articles_of_association',
        'doc_practice_license'        => 'practice_license',
    ];

    private function getDocTypesForUser(User $user): array
    {
        return in_array($user->role, ['Sponsor', 'Company']) ? self::SPONSOR_DOC_TYPES : self::DOC_TYPES;
    }

    /**
     * Normalize a doc key — handles both 'commercial_register' and 'doc_commercial_register' formats.
     */
    private function normalizeDocType(string $key): ?string
    {
        if (in_array($key, self::DOC_TYPES)) {
            return $key;
        }
        return self::DOC_LEGACY_KEYS[$key] ?? null;
    }

    /**
     * List pending/changes_requested + verified-with-pending_update users for admin.
     */
    public function pendingRequests(Request $request)
    {
        if ($request->user()->role !== 'Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $requests = User::whereIn('role', ['Event Manager', 'Sponsor', 'Company'])
            ->where(function ($query) {
                $query->whereIn('verification_status', ['pending', 'changes_requested'])
                      ->orWhere(function ($q) {
                          $q->where('verification_status', 'verified')
                            ->whereHas('documents', function ($dq) {
                                $dq->where('status', 'pending_update');
                            });
                      });
            })
            ->with('documents')
            ->orderBy('created_at', 'asc')
            ->get();

        // Append legacy doc_* attributes for frontend backward compatibility
        $requests->each(function ($user) {
            $this->appendLegacyDocAttributes($user);
        });

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

        $user = User::with('documents')->findOrFail($id);
        $isVerifiedUpdate = $user->verification_status === 'verified';
        $rejectedDocs = [];
        $approvedDocs = [];

        foreach ($request->documents as $docKey => $decision) {
            $docType = $this->normalizeDocType($docKey);
            if (!$docType || !in_array($docType, self::DOC_TYPES)) {
                continue;
            }

            $doc = $user->documents->firstWhere('document_type', $docType);
            if (!$doc) {
                // Create if doesn't exist
                $doc = UserDocument::create([
                    'user_id' => $user->id,
                    'document_type' => $docType,
                    'status' => $decision['status'],
                    'note' => $decision['status'] === 'rejected' ? ($decision['note'] ?? '') : null,
                ]);
            } else {
                $doc->status = $decision['status'];
                if ($decision['status'] === 'rejected') {
                    $doc->note = $decision['note'] ?? '';
                } else {
                    $doc->note = null;
                }
                $doc->save();
            }

            if ($decision['status'] === 'rejected') {
                $note = $decision['note'] ?? '';
                $rejectedDocs[] = (self::DOC_LABELS[$docType] ?? $docType) . ': ' . $note;
            } else {
                $approvedDocs[] = self::DOC_LABELS[$docType] ?? $docType;
            }
        }

        // ── Verified user document update: don't change overall status ──
        if ($isVerifiedUpdate) {
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
        $user->load('documents'); // Reload after changes
        $allApproved = true;
        foreach ($userDocTypes as $dt) {
            $doc = $user->documents->firstWhere('document_type', $dt);
            if (!$doc || $doc->status !== 'approved') {
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

        $docType = $this->normalizeDocType($type);
        if (!$docType || !in_array($docType, self::DOC_TYPES)) {
            return response()->json(['message' => 'Invalid document type'], 400);
        }

        $user = User::with('documents')->findOrFail($id);
        $doc = $user->documents->firstWhere('document_type', $docType);
        $docPath = $doc?->file_path;

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

        if (!$user || !in_array($user->role, ['Event Manager', 'Sponsor', 'Company'])) {
            abort(403);
        }

        $docType = $this->normalizeDocType($type);
        if (!$docType || !in_array($docType, self::DOC_TYPES)) {
            abort(400, 'Invalid document type');
        }

        $doc = $user->documents()->where('document_type', $docType)->first();
        $docPath = $doc?->file_path;

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

        if (!in_array($user->role, ['Event Manager', 'Sponsor', 'Company'])) {
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
            $legacyKey = 'doc_' . $docType;

            if ($request->hasFile($legacyKey)) {
                // Get or create the document record
                $doc = UserDocument::firstOrNew([
                    'user_id' => $user->id,
                    'document_type' => $docType,
                ]);

                // Delete old file if exists
                if ($doc->file_path && Storage::exists($doc->file_path)) {
                    Storage::delete($doc->file_path);
                }

                $doc->file_path = $request->file($legacyKey)->store('verifications');
                $doc->status = $isVerified ? 'pending_update' : 'pending';
                $doc->note = null;
                $doc->save();

                $updatedDocNames[] = self::DOC_LABELS[$docType] ?? $docType;
                $reuploadedCount++;
            }
        }

        if ($reuploadedCount > 0) {
            if (!$isVerified) {
                $user->verification_status = 'pending';
                $user->save();
            }

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

        $user = User::with('documents')->find($user->id);
        $this->appendLegacyDocAttributes($user);

        return response()->json(['message' => 'Documents submitted for review successfully.', 'user' => $user]);
    }

    /**
     * Get current user's document statuses (for partner profile page).
     */
    public function myDocuments(Request $request)
    {
        $user = $request->user();

        if (!in_array($user->role, ['Event Manager', 'Sponsor', 'Company'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user->load('documents');
        $userDocTypes = $this->getDocTypesForUser($user);
        $documents = [];

        foreach ($userDocTypes as $docType) {
            $doc = $user->documents->firstWhere('document_type', $docType);
            $documents[] = [
                'key'      => 'doc_' . $docType, // Keep legacy key format for frontend
                'label'    => self::DOC_LABELS[$docType] ?? $docType,
                'has_file' => !empty($doc?->file_path),
                'status'   => $doc?->status ?? 'pending',
                'note'     => $doc?->note ?? null,
            ];
        }

        return response()->json([
            'verification_status' => $user->verification_status,
            'documents'           => $documents,
        ]);
    }

    /**
     * Append legacy doc_* attributes to user for frontend backward compatibility.
     * This ensures the API response includes the same flat structure the frontend expects.
     */
    private function appendLegacyDocAttributes(User $user): void
    {
        if (!$user->relationLoaded('documents')) {
            $user->load('documents');
        }

        foreach (self::DOC_TYPES as $docType) {
            $doc = $user->documents->firstWhere('document_type', $docType);
            $legacyPrefix = 'doc_' . $docType;
            $user->setAttribute($legacyPrefix, $doc?->file_path);
            $user->setAttribute($legacyPrefix . '_status', $doc?->status ?? 'pending');
            $user->setAttribute($legacyPrefix . '_note', $doc?->note);
        }
    }
}
