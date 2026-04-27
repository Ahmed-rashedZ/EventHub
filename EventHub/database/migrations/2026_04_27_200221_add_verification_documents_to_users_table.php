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
        Schema::table('users', function (Blueprint $table) {
            // ── Document file paths ──
            $table->string('doc_commercial_register')->nullable()->after('verification_notes');
            $table->string('doc_tax_number')->nullable()->after('doc_commercial_register');
            $table->string('doc_articles_of_association')->nullable()->after('doc_tax_number');
            $table->string('doc_practice_license')->nullable()->after('doc_articles_of_association');

            // ── Per-document status: pending / approved / rejected ──
            $table->string('doc_commercial_register_status')->default('pending')->after('doc_practice_license');
            $table->string('doc_tax_number_status')->default('pending')->after('doc_commercial_register_status');
            $table->string('doc_articles_of_association_status')->default('pending')->after('doc_tax_number_status');
            $table->string('doc_practice_license_status')->default('pending')->after('doc_articles_of_association_status');

            // ── Per-document rejection notes ──
            $table->text('doc_commercial_register_note')->nullable()->after('doc_practice_license_status');
            $table->text('doc_tax_number_note')->nullable()->after('doc_commercial_register_note');
            $table->text('doc_articles_of_association_note')->nullable()->after('doc_tax_number_note');
            $table->text('doc_practice_license_note')->nullable()->after('doc_articles_of_association_note');

            // Drop old single-document column
            $table->dropColumn('verification_document');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('verification_document')->nullable()->after('verification_status');

            $table->dropColumn([
                'doc_commercial_register',
                'doc_tax_number',
                'doc_articles_of_association',
                'doc_practice_license',
                'doc_commercial_register_status',
                'doc_tax_number_status',
                'doc_articles_of_association_status',
                'doc_practice_license_status',
                'doc_commercial_register_note',
                'doc_tax_number_note',
                'doc_articles_of_association_note',
                'doc_practice_license_note',
            ]);
        });
    }
};
