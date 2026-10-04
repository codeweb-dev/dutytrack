<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chat_messages', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::dropIfExists('chat_typing_indicators');
    }

    public function down(): void
    {
        Schema::create('chat_typing_indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_conversation_id')->constrained('chat_conversations')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->unique(['chat_conversation_id', 'user_id'], 'chat_typing_unique');
            $table->index(['chat_conversation_id', 'expires_at']);
        });

        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }
};
