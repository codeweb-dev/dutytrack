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
        Schema::table('ojt_assignments', function (Blueprint $table) {
            $table->dropColumn(['status', 'remarks']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ojt_assignments', function (Blueprint $table) {
            $table->enum('status', ['pending', 'active', 'completed', 'cancelled'])->default('active')->after('company_supervisor_id');
            $table->text('remarks')->nullable()->after('status');
        });
    }
};
