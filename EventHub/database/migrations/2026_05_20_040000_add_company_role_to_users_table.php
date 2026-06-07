<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('Admin','Event Manager','Sponsor','Attendee','Assistant','Company') DEFAULT 'Attendee'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('Admin','Event Manager','Sponsor','Attendee','Assistant') DEFAULT 'Attendee'");
    }
};
