<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\EventReminder;
use App\Models\Ticket;
use App\Notifications\SystemNotification;
use Illuminate\Console\Command;
use Carbon\Carbon;

class SendEventReminders extends Command
{
    protected $signature = 'app:send-event-reminders';
    protected $description = 'Send reminder notifications to ticket holders before events start';

    /**
     * Reminder thresholds: key => [minutes_before, label, icon, message_template]
     */
    private array $thresholds = [
        '20_days'  => ['minutes' => 20 * 24 * 60, 'label' => '20 days',    'icon' => '📅', 'msg' => 'starts in 20 days! Mark your calendar.'],
        '10_days'  => ['minutes' => 10 * 24 * 60, 'label' => '10 days',    'icon' => '📆', 'msg' => 'is just 10 days away! Get ready.'],
        '3_days'   => ['minutes' => 3  * 24 * 60, 'label' => '3 days',     'icon' => '⏳', 'msg' => 'is only 3 days away! Don\'t forget.'],
        '1_day'    => ['minutes' => 1  * 24 * 60, 'label' => '1 day',      'icon' => '🔔', 'msg' => 'is tomorrow! See you there.'],
        '12_hours' => ['minutes' => 12 * 60,      'label' => '12 hours',   'icon' => '⚡', 'msg' => 'starts in 12 hours! Almost time.'],
        '1_hour'   => ['minutes' => 60,           'label' => '1 hour',     'icon' => '🚀', 'msg' => 'starts in 1 hour! Head over now.'],
        'started'  => ['minutes' => 0,            'label' => 'now',        'icon' => '🎉', 'msg' => 'has just started! Join now.'],
    ];

    public function handle(): int
    {
        $now = Carbon::now();

        // Get all published upcoming events (start_time in the future or just started within last 30 min)
        $events = Event::where('is_published', true)
            ->where('start_time', '>', $now->copy()->subMinutes(30))
            ->where('start_time', '<=', $now->copy()->addDays(21))
            ->get();

        $totalSent = 0;

        foreach ($events as $event) {
            $minutesUntilStart = $now->diffInMinutes($event->start_time, false);

            foreach ($this->thresholds as $type => $config) {
                // Check if this reminder was already sent
                $alreadySent = EventReminder::where('event_id', $event->id)
                    ->where('reminder_type', $type)
                    ->exists();

                if ($alreadySent) {
                    continue;
                }

                // Send reminder if the time remaining is <= threshold
                // For 'started': send when event has started (minutesUntilStart <= 0)
                // For others: send when we've passed the threshold
                if ($minutesUntilStart <= $config['minutes']) {
                    $this->sendReminder($event, $type, $config);
                    $totalSent++;
                }
            }
        }

        $this->info("Sent {$totalSent} reminder(s).");
        return self::SUCCESS;
    }

    private function sendReminder(Event $event, string $type, array $config): void
    {
        // Get all users who have tickets for this event
        $ticketHolders = Ticket::where('event_id', $event->id)
            ->where('status', '!=', 'cancelled')
            ->with('user')
            ->get()
            ->pluck('user')
            ->filter()
            ->unique('id');

        $title = "⏰ Event Reminder";
        $message = "\"{$event->title}\" {$config['msg']}";

        foreach ($ticketHolders as $user) {
            $user->notify(new SystemNotification(
                title: $title,
                message: $message,
                type: 'event',
                icon: $config['icon'],
                relatedId: $event->id,
            ));
        }

        // Record that this reminder was sent
        EventReminder::create([
            'event_id'      => $event->id,
            'reminder_type' => $type,
            'sent_at'       => now(),
        ]);

        $this->line("  → [{$config['icon']}] {$event->title}: {$config['label']} reminder sent to {$ticketHolders->count()} user(s)");
    }
}
