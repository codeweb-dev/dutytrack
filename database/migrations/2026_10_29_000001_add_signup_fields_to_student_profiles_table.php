<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_profiles', function (Blueprint $table): void {
            $table->string('year_level')->default('4th Year')->after('course');
            $table->string('address')->nullable()->after('year_level');
            $table->string('guardian_name')->nullable()->after('address');
            $table->string('guardian_address')->nullable()->after('guardian_name');
            $table->string('guardian_contact_number', 20)->nullable()->after('guardian_address');
        });
    }

    public function down(): void
    {
        Schema::table('student_profiles', function (Blueprint $table): void {
            $table->dropColumn(['year_level', 'address', 'guardian_name', 'guardian_address', 'guardian_contact_number']);
        });
    }
};
