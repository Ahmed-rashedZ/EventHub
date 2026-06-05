<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->unique()->constrained('events')->onDelete('cascade');
            $table->text('rejection_reason')->nullable();
            $table->text('review_message')->nullable();
            $table->json('review_fields')->nullable();
            $table->string('review_status')->default('none'); // none, needs_review, reviewed
            $table->text('cancellation_reason')->nullable();
            $table->text('cancellation_rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_reviews');
    }
};
