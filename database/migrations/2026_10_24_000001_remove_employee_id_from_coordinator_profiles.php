<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('coordinator_profiles', 'employee_id')) {
            Schema::table('coordinator_profiles', function (Blueprint $table): void {
                $table->dropUnique('professor_profiles_employee_id_unique');
                $table->dropColumn('employee_id');
            });
        }

        if (Schema::hasTable('audit_logs')) {
            DB::table('audit_logs')->select('id', 'old_values', 'new_values')->orderBy('id')->chunkById(100, function ($logs): void {
                foreach ($logs as $log) {
                    $updates = [];

                    foreach (['old_values', 'new_values'] as $column) {
                        $values = json_decode($log->{$column} ?? 'null', true);

                        if (is_array($values) && array_key_exists('employee_id', $values)) {
                            unset($values['employee_id']);
                            $updates[$column] = json_encode($values);
                        }
                    }

                    if ($updates) {
                        DB::table('audit_logs')->where('id', $log->id)->update($updates);
                    }
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('coordinator_profiles', function (Blueprint $table): void {
            $table->string('employee_id')->nullable()->unique();
        });
    }
};
