<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('ojt_assignments')
            ->where('ojt_type', 'internal')
            ->update(['company_supervisor_id' => null]);

        DB::table('ojt_assignments')
            ->where('ojt_type', 'external')
            ->update(['professor_id' => null]);
    }

    public function down(): void
    {
        //
    }
};
