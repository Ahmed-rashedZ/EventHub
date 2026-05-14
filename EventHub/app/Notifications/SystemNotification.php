<?php

namespace App\Notifications;

use App\Services\FcmService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SystemNotification extends Notification
{
    use Queueable;

    protected string $title;
    protected string $message;
    protected string $type;     // event, sponsorship, ticket, verification, system
    protected string $icon;
    protected ?string $actionUrl;
    protected ?int $relatedId;

    /**
     * Create a new notification instance.
     *
     * @param string      $title     Short title (e.g. "Event Approved")
     * @param string      $message   Descriptive message
     * @param string      $type      Category: event|sponsorship|ticket|verification|system
     * @param string      $icon      Emoji icon for display
     * @param string|null $actionUrl URL to navigate to when clicked
     * @param int|null    $relatedId Related model ID (event_id, etc.)
     */
    public function __construct(
        string $title,
        string $message,
        string $type = 'system',
        string $icon = '🔔',
        ?string $actionUrl = null,
        ?int $relatedId = null
    ) {
        $this->title     = $title;
        $this->message   = $message;
        $this->type      = $type;
        $this->icon      = $icon;
        $this->actionUrl = $actionUrl;
        $this->relatedId = $relatedId;
    }

    /**
     * Get the notification's delivery channels.
     * Always stores in database. Sends FCM if user has a token.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Handle sending via FCM after storing in database.
     * Called automatically by Laravel's notification system.
     */
    public function toDatabase(object $notifiable): array
    {
        // Send FCM push notification if user has a token
        $fcmToken = $notifiable->fcm_token ?? null;
        if ($fcmToken) {
            FcmService::sendToDevice(
                fcmToken: $fcmToken,
                title: $this->icon . ' ' . $this->title,
                body: $this->message,
                data: [
                    'type'       => $this->type,
                    'related_id' => (string) ($this->relatedId ?? ''),
                    'action_url' => $this->actionUrl ?? '',
                ]
            );
        }

        return [
            'title'      => $this->title,
            'message'    => $this->message,
            'type'       => $this->type,
            'icon'       => $this->icon,
            'action_url' => $this->actionUrl,
            'related_id' => $this->relatedId,
        ];
    }
}
