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

            $table->date('attendance_date');
            $table->timestamp('time_in')->nullable();
            $table->timestamp('time_out')->nullable();

            $table->unsignedInteger('break_minutes')->default(0);
            $table->decimal('hours_rendered', 5, 2)->default(0);

            $table->enum('status', ['present', 'late', 'absent', 'incomplete'])->default('present');
            $table->string('source')->nullable(); // qr, manual, biometric, etc.
            $table->text('remarks')->nullable();

            $table->boolean('is_flagged')->default(false);
            $table->text('flag_reason')->nullable();

            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();

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
