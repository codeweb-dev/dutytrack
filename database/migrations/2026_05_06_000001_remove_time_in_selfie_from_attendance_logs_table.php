<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Storage::disk('public')->deleteDirectory('attendance/selfies');

        Schema::table('attendance_logs', function (Blueprint $table) {
            if (Schema::hasColumn('attendance_logs', 'time_in_selfie_path')) {
                $table->dropColumn('time_in_selfie_path');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('attendance_logs', 'time_in_selfie_path')) {
                $table->string('time_in_selfie_path')->nullable()->after('time_in_location_accuracy');
            }
        });
    }
};
