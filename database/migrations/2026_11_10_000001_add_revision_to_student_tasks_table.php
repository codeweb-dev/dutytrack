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
        Schema::table('student_tasks', function (Blueprint $table) {
            $table->enum('status', ['pending', 'in_progress', 'in_review', 'revision', 'done'])->default('pending')->change();
            $table->text('revision_note')->nullable()->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_tasks', function (Blueprint $table) {
            $table->dropColumn('revision_note');
            $table->enum('status', ['pending', 'in_progress', 'in_review', 'done'])->default('pending')->change();
        });
    }
};
