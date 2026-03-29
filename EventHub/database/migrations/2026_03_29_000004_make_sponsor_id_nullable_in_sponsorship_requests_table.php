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
        Schema::table('sponsorship_requests', function (Blueprint $table) {
            $table->dropForeign(['sponsor_id']);
            $table->unsignedBigInteger('sponsor_id')->nullable()->change();
            $table->foreign('sponsor_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sponsorship_requests', function (Blueprint $table) {
            $table->dropForeign(['sponsor_id']);
            $table->unsignedBigInteger('sponsor_id')->nullable(false)->change();
            $table->foreign('sponsor_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
