<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $columns = array_values(array_filter(
            ['overtime_enabled', 'remarks'],
            fn (string $column) => Schema::hasColumn('attendance_logs', $column),
        ));

        if ($columns) {
            Schema::table('attendance_logs', fn (Blueprint $table) => $table->dropColumn($columns));
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('attendance_logs', 'overtime_enabled')) {
            Schema::table('attendance_logs', fn (Blueprint $table) => $table->boolean('overtime_enabled')->default(false));
        }

        if (! Schema::hasColumn('attendance_logs', 'remarks')) {
            Schema::table('attendance_logs', fn (Blueprint $table) => $table->text('remarks')->nullable());
        }
    }
};
