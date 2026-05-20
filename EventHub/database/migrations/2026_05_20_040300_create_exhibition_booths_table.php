<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exhibition_booths', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained('exhibition_applications')->onDelete('cascade');
            $table->foreignId('event_id')->constrained('events');
            $table->foreignId('company_id')->constrained('users');
            $table->string('booth_number')->nullable();
            $table->enum('booth_size', ['small', 'medium', 'large', 'custom'])->default('medium');
            $table->decimal('booth_fee', 10, 2)->default(0);
            $table->string('rank')->nullable();        // Flexible rank text (e.g. "رئيسي", "ذهبي")
            $table->integer('rank_order')->default(99); // Display ordering, lower = higher prominence
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exhibition_booths');
    }
};
