<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agreement_negotiations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sponsorship_request_id')->constrained('sponsorship_requests')->cascadeOnDelete();
            $table->enum('status', ['draft', 'pending_review', 'revision_requested', 'accepted', 'rejected'])->default('draft');
            $table->foreignId('last_submitted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('final_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('agreement_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('negotiation_id')->constrained('agreement_negotiations')->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->string('file_path');
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->enum('action', ['uploaded', 'accepted', 'rejected', 'revision_requested']);
            $table->text('message')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agreement_versions');
        Schema::dropIfExists('agreement_negotiations');
    }
};
