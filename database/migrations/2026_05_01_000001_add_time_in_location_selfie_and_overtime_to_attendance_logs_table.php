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
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->decimal('time_in_latitude', 10, 7)->nullable()->after('time_in');
            $table->decimal('time_in_longitude', 10, 7)->nullable()->after('time_in_latitude');
            $table->unsignedInteger('time_in_location_accuracy')->nullable()->after('time_in_longitude');
            $table->string('time_in_selfie_path')->nullable()->after('time_in_location_accuracy');
            $table->boolean('overtime_enabled')->default(false)->after('hours_rendered');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendance_logs', function (Blueprint $table) {
            $table->dropColumn([
                'time_in_latitude',
                'time_in_longitude',
                'time_in_location_accuracy',
                'time_in_selfie_path',
                'overtime_enabled',
            ]);
        });
    }
};
