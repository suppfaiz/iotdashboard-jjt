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
        Schema::table('daily_energy_logs', function (Blueprint $table) {
            $table->index('date');
            $table->index(['device_id', 'date']);
        });

        Schema::table('hourly_energy_logs', function (Blueprint $table) {
            $table->index('logged_at');
            $table->index(['device_id', 'logged_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_energy_logs', function (Blueprint $table) {
            $table->dropIndex(['date']);
            $table->dropIndex(['device_id', 'date']);
        });

        Schema::table('hourly_energy_logs', function (Blueprint $table) {
            $table->dropIndex(['logged_at']);
            $table->dropIndex(['device_id', 'logged_at']);
        });
    }
};
