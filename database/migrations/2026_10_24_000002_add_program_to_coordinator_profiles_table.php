<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('coordinator_profiles', function (Blueprint $table): void {
            $table->string('program')->nullable()->after('department');
        });

        DB::table('coordinator_profiles')
            ->whereIn('department', ['Computer Science', 'Information Technology'])
            ->update([
                'program' => DB::raw('department'),
                'department' => 'Computer Studies',
            ]);
    }

    public function down(): void
    {
        DB::table('coordinator_profiles')
            ->where('department', 'Computer Studies')
            ->whereIn('program', ['Computer Science', 'Information Technology'])
            ->update(['department' => DB::raw('program')]);

        Schema::table('coordinator_profiles', function (Blueprint $table): void {
            $table->dropColumn('program');
        });
    }
};
