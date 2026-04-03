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
        Schema::create('profile_contacts', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('profile_id')
                  ->constrained('profiles')
                  ->onDelete('cascade');
                  
            // Type of contact (e.g. phone, website, linkedin...)
            $table->string('type');
            
            // The actual value
            $table->string('value');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profile_contacts');
    }
};
