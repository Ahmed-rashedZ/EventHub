<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Make the tier column nullable in event_sponsor table.
     * This allows sponsors to be accepted without a tier classification.
     * Unique tiers per event are enforced at the application level (null tiers are exempt).
     */
    public function up(): void
    {
        // Change enum to nullable string to support null (unranked) sponsors
        DB::statement("ALTER TABLE event_sponsor MODIFY COLUMN tier VARCHAR(10) NULL DEFAULT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to non-nullable enum
        DB::statement("UPDATE event_sponsor SET tier = 'bronze' WHERE tier IS NULL");
        DB::statement("ALTER TABLE event_sponsor MODIFY COLUMN tier ENUM('diamond','gold','silver','bronze') NOT NULL");
    }
};
