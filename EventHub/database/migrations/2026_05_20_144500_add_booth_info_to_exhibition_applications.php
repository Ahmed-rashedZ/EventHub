<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('exhibition_applications', function (Blueprint $table) {
            $table->string('booth_number')->nullable();
            $table->string('booth_size')->nullable();
        });
    }

    public function down()
    {
        Schema::table('exhibition_applications', function (Blueprint $table) {
            $table->dropColumn(['booth_number', 'booth_size']);
        });
    }
};
