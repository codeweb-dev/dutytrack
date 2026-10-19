<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('duty_hour_logs');
    }

    public function down(): void
    {
        (require __DIR__.'/2026_04_11_060343_create_duty_hour_logs_table.php')->up();
    }
};
