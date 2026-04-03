<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Unified profile table — one profile per user regardless of role.
     * Sponsors use company fields; event managers use individual fields.
     */
    public function up(): void
    {
        Schema::create('profiles', function (Blueprint $table) {
            $table->id();

            // One profile per user, cascades on user deletion
            $table->foreignId('user_id')
                  ->unique()
                  ->constrained('users')
                  ->onDelete('cascade');

            // individual = event managers / personal accounts
            // company    = sponsors / organisations
            $table->enum('profile_type', ['individual', 'company'])
                  ->default('individual');

            // ── Basic Info ──────────────────────────────────────────────────
            $table->string('logo')->nullable();           // file path or URL
            $table->text('bio')->nullable();              // "About me / About us"
            $table->string('phone')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('website')->nullable();

            // ── Company / Sponsor Info ───────────────────────────────────────
            $table->string('company_name')->nullable();
            $table->text('company_description')->nullable();

            // ── Social Media ────────────────────────────────────────────────
            $table->string('facebook')->nullable();
            $table->string('instagram')->nullable();
            $table->string('linkedin')->nullable();

            // ── Admin Workflow ───────────────────────────────────────────────
            // Pre-built for future admin approval of sponsor profiles
            $table->boolean('is_approved')->default(false);

            $table->timestamps();

            // Index profile_type for role-based filtering queries
            $table->index('profile_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profiles');
    }
};
