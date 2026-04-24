<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * GET /api/notifications – current user's notifications (latest 50)
     */
    public function index(Request $request)
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->take(50)
            ->get()
            ->map(function ($n) {
                return [
                    'id'         => $n->id,
                    'title'      => $n->data['title'] ?? '',
                    'message'    => $n->data['message'] ?? '',
                    'type'       => $n->data['type'] ?? 'system',
                    'icon'       => $n->data['icon'] ?? '🔔',
                    'action_url' => $n->data['action_url'] ?? null,
                    'related_id' => $n->data['related_id'] ?? null,
                    'is_read'    => $n->read_at !== null,
                    'created_at' => $n->created_at->toISOString(),
                ];
            });

        $unread_count = $request->user()->unreadNotifications()->count();

        return response()->json([
            'notifications' => $notifications,
            'unread_count'  => $unread_count,
        ]);
    }

    /**
     * PUT /api/notifications/{id}/read – mark a single notification as read
     */
    public function markRead(Request $request, $id)
    {
        $notification = $request->user()
            ->notifications()
            ->where('id', $id)
            ->firstOrFail();

        $notification->markAsRead();

        return response()->json(['message' => 'Marked as read']);
    }

    /**
     * PUT /api/notifications/read-all – mark all notifications as read
     */
    public function markAllRead(Request $request)
    {
        $request->user()->unreadNotifications->markAsRead();

        return response()->json(['message' => 'All notifications marked as read']);
    }
}
