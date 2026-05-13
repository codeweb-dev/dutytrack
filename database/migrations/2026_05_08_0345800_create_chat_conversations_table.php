<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ojt_assignment_id')->nullable()->constrained('ojt_assignments')->cascadeOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('participant_id')->constrained('users')->cascadeOnDelete();
            $table->enum('participant_role', ['professor', 'supervisor', 'coordinator']);
            $table->timestamps();

            $table->unique(['ojt_assignment_id', 'participant_id', 'participant_role'], 'chat_conversation_unique');
            $table->index(['student_id', 'participant_role']);
            $table->index(['participant_id', 'participant_role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_conversations');
    }
};
