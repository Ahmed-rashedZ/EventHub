<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('document_type', [
                'commercial_register',
                'tax_number',
                'articles_of_association',
                'practice_license',
            ]);
            $table->string('file_path')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected, pending_update
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'document_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_documents');
    }
};
