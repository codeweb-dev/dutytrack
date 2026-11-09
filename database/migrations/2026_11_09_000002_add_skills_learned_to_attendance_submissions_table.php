<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_submissions', function (Blueprint $table) {
            $table->text('skills_learned')->nullable()->after('accomplishment');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_submissions', function (Blueprint $table) {
            $table->dropColumn('skills_learned');
        });
    }
};
