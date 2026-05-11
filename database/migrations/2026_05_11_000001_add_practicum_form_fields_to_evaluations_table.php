<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            $table->date('inclusive_from')->nullable()->after('evaluation_date');
            $table->date('inclusive_to')->nullable()->after('inclusive_from');
            $table->string('cooperating_program')->nullable()->after('inclusive_to');
            $table->json('ratings')->nullable()->after('rating');
        });
    }

    public function down(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            $table->dropColumn([
                'inclusive_from',
                'inclusive_to',
                'cooperating_program',
                'ratings',
            ]);
        });
    }
};
