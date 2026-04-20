<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VerificationController extends Controller
{
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

    public function approve(Request $request, $id)
    {
        if ($request->user()->role !== 'Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user = User::findOrFail($id);
        $user->verification_status = 'verified';
        $user->save();

        return response()->json(['message' => 'User verification approved.']);
    }

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

        return response()->json(['message' => 'User verification rejected.']);
    }

    public function downloadDocument(Request $request, $id)
    {
        if ($request->user()->role !== 'Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $user = User::findOrFail($id);

        if (!$user->verification_document || !Storage::exists($user->verification_document)) {
            return response()->json(['message' => 'Document not found'], 404);
        }

        // To determine a proper filename and content type based on the file
        return Storage::download($user->verification_document);
    }

    public function requestChanges(Request $request, $id)
    {
        if ($request->user()->role !== 'Admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'notes' => 'required|string|max:1000'
        ]);

        $user = User::findOrFail($id);
        $user->verification_status = 'changes_requested';
        $user->verification_notes = $request->notes;
        $user->save();

        return response()->json(['message' => 'Changes requested properly.']);
    }

    public function reuploadDocument(Request $request)
    {
        $user = clone $request->user(); // Or fetch fresh user
        
        if (!in_array($user->role, ['Event Manager', 'Sponsor'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'verification_document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        // Delete old document
        if ($user->verification_document && Storage::exists($user->verification_document)) {
            Storage::delete($user->verification_document);
        }

        $docPath = $request->file('verification_document')->store('verifications');

        $user->verification_status = 'pending';
        // Keep verification_notes so the admin knows what was requested
        $user->verification_document = $docPath;
        $user->save();

        return response()->json(['message' => 'Document re-uploaded successfully', 'user' => $user]);
    }
}
