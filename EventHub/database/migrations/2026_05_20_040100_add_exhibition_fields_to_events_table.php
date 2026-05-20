<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->boolean('is_exhibition')->default(false)->after('is_tickets_open');
            $table->boolean('is_applications_open')->default(true)->after('is_exhibition');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['is_exhibition', 'is_applications_open']);
        });
    }
};
