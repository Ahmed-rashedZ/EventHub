<?php

namespace Tests\Concerns;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

trait CreatesCompanyExhibitionSchema
{
    protected function setUpCompanyExhibitionSchema(): void
    {
        Schema::dropAllTables();

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('role')->default('Attendee');
            $table->string('verification_status')->default('verified');
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
            $table->string('company_type')->nullable();
            $table->string('company_type_slug')->nullable();
            $table->boolean('is_available')->default(true);
            $table->timestamps();
        });

        Schema::create('profile_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('profile_id')->constrained('profiles')->cascadeOnDelete();
            $table->string('type');
            $table->string('value');
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

        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('event_type')->default('مؤتمر');
            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->string('status')->default('approved');
            $table->boolean('is_exhibition')->default(false);
            $table->boolean('is_published')->default(true);
            $table->boolean('is_exhibitor_registration_open')->default(true);
            $table->string('company_category_slug')->nullable();
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
            $table->timestamps();
        });

        Schema::create('exhibition_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('company_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('event_manager_id')->constrained('users')->cascadeOnDelete();
            $table->string('initiator')->default('company');
            $table->text('message')->nullable();
            $table->string('status')->default('pending');
            $table->string('product_category')->nullable();
            $table->timestamps();
            $table->unique(['event_id', 'company_id']);
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

    protected function tearDownCompanyExhibitionSchema(): void
    {
        Schema::dropAllTables();
    }
}
