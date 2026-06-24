<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\SystemConfig;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $configs = [
            'pln_tariff_wbp' => '2000.00',
            'pln_tariff_lwbp' => '1444.70',
            'wbp_start' => '17:00',
            'wbp_end' => '22:00',
            'telegram_bot_token' => '',
            'telegram_chat_id' => '',
            'alert_voltage_min' => '200.00',
            'alert_voltage_max' => '240.00',
            'alert_power_max' => '2200.00',
        ];

        foreach ($configs as $key => $value) {
            SystemConfig::firstOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to rollback since it only seeds configs
    }
};
