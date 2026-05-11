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
        Schema::table('events', function (Blueprint $table) {
            $table->text('cancellation_reason')->nullable()->after('rejection_reason');
            $table->text('cancellation_rejection_reason')->nullable()->after('cancellation_reason');
            $table->boolean('is_tickets_open')->default(true)->after('is_sponsorship_open');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['cancellation_reason', 'cancellation_rejection_reason', 'is_tickets_open']);
        });
    }
};
