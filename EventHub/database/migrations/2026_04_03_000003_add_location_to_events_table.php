<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds a plain-text `location` string alongside the existing venue_id FK.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            // Nullable so existing rows are not broken
            $table->string('location')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('location');
        });
    }
};
