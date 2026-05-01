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

            // ── Company / Sponsor Info ───────────────────────────────────────
            $table->string('company_name')->nullable();
            $table->text('company_description')->nullable();

            // ── Admin Workflow ───────────────────────────────────────────────
            $table->boolean('is_approved')->default(false);
            $table->boolean('is_available')->default(true);

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
