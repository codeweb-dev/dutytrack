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
        Schema::create('evaluations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('ojt_assignment_id')->constrained('ojt_assignments')->cascadeOnDelete();
            $table->foreignId('evaluator_id')->constrained('users')->cascadeOnDelete();
            $table->enum('evaluator_type', ['company_supervisor']);
            $table->date('evaluation_date')->nullable();
            $table->date('inclusive_from')->nullable();
            $table->date('inclusive_to')->nullable();
            $table->string('cooperating_program')->nullable();
            $table->decimal('rating', 5, 2)->default(0);
            $table->json('ratings')->nullable();
            $table->text('remarks')->nullable();
            $table->enum('status', ['pending', 'submitted', 'reviewed'])->default('pending');
            $table->timestamps();
            $table->index(['status', 'evaluation_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
