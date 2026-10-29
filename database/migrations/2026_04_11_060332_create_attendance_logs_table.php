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
        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('ojt_assignment_id')->constrained('ojt_assignments')->cascadeOnDelete();
            $table->decimal('hours_rendered', 5, 2)->default(0);
            $table->boolean('overtime_enabled')->default(false);
            $table->date('attendance_date');
            $table->timestamp('morning_time_in')->nullable();
            $table->timestamp('morning_time_out')->nullable();
            $table->timestamp('afternoon_time_in')->nullable();
            $table->timestamp('afternoon_time_out')->nullable();
            $table->decimal('time_in_latitude', 10, 7)->nullable();
            $table->decimal('time_in_longitude', 10, 7)->nullable();
            $table->unsignedInteger('time_in_location_accuracy')->nullable();
            $table->enum('status', ['present', 'late', 'absent', 'incomplete'])->default('present');
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->unique(['student_id', 'attendance_date']);
            $table->index(['attendance_date', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_logs');
    }
};
