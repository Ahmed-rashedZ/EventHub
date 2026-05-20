<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agreement_negotiations', function (Blueprint $table) {
            $table->foreignId('exhibition_application_id')
                  ->nullable()
                  ->after('sponsorship_request_id')
                  ->constrained('exhibition_applications')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('agreement_negotiations', function (Blueprint $table) {
            $table->dropForeign(['exhibition_application_id']);
            $table->dropColumn('exhibition_application_id');
        });
    }
};
