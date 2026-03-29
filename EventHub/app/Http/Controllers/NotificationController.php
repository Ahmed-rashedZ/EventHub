<?php

namespace App\Http\Controllers;

use App\Models\EventNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    // GET /api/notifications  – user's notifications
    public function index(Request $request)
    {
        $notifications = EventNotification::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($notifications);
    }

    // PUT /api/notifications/{id}/read
    public function markRead(Request $request, $id)
    {
        $notification = EventNotification::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $notification->is_read = true;
        $notification->save();

        return response()->json(['message' => 'Marked as read']);
    }

    // POST /api/notifications  – Internal: create notification (Admin/System)
    public function store(Request $request)
    {
        if (!in_array($request->user()->role, ['Admin', 'Event Manager'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'message' => 'required|string|max:1000',
        ]);

        $notification = EventNotification::create([
            'user_id' => $request->user_id,
            'message' => $request->message,
        ]);

        return response()->json($notification, 201);
    }
}
