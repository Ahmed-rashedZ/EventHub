<?php

namespace Tests\Concerns;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

trait CreatesEventLifecycleSchema
{
    protected function setUpEventLifecycleSchema(): void
    {
        Schema::dropAllTables();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->default('Attendee');
            $table->json('interests')->nullable();
            $table->timestamps();
        });

        Schema::create('user_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('document_type');
            $table->string('file_path')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('event_type')->default('مؤتمر');
            $table->unsignedBigInteger('venue_id')->nullable();
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->integer('capacity')->nullable();
            $table->string('image')->nullable();
            $table->string('status')->default('pending');
            $table->boolean('is_sponsorship_open')->default(true);
            $table->boolean('is_tickets_open')->default(true);
            $table->boolean('is_exhibition')->default(false);
            $table->boolean('is_applications_open')->default(false);
            $table->boolean('is_published')->default(false);
            $table->boolean('is_exhibitor_registration_open')->default(false);
            $table->string('event_objective')->nullable();
            $table->string('target_audience')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('event_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->unique()->constrained('events')->cascadeOnDelete();
            $table->string('ministry_document_path')->nullable();
            $table->json('external_schedule')->nullable();
            $table->json('internal_schedule')->nullable();
            $table->json('agenda')->nullable();
            $table->json('published_schedule')->nullable();
            $table->timestamps();
        });

        Schema::create('event_external_venues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->unique()->constrained('events')->cascadeOnDelete();
            $table->string('venue_name');
            $table->string('venue_location')->nullable();
            $table->string('booking_proof_path')->nullable();
            $table->timestamps();
        });

        Schema::create('event_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->string('reminder_type');
            $table->timestamp('sent_at');
            $table->timestamps();
            $table->unique(['event_id', 'reminder_type']);
        });

        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->text('review_text')->nullable();
            $table->timestamps();
        });

        Schema::create('event_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->unique()->constrained('events')->cascadeOnDelete();
            $table->text('rejection_reason')->nullable();
            $table->text('review_message')->nullable();
            $table->json('review_fields')->nullable();
            $table->string('review_status')->default('none');
            $table->text('cancellation_reason')->nullable();
            $table->text('cancellation_rejection_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDownEventLifecycleSchema(): void
    {
        Schema::dropAllTables();
    }
}
