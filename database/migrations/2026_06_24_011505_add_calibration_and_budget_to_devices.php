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
        Schema::table('devices', function (Blueprint $table) {
            $table->decimal('voltage_multiplier', 5, 2)->default(1.00);
            $table->decimal('current_multiplier', 5, 2)->default(1.00);
            $table->decimal('monthly_budget_kwh', 10, 2)->nullable();
            $table->decimal('monthly_budget_cost', 10, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn([
                'voltage_multiplier',
                'current_multiplier',
                'monthly_budget_kwh',
                'monthly_budget_cost'
            ]);
        });
    }
};
