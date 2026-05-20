<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('exhibition_zones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('exhibition_booths', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exhibition_zone_id')->constrained('exhibition_zones')->onDelete('cascade');
            $table->string('booth_number');
            $table->string('size')->nullable();
            $table->foreignId('exhibition_application_id')->nullable()->constrained('exhibition_applications')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('exhibition_booths');
        Schema::dropIfExists('exhibition_zones');
    }
};
