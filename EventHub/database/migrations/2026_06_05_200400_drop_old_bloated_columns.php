<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * FINAL CLEANUP: Drop old bloated columns after data has been migrated
 * to the new normalized tables.
 *
 * Run ONLY after:
 *   1. php artisan migrate (creates new tables)
 *   2. php artisan app:migrate-normalized-data (copies data)
 *   3. Verify everything works
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── Drop old document columns from users ──
        Schema::table('users', function (Blueprint $table) {
            $columns = [
                'doc_commercial_register',
                'doc_commercial_register_status',
                'doc_commercial_register_note',
                'doc_tax_number',
                'doc_tax_number_status',
                'doc_tax_number_note',
                'doc_articles_of_association',
                'doc_articles_of_association_status',
                'doc_articles_of_association_note',
                'doc_practice_license',
                'doc_practice_license_status',
                'doc_practice_license_note',
            ];

            foreach ($columns as $col) {
                if (Schema::hasColumn('users', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        // ── Drop old columns from events ──
        Schema::table('events', function (Blueprint $table) {
            $columns = [
                'external_venue_name',
                'external_venue_location',
                'booking_proof_path',
                'booking_date',
                'period',
                'ministry_document_path',
                'external_schedule',
                'internal_schedule',
                'agenda',
                'published_schedule',
                'rejection_reason',
                'review_message',
                'review_fields',
                'review_status',
                'cancellation_reason',
                'cancellation_rejection_reason',
            ];

            foreach ($columns as $col) {
                if (Schema::hasColumn('events', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }

    public function down(): void
    {
        // Re-add old columns if rollback is needed
        Schema::table('users', function (Blueprint $table) {
            $table->string('doc_commercial_register')->nullable();
            $table->string('doc_commercial_register_status')->default('pending');
            $table->text('doc_commercial_register_note')->nullable();
            $table->string('doc_tax_number')->nullable();
            $table->string('doc_tax_number_status')->default('pending');
            $table->text('doc_tax_number_note')->nullable();
            $table->string('doc_articles_of_association')->nullable();
            $table->string('doc_articles_of_association_status')->default('pending');
            $table->text('doc_articles_of_association_note')->nullable();
            $table->string('doc_practice_license')->nullable();
            $table->string('doc_practice_license_status')->default('pending');
            $table->text('doc_practice_license_note')->nullable();
        });

        Schema::table('events', function (Blueprint $table) {
            $table->string('external_venue_name')->nullable();
            $table->string('external_venue_location')->nullable();
            $table->string('booking_proof_path')->nullable();
            $table->date('booking_date')->nullable();
            $table->string('period')->nullable();
            $table->string('ministry_document_path')->nullable();
            $table->json('external_schedule')->nullable();
            $table->json('internal_schedule')->nullable();
            $table->json('agenda')->nullable();
            $table->json('published_schedule')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('review_message')->nullable();
            $table->json('review_fields')->nullable();
            $table->string('review_status')->default('none');
            $table->text('cancellation_reason')->nullable();
            $table->text('cancellation_rejection_reason')->nullable();
        });
    }
};
