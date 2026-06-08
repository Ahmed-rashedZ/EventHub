<?php

namespace Tests\Concerns;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

trait CreatesSponsorshipSchema
{
    protected function setUpSponsorshipSchema(): void
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

        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('profile_type')->default('company');
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('event_type')->default('مؤتمر');
            $table->unsignedBigInteger('venue_id')->nullable();
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->integer('capacity')->nullable();
            $table->string('status')->default('approved');
            $table->boolean('is_sponsorship_open')->default(true);
            $table->string('event_objective')->nullable();
            $table->string('target_audience')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('event_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->unique()->constrained('events')->cascadeOnDelete();
            $table->json('external_schedule')->nullable();
            $table->timestamps();
        });

        Schema::create('event_external_venues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->unique()->constrained('events')->cascadeOnDelete();
            $table->string('venue_name')->nullable();
            $table->string('venue_location')->nullable();
            $table->timestamps();
        });

        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating');
            $table->timestamps();
        });

        Schema::create('event_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->unique()->constrained('events')->cascadeOnDelete();
            $table->string('review_status')->default('none');
            $table->timestamps();
        });

        Schema::create('sponsorship_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('sponsor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('event_manager_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('initiator')->default('sponsor');
            $table->text('message')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });

        Schema::create('agreement_negotiations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sponsorship_request_id')->nullable()->constrained('sponsorship_requests')->cascadeOnDelete();
            $table->unsignedBigInteger('exhibition_application_id')->nullable();
            $table->string('status')->default('draft');
            $table->foreignId('last_submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('final_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('agreement_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('negotiation_id')->constrained('agreement_negotiations')->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->string('file_path');
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->string('action');
            $table->text('message')->nullable();
            $table->timestamps();
        });

        Schema::create('event_sponsor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('sponsor_id')->constrained('users')->cascadeOnDelete();
            $table->string('tier')->nullable();
            $table->decimal('contribution_amount', 10, 2)->nullable();
            $table->timestamps();
            $table->unique(['event_id', 'sponsor_id']);
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

    protected function tearDownSponsorshipSchema(): void
    {
        Schema::dropAllTables();
    }
}
