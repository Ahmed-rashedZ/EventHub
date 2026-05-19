<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assistance_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assistant_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->foreignId('manager_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            $table->text('message')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            // Prevent duplicate invitations for same assistant + event
            $table->unique(['assistant_id', 'event_id']);

            // Index for quick lookups
            $table->index(['assistant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assistance_requests');
    }
};
