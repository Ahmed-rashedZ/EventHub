<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('event_sponsor', function (Blueprint $table) {
            $table->id();

            $table->foreignId('event_id')
                  ->constrained('events')
                  ->onDelete('cascade');

            $table->foreignId('sponsor_id')
                  ->constrained('sponsors')
                  ->onDelete('cascade');

            // Sponsor tier — drives ticket branding logic:
            //   diamond → logo alongside event logo
            //   gold    → "Sponsored by"
            //   silver  → "Supported by"
            //   bronze  → "Special thanks to"
            $table->enum('tier', ['diamond', 'gold', 'silver', 'bronze']);

            $table->decimal('contribution_amount', 10, 2)->nullable();

            $table->timestamps();

            // A sponsor can only appear once per event (but can have different tiers
            // across different events)
            $table->unique(['event_id', 'sponsor_id']);
        });

        // Composite index for efficient per-event tier lookups
        // (unique constraint above already creates an index, but we add
        //  an explicit one on sponsor_id alone for reverse lookups)
        Schema::table('event_sponsor', function (Blueprint $table) {
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
