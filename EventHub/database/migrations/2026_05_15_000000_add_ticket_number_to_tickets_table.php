<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $box) {
            $box->integer('ticket_number')->nullable()->after('event_id');
        });
    }

    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $box) {
            $box->dropColumn('ticket_number');
        });
    }
};
