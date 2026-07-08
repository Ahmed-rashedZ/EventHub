<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\AiTrainingLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;

class FeedEndedEventsToAI extends Command
{
    protected $signature = 'app:feed-ended-events-to-ai';
    protected $description = 'Send ended event attendance data to AI microservice for continuous model retraining';

    /**
     * Map Arabic event types to English types used by the AI model.
     * (Same mapping as EventController::mapEventTypeToAI)
     */
    private array $eventTypeMap = [
        'مؤتمر'        => 'Conference',
        'ندوة'          => 'Seminar',
        'ورشة عمل'     => 'Workshop',
        'دورة تدريبية' => 'Course',
        'ترفيه'         => 'Entertainment',
        'معرض'          => 'Exhibition',
        'ملتقى علمي'   => 'Conference',
        'رياضة'         => 'Entertainment',
        'تقنية'         => 'Conference',
        'اجتماعية'      => 'Meeting',
    ];

    public function handle(): int
    {
        $aiUrl = env('EVENTHUB_AI_URL', 'http://127.0.0.1:8001');

        // Get all approved events that have ended and NOT yet sent to AI
        $endedEvents = Event::where('status', 'approved')
            ->where('end_time', '<', now())
            ->whereDoesntHave('aiTrainingLog')
            ->with(['schedule', 'tickets.attendanceLogs'])
            ->get();

        if ($endedEvents->isEmpty()) {
            $this->info('No new ended events to process.');
            return self::SUCCESS;
        }

        $this->info("Found {$endedEvents->count()} ended event(s) to feed to AI.");
        $successCount = 0;
        $skipCount = 0;

        foreach ($endedEvents as $event) {
            // ── Calculate Actual Attendance ──
            // Count unique tickets that have at least one scan (attendance log)
            $actualAttendance = $event->tickets
                ->filter(fn ($ticket) => $ticket->attendanceLogs->isNotEmpty())
                ->count();

            // Skip events with zero attendance (no useful training data)
            if ($actualAttendance === 0) {
                $this->line("  ⏭ [{$event->id}] \"{$event->title}\" — 0 attendance, skipping.");

                // Still log it so we don't re-check every run
                AiTrainingLog::create([
                    'event_id'          => $event->id,
                    'actual_attendance' => 0,
                    'ai_response'       => ['skipped' => 'zero_attendance'],
                    'sent_at'           => now(),
                ]);

                $skipCount++;
                continue;
            }

            // ── Calculate Total Days ──
            $totalDays = $this->calculateTotalDays($event);

            // ── Check if Includes Weekend (Friday or Saturday) ──
            $includesWeekend = $this->checkIncludesWeekend($event);

            // ── Determine Time Period ──
            $timePeriod = $this->determineTimePeriod($event);

            // ── Map Event Type ──
            $eventTypeEN = $this->eventTypeMap[$event->event_type] ?? 'Conference';

            // ── Build payload for AI ──
            $payload = [
                'Event_Type'        => $eventTypeEN,
                'Total_Days'        => $totalDays,
                'Includes_Weekend'  => $includesWeekend,
                'Time_Period'       => $timePeriod,
                'Actual_Attendance' => $actualAttendance,
            ];

            $this->line("  → [{$event->id}] \"{$event->title}\" | {$eventTypeEN}, {$totalDays}d, wknd={$includesWeekend}, {$timePeriod}, attendance={$actualAttendance}");

            // ── Send to AI /retrain ──
            try {
                $response = Http::timeout(30)->post("{$aiUrl}/retrain", $payload);

                if ($response->successful()) {
                    AiTrainingLog::create([
                        'event_id'          => $event->id,
                        'actual_attendance' => $actualAttendance,
                        'ai_response'       => $response->json(),
                        'sent_at'           => now(),
                    ]);

                    $this->info("    ✅ Sent successfully. Dataset now has {$response->json('dataset_rows')} rows.");
                    $successCount++;
                } else {
                    $this->error("    ❌ AI returned error: {$response->status()} — {$response->body()}");
                }
            } catch (\Exception $e) {
                $this->error("    ❌ Could not connect to AI service: {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->info("Done! Sent: {$successCount}, Skipped: {$skipCount}, Total: {$endedEvents->count()}");

        return self::SUCCESS;
    }

    /**
     * Calculate total event days from published_schedule, internal/external schedule, or date diff.
     */
    private function calculateTotalDays(Event $event): int
    {
        // Priority 1: published_schedule (most accurate — actual published days)
        $publishedSchedule = $event->schedule?->published_schedule;
        if ($publishedSchedule && is_array($publishedSchedule) && count($publishedSchedule) > 0) {
            return count($publishedSchedule);
        }

        // Priority 2: internal_schedule
        $internalSchedule = $event->schedule?->internal_schedule;
        if ($internalSchedule && is_array($internalSchedule) && count($internalSchedule) > 0) {
            $uniqueDates = collect($internalSchedule)->pluck('date')->unique();
            return $uniqueDates->count();
        }

        // Priority 3: external_schedule
        $externalSchedule = $event->schedule?->external_schedule;
        if ($externalSchedule && is_array($externalSchedule) && count($externalSchedule) > 0) {
            $uniqueDates = collect($externalSchedule)->pluck('date')->unique();
            return $uniqueDates->count();
        }

        // Fallback: diff between start_time and end_time
        if ($event->start_time && $event->end_time) {
            $days = $event->start_time->startOfDay()->diffInDays($event->end_time->startOfDay()) + 1;
            return max(1, $days);
        }

        return 1;
    }

    /**
     * Check if the event spans a Friday or Saturday (Libyan weekend).
     */
    private function checkIncludesWeekend(Event $event): int
    {
        $dates = collect();

        // Collect all event dates from schedules
        $publishedSchedule = $event->schedule?->published_schedule;
        if ($publishedSchedule && is_array($publishedSchedule)) {
            $dates = collect($publishedSchedule)->pluck('date');
        } elseif ($event->schedule?->internal_schedule && is_array($event->schedule->internal_schedule)) {
            $dates = collect($event->schedule->internal_schedule)->pluck('date');
        } elseif ($event->schedule?->external_schedule && is_array($event->schedule->external_schedule)) {
            $dates = collect($event->schedule->external_schedule)->pluck('date');
        }

        // If we have explicit dates, check them
        if ($dates->isNotEmpty()) {
            foreach ($dates as $date) {
                $dayOfWeek = Carbon::parse($date)->dayOfWeek;
                // Friday = 5, Saturday = 6
                if ($dayOfWeek === Carbon::FRIDAY || $dayOfWeek === Carbon::SATURDAY) {
                    return 1;
                }
            }
            return 0;
        }

        // Fallback: check all dates in the range
        if ($event->start_time && $event->end_time) {
            $current = $event->start_time->copy()->startOfDay();
            $end = $event->end_time->copy()->startOfDay();

            while ($current <= $end) {
                if ($current->dayOfWeek === Carbon::FRIDAY || $current->dayOfWeek === Carbon::SATURDAY) {
                    return 1;
                }
                $current->addDay();
            }
        }

        return 0;
    }

    /**
     * Determine if the event is Morning or Evening based on start_time.
     */
    private function determineTimePeriod(Event $event): string
    {
        if ($event->start_time) {
            $hour = $event->start_time->hour;
            // If start time is before 2 PM → Morning, otherwise Evening
            return $hour < 14 ? 'Morning' : 'Evening';
        }

        return 'Morning'; // Default fallback
    }
}
