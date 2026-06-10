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
    protected ?int $requestId;

    /**
     * Create a new notification instance.
     *
     * @param string      $title     Short title (e.g. "Event Approved")
     * @param string      $message   Descriptive message
     * @param string      $type      Category: event|assistant_request|sponsorship|ticket|verification|system
     * @param string      $icon      Emoji icon for display
     * @param string|null $actionUrl URL to navigate to when clicked
     * @param int|null    $relatedId Related model ID (event_id, etc.)
     * @param int|null    $requestId Assistance request ID (for assistant invitations)
     */
    public function __construct(
        string $title,
        string $message,
        string $type = 'system',
        string $icon = '🔔',
        ?string $actionUrl = null,
        ?int $relatedId = null,
        ?int $requestId = null
    ) {
        $this->title     = $this->stripEmojis($title);
        $this->message   = $this->stripEmojis($message);
        $this->type      = $type;
        $this->icon      = $icon;
        $this->actionUrl = $actionUrl;
        $this->relatedId = $relatedId;
        $this->requestId = $requestId;
    }

    /**
     * Helper to strip all raw Unicode emojis from text fields.
     */
    private function stripEmojis(string $text): string
    {
        $emojiPattern = '/[\x{1F600}-\x{1F64F}\x{1F300}-\x{1F5FF}\x{1F680}-\x{1F6FF}\x{1F1E6}-\x{1F1FF}\x{2600}-\x{27BF}\x{1F900}-\x{1F9FF}\x{1F018}-\x{1F0F5}\x{203C}\x{2049}\x{2139}\x{2194}-\x{2199}\x{21A9}-\x{21AA}\x{231A}-\x{231B}\x{23E9}-\x{23EC}\x{23F0}\x{23F3}\x{24C2}\x{25AA}-\x{25AB}\x{25B6}\x{25C0}\x{25FB}-\x{25FE}\x{2934}-\x{2935}\x{2B05}-\x{2B07}\x{2B1B}-\x{2B1C}\x{2B50}\x{2B55}\x{3030}\x{303D}\x{3297}\x{3299}]/u';
        $clean = preg_replace($emojiPattern, '', $text);
        return trim(preg_replace('/\s+/', ' ', $clean));
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
                    'type'        => $this->type,
                    'related_id'  => (string) ($this->relatedId ?? ''),
                    'request_id'  => (string) ($this->requestId ?? ''),
                    'action_url'  => $this->actionUrl ?? '',
                    'title'       => $this->title,
                    'body'        => $this->message,
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
