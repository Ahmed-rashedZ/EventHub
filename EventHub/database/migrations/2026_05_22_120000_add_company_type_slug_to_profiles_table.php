<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('profiles', 'company_type_slug')) {
                $table->string('company_type_slug')->nullable()->after('company_type');
            }
        });
    }

    public function down()
    {
        Schema::table('profiles', function (Blueprint $table) {
            if (Schema::hasColumn('profiles', 'company_type_slug')) {
                $table->dropColumn('company_type_slug');
            }
        });
    }
};
