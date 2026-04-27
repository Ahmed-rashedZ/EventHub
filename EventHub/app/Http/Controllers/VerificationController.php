<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Notifications\SystemNotification;

class VerificationController extends Controller
{
    /**
     * Valid document type keys.
     */
    private const DOC_TYPES = [
        'doc_commercial_register',
        'doc_tax_number',
        'doc_articles_of_association',
        'doc_practice_license',
    ];

    /**
     * Human-readable labels for each document.
     */
    private const DOC_LABELS = [
        'doc_commercial_register'     => 'Commercial Register',
        'doc_tax_number'              => 'Tax Number Certificate',
        'doc_articles_of_association' => 'Articles of Association',
        'doc_practice_license'        => 'Practice License',
    ];

    /**
     * List pending/changes_requested verification requests.
     */
    public function pendingRequests(Request $request)
    {
        if ($request->user()->role !== 'Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $requests = User::whereIn('role', ['Event Manager', 'Sponsor'])
            ->whereIn('verification_status', ['pending', 'changes_requested'])
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($requests);
    }

    /**
     * Review individual documents – per-document approve/reject.
     * If all approved → auto-verify the account.
     * If any rejected → changes_requested + notification.
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
        $rejectedDocs = [];

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
            }
        }

        // Check if all documents are now approved
        $allApproved = true;
        foreach (self::DOC_TYPES as $dt) {
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

        // Some documents rejected
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
     * Reject the entire application directly without reviewing individual documents.
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

        // ── Notify the partner ──
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
     * Download a specific document by type.
     */
    public function downloadDocument(Request $request, $id, $type)
    {
        if ($request->user()->role !== 'Admin') {
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
     * Re-upload rejected documents only (partner side).
     */
    public function reuploadDocument(Request $request)
    {
        $user = $request->user();

        if (!in_array($user->role, ['Event Manager', 'Sponsor'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Validate – each document is optional (only rejected ones need re-upload)
        $request->validate([
            'doc_commercial_register'     => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'doc_tax_number'              => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'doc_articles_of_association' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'doc_practice_license'        => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $reuploadedCount = 0;

        foreach (self::DOC_TYPES as $docType) {
            if ($request->hasFile($docType)) {
                // Only allow re-upload of rejected documents
                if ($user->{$docType . '_status'} !== 'rejected') {
                    continue;
                }

                // Delete old document
                if ($user->{$docType} && Storage::exists($user->{$docType})) {
                    Storage::delete($user->{$docType});
                }

                $user->{$docType} = $request->file($docType)->store('verifications');
                $user->{$docType . '_status'} = 'pending';
                $user->{$docType . '_note'} = null;
                $reuploadedCount++;
            }
        }

        if ($reuploadedCount > 0) {
            $user->verification_status = 'pending';
            $user->save();
        }

        // Refresh user data
        $user = User::find($user->id);

        return response()->json(['message' => 'Documents re-uploaded successfully', 'user' => $user]);
    }
}
