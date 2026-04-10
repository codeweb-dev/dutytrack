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
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();

            // link to users table
            $table->foreignId('user_id')
                ->unique()
                ->constrained()
                ->cascadeOnDelete();

            // student-specific fields
            $table->string('student_id')->unique();

            $table->string('course');       // BSCS, BSIT
            $table->string('year_level');   // 1,2,3,4
            $table->string('section')->nullable(); // A, B, etc

            $table->unsignedInteger('ojt_required_hours')->default(0);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_profiles');
    }
};
