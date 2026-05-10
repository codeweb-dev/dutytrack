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
        Schema::create('student_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assigned_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('ojt_assignment_id')->nullable()->constrained('ojt_assignments')->nullOnDelete();
            $table->uuid('task_group_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'in_review', 'done'])->default('pending');
            $table->enum('priority', ['low', 'normal', 'high'])->default('normal');
            $table->date('due_date')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->index(['status', 'sort_order']);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'status']);
            $table->index(['assigned_by', 'status']);
            $table->index(['task_group_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_tasks');
    }
};
