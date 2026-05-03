<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('ministry_document_path')->nullable()->after('booking_proof_path');
            $table->text('review_message')->nullable()->after('rejection_reason');
            $table->json('review_fields')->nullable()->after('review_message');
            $table->string('review_status')->default('none')->after('review_fields');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['ministry_document_path', 'review_message', 'review_fields', 'review_status']);
        });
    }
};
