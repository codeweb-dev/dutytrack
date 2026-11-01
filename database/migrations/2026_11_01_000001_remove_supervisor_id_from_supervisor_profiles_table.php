<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supervisor_profiles', function (Blueprint $table) {
            $table->dropUnique('supervisor_profiles_supervisor_id_unique');
            $table->dropColumn('supervisor_id');
        });
    }

    public function down(): void
    {
        Schema::table('supervisor_profiles', function (Blueprint $table) {
            $table->string('supervisor_id')->nullable()->unique();
        });
    }
};
