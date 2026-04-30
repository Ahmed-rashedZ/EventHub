<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('venues', function (Blueprint $table) {
            $table->string('morning_start', 5)->default('09:00')->after('capacity');
            $table->string('morning_end', 5)->default('13:00')->after('morning_start');
            $table->string('evening_start', 5)->default('15:00')->after('morning_end');
            $table->string('evening_end', 5)->default('19:00')->after('evening_start');
        });
    }

    public function down(): void
    {
        Schema::table('venues', function (Blueprint $table) {
            $table->dropColumn(['morning_start', 'morning_end', 'evening_start', 'evening_end']);
        });
    }
};
