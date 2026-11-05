<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['users', 'announcements', 'audit_logs', 'ojt_assignments', 'student_notes'] as $tableName) {
            if (! Schema::hasColumn($tableName, 'deleted_at')) {
                Schema::table($tableName, fn (Blueprint $table) => $table->softDeletes());
            }
        }

        if (! Schema::hasIndex('ojt_assignments', 'ojt_assignments_student_id_index')) {
            Schema::table('ojt_assignments', fn (Blueprint $table) => $table->index('student_id'));
        }

        if (Schema::hasIndex('ojt_assignments', ['student_id'], 'unique')) {
            Schema::table('ojt_assignments', fn (Blueprint $table) => $table->dropUnique(['student_id']));
        }
    }

    public function down(): void
    {
        if (! Schema::hasIndex('ojt_assignments', ['student_id'], 'unique')) {
            $duplicatesExist = DB::table('ojt_assignments')
                ->select('student_id')
                ->groupBy('student_id')
                ->havingRaw('COUNT(*) > 1')
                ->exists();

            if ($duplicatesExist) {
                throw new RuntimeException('Cannot restore unique OJT assignments while archived duplicates exist.');
            }

            Schema::table('ojt_assignments', fn (Blueprint $table) => $table->unique('student_id'));
        }

        if (Schema::hasIndex('ojt_assignments', 'ojt_assignments_student_id_index')) {
            Schema::table('ojt_assignments', fn (Blueprint $table) => $table->dropIndex('ojt_assignments_student_id_index'));
        }

        foreach (['users', 'announcements', 'audit_logs', 'ojt_assignments', 'student_notes'] as $tableName) {
            if (Schema::hasColumn($tableName, 'deleted_at')) {
                Schema::table($tableName, fn (Blueprint $table) => $table->dropSoftDeletes());
            }
        }
    }
};
