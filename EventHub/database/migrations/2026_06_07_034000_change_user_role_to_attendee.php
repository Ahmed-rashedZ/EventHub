<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Temporarily allow both 'User' and 'Attendee' in the role enum
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('Admin','Event Manager','Sponsor','User','Attendee','Assistant','Company') DEFAULT 'Attendee'");

        // 2. Update existing 'User' to 'Attendee'
        DB::table('users')->where('role', 'User')->update(['role' => 'Attendee']);

        // 3. Remove 'User' from the role enum
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('Admin','Event Manager','Sponsor','Attendee','Assistant','Company') DEFAULT 'Attendee'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Temporarily allow both 'User' and 'Attendee' in the role enum
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('Admin','Event Manager','Sponsor','User','Attendee','Assistant','Company') DEFAULT 'User'");

        // 2. Update existing 'Attendee' to 'User'
        DB::table('users')->where('role', 'Attendee')->update(['role' => 'User']);

        // 3. Remove 'Attendee' from the role enum
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('Admin','Event Manager','Sponsor','User','Assistant','Company') DEFAULT 'User'");
    }
};
