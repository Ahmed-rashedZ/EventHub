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
        Schema::dropIfExists('exhibition_booths');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('exhibition_booths', function ($table) {
            $table->id();
            $table->foreignId('application_id')->constrained('exhibition_applications')->onDelete('cascade');
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->foreignId('company_id')->constrained('users')->onDelete('cascade');
            $table->string('booth_number')->nullable();
            $table->string('booth_size')->default('medium');
            $table->decimal('booth_fee', 10, 2)->default(0);
            $table->string('rank')->nullable();
            $table->integer('rank_order')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }
};
