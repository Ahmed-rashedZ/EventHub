<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['venue_id']);
            $table->unsignedBigInteger('venue_id')->nullable()->change();
            $table->foreign('venue_id')->references('id')->on('venues')->onDelete('set null');

            $table->string('external_venue_name')->nullable()->after('venue_id');
            $table->string('external_venue_location')->nullable()->after('external_venue_name');
            $table->string('booking_proof_path')->nullable()->after('external_venue_location');
            $table->string('period')->nullable()->after('booking_proof_path');
            $table->date('booking_date')->nullable()->after('period');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropForeign(['venue_id']);
            $table->unsignedBigInteger('venue_id')->nullable(false)->change();
            $table->foreign('venue_id')->references('id')->on('venues')->onDelete('cascade');

            $table->dropColumn([
                'external_venue_name',
                'external_venue_location',
                'booking_proof_path',
                'period',
                'booking_date'
            ]);
        });
    }
};
