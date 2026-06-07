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
        '20_days'  => ['type' => 'day',  'val' => 20, 'label' => '20 days',    'icon' => '📅', 'msg' => 'starts in 20 days! Mark your calendar.'],
        '10_days'  => ['type' => 'day',  'val' => 10, 'label' => '10 days',    'icon' => '📆', 'msg' => 'is just 10 days away! Get ready.'],
        '3_days'   => ['type' => 'day',  'val' => 3,  'label' => '3 days',     'icon' => '⏳', 'msg' => 'is only 3 days away! Don\'t forget.'],
        '1_day'    => ['type' => 'day',  'val' => 1,  'label' => '1 day',      'icon' => '🔔', 'msg' => 'is tomorrow! See you there.'],
        '12_hours' => ['type' => 'time', 'val' => 720,'label' => '12 hours',   'icon' => '⚡', 'msg' => 'starts in 12 hours! Almost time.'],
        '1_hour'   => ['type' => 'time', 'val' => 60, 'label' => '1 hour',     'icon' => '🚀', 'msg' => 'starts in 1 hour! Head over now.'],
        'started'  => ['type' => 'time', 'val' => 0,  'label' => 'now',        'icon' => '🎉', 'msg' => 'has just started! Join now.'],
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
        $orderedKeys = ['20_days', '10_days', '3_days', '1_day', '12_hours', '1_hour', 'started'];

        foreach ($events as $event) {
            $daysUntilStart = $now->startOfDay()->diffInDays($event->start_time->copy()->startOfDay(), false);
            $minutesUntilStart = $now->diffInMinutes($event->start_time, false);

            // Find the most urgent/closest threshold currently active for this event
            $activeThresholdType = null;
            $activeThresholdIndex = -1;

            foreach ($orderedKeys as $index => $type) {
                $config = $this->thresholds[$type];
                $isActive = false;

                if ($config['type'] === 'day') {
                    if ($daysUntilStart <= $config['val']) {
                        $isActive = true;
                    }
                } else {
                    if ($daysUntilStart <= 0 && $minutesUntilStart <= $config['val']) {
                        $isActive = true;
                    }
                }

                if ($isActive) {
                    $activeThresholdType = $type;
                    $activeThresholdIndex = $index;
                }
            }

            if ($activeThresholdType !== null) {
                // Send the reminder if it has not been sent yet
                $alreadySent = EventReminder::where('event_id', $event->id)
                    ->where('reminder_type', $activeThresholdType)
                    ->exists();

                if (!$alreadySent) {
                    $config = $this->thresholds[$activeThresholdType];
                    $this->sendReminder($event, $activeThresholdType, $config);
                    $totalSent++;
                }

                // Auto-mark all less urgent (passed) thresholds as sent/skipped
                for ($i = 0; $i < $activeThresholdIndex; $i++) {
                    $passedType = $orderedKeys[$i];
                    EventReminder::firstOrCreate([
                        'event_id'      => $event->id,
                        'reminder_type' => $passedType,
                    ], [
                        'sent_at'       => $now,
                    ]);
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
