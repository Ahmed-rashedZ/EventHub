<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Models\EventExternalVenue;
use App\Models\EventReview;
use App\Models\EventSchedule;
use App\Models\User;
use App\Models\UserDocument;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateNormalizedData extends Command
{
    protected $signature = 'app:migrate-normalized-data';
    protected $description = 'Safely migrate existing data from old bloated columns to new normalized tables';

    public function handle()
    {
        $this->info('Starting data migration to normalized tables...');

        DB::beginTransaction();

        try {
            $this->migrateUserDocuments();
            $this->migrateEventExternalVenues();
            $this->migrateEventSchedules();
            $this->migrateEventReviews();

            DB::commit();
            $this->info('✅ All data migrated successfully!');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('❌ Migration failed: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return Command::FAILURE;
        }
    }

    private function migrateUserDocuments()
    {
        $this->info('Migrating user documents...');

        $docTypes = [
            'commercial_register',
            'tax_number',
            'articles_of_association',
            'practice_license',
        ];

        // Only migrate partners who might have documents
        $users = DB::table('users')
            ->whereIn('role', ['Event Manager', 'Sponsor', 'Company'])
            ->get();

        $count = 0;
        foreach ($users as $user) {
            foreach ($docTypes as $type) {
                $fileCol = 'doc_' . $type;
                $statusCol = 'doc_' . $type . '_status';
                $noteCol = 'doc_' . $type . '_note';

                // Check if column exists and has data
                $filePath = $user->$fileCol ?? null;
                $status = $user->$statusCol ?? 'pending';
                $note = $user->$noteCol ?? null;

                // Only create record if the user has a file path or non-default status
                if ($filePath || $status !== 'pending' || $note) {
                    UserDocument::updateOrCreate(
                        ['user_id' => $user->id, 'document_type' => $type],
                        [
                            'file_path' => $filePath,
                            'status' => $status,
                            'note' => $note,
                        ]
                    );
                    $count++;
                }
            }
        }

        $this->info("  → Migrated {$count} user document records from {$users->count()} partners.");
    }

    private function migrateEventExternalVenues()
    {
        $this->info('Migrating event external venues...');

        $events = DB::table('events')
            ->whereNotNull('external_venue_name')
            ->where('external_venue_name', '!=', '')
            ->get();

        $count = 0;
        foreach ($events as $event) {
            EventExternalVenue::updateOrCreate(
                ['event_id' => $event->id],
                [
                    'venue_name' => $event->external_venue_name,
                    'venue_location' => $event->external_venue_location,
                    'booking_proof_path' => $event->booking_proof_path,
                    'booking_date' => $event->booking_date,
                    'period' => $event->period,
                ]
            );
            $count++;
        }

        $this->info("  → Migrated {$count} external venue records.");
    }

    private function migrateEventSchedules()
    {
        $this->info('Migrating event schedules...');

        $events = DB::table('events')->get();

        $count = 0;
        foreach ($events as $event) {
            // Only create if there's actual schedule data
            $hasData = $event->ministry_document_path
                || $event->external_schedule
                || $event->internal_schedule
                || $event->agenda
                || $event->published_schedule;

            if ($hasData) {
                EventSchedule::updateOrCreate(
                    ['event_id' => $event->id],
                    [
                        'ministry_document_path' => $event->ministry_document_path,
                        'external_schedule' => is_string($event->external_schedule) ? json_decode($event->external_schedule, true) : $event->external_schedule,
                        'internal_schedule' => is_string($event->internal_schedule) ? json_decode($event->internal_schedule, true) : $event->internal_schedule,
                        'agenda' => is_string($event->agenda) ? json_decode($event->agenda, true) : $event->agenda,
                        'published_schedule' => is_string($event->published_schedule) ? json_decode($event->published_schedule, true) : $event->published_schedule,
                    ]
                );
                $count++;
            }
        }

        $this->info("  → Migrated {$count} event schedule records.");
    }

    private function migrateEventReviews()
    {
        $this->info('Migrating event reviews...');

        $events = DB::table('events')->get();

        $count = 0;
        foreach ($events as $event) {
            // Only create if there's actual review/cancellation data
            $hasData = $event->rejection_reason
                || $event->review_message
                || $event->review_fields
                || ($event->review_status && $event->review_status !== 'none')
                || $event->cancellation_reason
                || $event->cancellation_rejection_reason;

            if ($hasData) {
                EventReview::updateOrCreate(
                    ['event_id' => $event->id],
                    [
                        'rejection_reason' => $event->rejection_reason,
                        'review_message' => $event->review_message,
                        'review_fields' => is_string($event->review_fields) ? json_decode($event->review_fields, true) : $event->review_fields,
                        'review_status' => $event->review_status ?? 'none',
                        'cancellation_reason' => $event->cancellation_reason,
                        'cancellation_rejection_reason' => $event->cancellation_rejection_reason,
                    ]
                );
                $count++;
            }
        }

        $this->info("  → Migrated {$count} event review records.");
    }
}
