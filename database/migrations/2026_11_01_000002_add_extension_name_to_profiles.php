<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->string('extension_name')->nullable();
        });

        Schema::table('supervisor_profiles', function (Blueprint $table) {
            $table->string('extension_name')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->dropColumn('extension_name');
        });

        Schema::table('supervisor_profiles', function (Blueprint $table) {
            $table->dropColumn('extension_name');
        });
    }
};
