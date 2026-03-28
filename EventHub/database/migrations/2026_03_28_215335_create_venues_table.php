<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('venues', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('location');
        $table->integer('capacity');
        $table->enum('status', ['available', 'maintenance'])->default('available');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('venues');
    }
};
