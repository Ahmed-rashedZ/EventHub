<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('location')->nullable();
            $table->string('event_type')->default('مؤتمر');

            // ── Venue (nullable for external venues) ─────────────────────
            $table->unsignedBigInteger('venue_id')->nullable();
            $table->foreign('venue_id')->references('id')->on('venues')->onDelete('set null');

            // ── External Venue Info ──────────────────────────────────────
            $table->string('external_venue_name')->nullable();
            $table->string('external_venue_location')->nullable();
            $table->string('booking_proof_path')->nullable();
            $table->string('period')->nullable();
            $table->date('booking_date')->nullable();

            $table->dateTime('start_time');
            $table->dateTime('end_time');
            $table->integer('capacity');
            $table->string('image')->nullable();

            // ── Status & Moderation ──────────────────────────────────────
            $table->string('status')->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->boolean('is_sponsorship_open')->default(true);

            $table->foreignId('created_by')->constrained('users');
            $table->timestamps();
        });

        // Add FK for users.event_id now that events table exists
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['event_id']);
        });
        Schema::dropIfExists('events');
    }
};
