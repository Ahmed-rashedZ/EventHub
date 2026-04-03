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
        Schema::table('sponsorship_requests', function (Blueprint $table) {
            $table->foreignId('event_manager_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('cascade')
                  ->after('sponsor_id');
                  
            $table->enum('initiator', ['sponsor', 'event_manager'])
                  ->default('sponsor')
                  ->after('event_manager_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sponsorship_requests', function (Blueprint $table) {
            $table->dropForeign(['event_manager_id']);
            $table->dropColumn(['event_manager_id', 'initiator']);
        });
    }
};
