<?php

namespace Tests\Concerns;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Minimal SQLite-compatible schema for white-box partner registration tests.
 * Avoids full migrations that use MySQL-specific ALTER statements.
 */
trait CreatesPartnerRegistrationSchema
{
    protected function setUpPartnerRegistrationSchema(): void
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
            $table->text('note')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'document_type']);
        });

        Schema::create('profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->string('profile_type')->default('individual');
            $table->string('company_type')->nullable();
            $table->string('company_type_slug')->nullable();
            $table->boolean('is_available')->default(true);
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

    protected function tearDownPartnerRegistrationSchema(): void
    {
        Schema::dropAllTables();
    }
}
