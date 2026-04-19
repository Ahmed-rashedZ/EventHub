<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Change event_type column to VARCHAR to support Arabic types
        DB::statement("ALTER TABLE events MODIFY event_type VARCHAR(255) NOT NULL DEFAULT 'مؤتمر'");

        // Update any old type values to the closest new Arabic type
        $map = [
            'Conference'    => 'مؤتمر',
            'Seminar'       => 'ندوة',
            'Workshop'      => 'ورشة عمل',
            'Exhibition'    => 'ملتقى علمي',
            'Entertainment' => 'ترفيه',
            'Festival'      => 'ترفيه',
            'Other'         => 'مؤتمر',
        ];

        foreach ($map as $old => $new) {
            DB::statement("UPDATE events SET event_type = ? WHERE event_type = ?", [$new, $old]);
        }
    }

    public function down(): void
    {
        // Revert to VARCHAR with English default
        DB::statement("ALTER TABLE events MODIFY event_type VARCHAR(255) NOT NULL DEFAULT 'Other'");
    }
};
