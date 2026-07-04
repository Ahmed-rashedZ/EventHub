<?php

namespace App\Http\Traits;

use App\Models\User;
use Illuminate\Http\JsonResponse;

trait ChecksDocumentVerification
{
    /**
     * Get required document types for a given user role.
     */
    private function getRequiredDocTypes(User $user): array
    {
        return in_array($user->role, ['Sponsor', 'Company'])
            ? ['commercial_register', 'tax_number']
            : ['commercial_register', 'tax_number', 'articles_of_association', 'practice_license'];
    }

    /**
     * Check if ALL required documents for a user are approved.
     */
    private function hasAllDocumentsApproved(User $user): bool
    {
        $user->load('documents');
        $requiredDocs = $this->getRequiredDocTypes($user);

        foreach ($requiredDocs as $docType) {
            $doc = $user->documents->firstWhere('document_type', $docType);
            if (!$doc || $doc->status !== 'approved') {
                return false;
            }
        }

        return true;
    }

    /**
     * Return a 403 JSON error for the requesting user's own documents not being approved.
     */
    private function ownDocumentsNotApprovedResponse(string $action = 'generic'): JsonResponse
    {
        $messages = [
            'create_event'      => 'لا يمكنك إنشاء فعالية لوجود وثائق مرفوضة أو غير معتمدة. يرجى مراجعة حالة وثائقك وإعادة رفعها.',
            'send_sponsorship'  => 'لا يمكنك إرسال طلبات رعاية لأن بعض وثائقك مرفوضة أو غير معتمدة. يرجى مراجعة حالة وثائقك.',
            'send_invitation'   => 'لا يمكنك إرسال دعوات لأن بعض وثائقك مرفوضة أو غير معتمدة. يرجى مراجعة حالة وثائقك.',
            'apply_exhibition'  => 'لا يمكنك التقديم على المعارض لأن بعض وثائقك مرفوضة أو غير معتمدة. يرجى مراجعة حالة وثائقك.',
            'generic'           => 'لا يمكنك تنفيذ هذا الإجراء لأن بعض وثائقك مرفوضة أو غير معتمدة.',
        ];

        return response()->json([
            'message' => $messages[$action] ?? $messages['generic'],
            'verification_status' => 'document_unapproved',
        ], 403);
    }

    /**
     * Return a 400 JSON error when the target partner's documents are not approved.
     */
    private function targetDocumentsNotApprovedResponse(string $targetRole = 'sponsor'): JsonResponse
    {
        $messages = [
            'sponsor' => 'لا يمكن دعوة هذا الراعي لأن وثائقه غير معتمدة بالكامل.',
            'company' => 'لا يمكن دعوة هذه الشركة لأن وثائقها غير معتمدة بالكامل.',
        ];

        return response()->json([
            'message' => $messages[$targetRole] ?? $messages['sponsor'],
            'verification_status' => 'target_document_unapproved',
        ], 400);
    }
}
