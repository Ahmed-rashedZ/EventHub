<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exhibition_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->onDelete('cascade');
            $table->foreignId('company_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('event_manager_id')->constrained('users');
            $table->enum('initiator', ['company', 'event_manager'])->default('company');
            $table->text('message')->nullable();
            $table->enum('status', ['pending', 'accepted', 'rejected', 'negotiating'])->default('pending');
            $table->string('booth_preference')->nullable();
            $table->string('product_category')->nullable();
            $table->timestamps();

            $table->unique(['event_id', 'company_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exhibition_applications');
    }
};
