<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drop the old event_sponsor pivot (references sponsors.id)
     * and the sponsors table — both superseded by the unified profiles system.
     */
    public function up(): void
    {
        // Must drop pivot first — it has a FK to sponsors
        Schema::dropIfExists('event_sponsor');
        Schema::dropIfExists('sponsors');
    }

    /**
     * Reverse the migrations.
     * Recreates sponsors and a bare-bones event_sponsor for rollback safety.
     */
    public function down(): void
    {
        Schema::create('sponsors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->onDelete('cascade');
            $table->string('company_name');
            $table->string('logo')->nullable();
            $table->string('website_url')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_approved')->default(false);
            $table->timestamps();
        });

        Schema::create('event_sponsor', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->foreignId('sponsor_id')->constrained('sponsors')->onDelete('cascade');
            $table->enum('tier', ['diamond', 'gold', 'silver', 'bronze']);
            $table->decimal('contribution_amount', 10, 2)->nullable();
            $table->unique(['event_id', 'sponsor_id']);
            $table->timestamps();
        });
    }
};
