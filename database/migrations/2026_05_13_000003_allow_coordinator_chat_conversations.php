<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement('ALTER TABLE chat_conversations MODIFY ojt_assignment_id BIGINT UNSIGNED NULL');
            DB::statement("ALTER TABLE chat_conversations MODIFY participant_role ENUM('professor', 'supervisor', 'coordinator') NOT NULL");

            return;
        }

        if ($driver !== 'sqlite') {
            Schema::table('chat_conversations', function (Blueprint $table): void {
                $table->foreignId('ojt_assignment_id')->nullable()->change();
                $table->enum('participant_role', ['professor', 'supervisor', 'coordinator'])->change();
            });
        }
    }

    public function down(): void
    {
        $this->deleteCoordinatorConversations();

        $driver = DB::getDriverName();

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            DB::statement("ALTER TABLE chat_conversations MODIFY participant_role ENUM('professor', 'supervisor') NOT NULL");
            DB::statement('ALTER TABLE chat_conversations MODIFY ojt_assignment_id BIGINT UNSIGNED NOT NULL');

            return;
        }

        if ($driver !== 'sqlite') {
            Schema::table('chat_conversations', function (Blueprint $table): void {
                $table->enum('participant_role', ['professor', 'supervisor'])->change();
                $table->foreignId('ojt_assignment_id')->nullable(false)->change();
            });
        }
    }

    private function deleteCoordinatorConversations(): void
    {
        DB::table('chat_conversations')
            ->where('participant_role', 'coordinator')
            ->orWhereNull('ojt_assignment_id')
            ->delete();
    }
};
