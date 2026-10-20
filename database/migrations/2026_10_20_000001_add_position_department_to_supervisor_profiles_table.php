<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('supervisor_profiles', function (Blueprint $table) {
            $table->string('position_department')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('supervisor_profiles', function (Blueprint $table) {
            $table->dropColumn('position_department');
        });
    }
};
