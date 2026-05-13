<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_log_id')->constrained('attendance_logs')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('ojt_assignment_id')->constrained('ojt_assignments')->cascadeOnDelete();
            $table->text('accomplishment')->nullable();
            $table->json('photo_paths')->nullable();
            $table->timestamps();

            $table->unique('attendance_log_id');
            $table->index(['student_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_submissions');
    }
};
