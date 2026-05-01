<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Event-sponsor pivot — sponsor_id references users.id directly.
     * Any user with role "Sponsor" can be linked to an event.
     */
    public function up(): void
    {
        Schema::create('event_sponsor', function (Blueprint $table) {
            $table->id();

            $table->foreignId('event_id')
                  ->constrained('events')
                  ->onDelete('cascade');

            // References users.id — the sponsor IS a user
            $table->foreignId('sponsor_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            // Tier drives ticket branding (nullable for unranked sponsors):
            //   diamond → logo alongside event logo
            //   gold    → "Sponsored by" + logo
            //   silver  → "Supported by"
            //   bronze  → "Special thanks to"
            //   null    → unranked (accepted but not yet classified)
            $table->string('tier', 10)->nullable();

            $table->decimal('contribution_amount', 10, 2)->nullable();

            $table->timestamps();

            // One sponsor per event (different tiers across different events is fine)
            $table->unique(['event_id', 'sponsor_id']);

            // Index for reverse lookups: all events a sponsor is in
            $table->index('sponsor_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_sponsor');
    }
};
