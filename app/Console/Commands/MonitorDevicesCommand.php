<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Device;
use App\Models\SystemConfig;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class MonitorDevicesCommand extends Command
{
    protected $signature = 'devices:monitor';
    protected $description = 'Monitor device heartbeats and monthly energy/cost budgets and send Telegram alerts';

    public function handle()
    {
        $botToken = SystemConfig::where('key', 'telegram_bot_token')->value('value');
        $chatId = SystemConfig::where('key', 'telegram_chat_id')->value('value');

        if (empty($botToken) || empty($chatId)) {
            $this->error('Telegram Bot token or Chat ID is not configured.');
            return;
        }

        $devices = Device::where('status', true)->get();
        $plnTariff = floatval(SystemConfig::where('key', 'pln_tariff')->value('value') ?? 1444.70);
        $totalDevices = $devices->count();
        $offlineCount = 0;

        foreach ($devices as $device) {
            $this->info("Monitoring device: {$device->name} ({$device->device_id})");

            // 1. Heartbeat Check
            $lastSeen = Cache::get("last_seen:{$device->device_id}", 0);
            $isOffline = ($lastSeen === 0 || (now()->timestamp - $lastSeen) > 300); // 5 minutes threshold
            $offlineCacheKey = "offline_alert_sent:{$device->device_id}";

            if ($isOffline) {
                $offlineCount++;
                if (!Cache::has($offlineCacheKey)) {
                    $lastSeenStr = $lastSeen > 0 ? Carbon::createFromTimestamp($lastSeen)->diffForHumans() : 'Never';
                    $message = "⚠️ <b>DEVICE OFFLINE ALERT</b>\nPerangkat <b>{$device->name}</b> terdeteksi OFFLINE.\nTerakhir terlihat: {$lastSeenStr}";
                    $this->sendTelegram($botToken, $chatId, $message);
                    Cache::put($offlineCacheKey, true, now()->addDays(30));
                }
            } else {
                if (Cache::has($offlineCacheKey)) {
                    $message = "✅ <b>DEVICE ONLINE RECOVERY</b>\nPerangkat <b>{$device->name}</b> telah ONLINE kembali.";
                    $this->sendTelegram($botToken, $chatId, $message);
                    Cache::forget($offlineCacheKey);
                }
            }

            // 2. Budget Check
            if ($device->monthly_budget_kwh || $device->monthly_budget_cost) {
                // Calculate current month's kWh
                $currentMonthKwh = DB::table('daily_energy_logs')
                    ->where('device_id', $device->id)
                    ->where('date', '>=', now()->startOfMonth()->toDateString())
                    ->sum('total_kwh_harian') ?? 0.0;
                
                $currentMonthKwh = floatval($currentMonthKwh);

                // kWh Budget Check
                if ($device->monthly_budget_kwh) {
                    $budgetKwh = floatval($device->monthly_budget_kwh);
                    $kwh100Key = "budget_kwh_100_alert_sent:{$device->device_id}";
                    $kwh80Key = "budget_kwh_80_alert_sent:{$device->device_id}";

                    if ($currentMonthKwh >= $budgetKwh) {
                        if (!Cache::has($kwh100Key)) {
                            $message = "⚠️ <b>BATAS ANGGARAN ENERGI 100%</b>\nPerangkat <b>{$device->name}</b> telah melebihi 100% anggaran energi bulanan ({$budgetKwh} kWh).\nPenggunaan saat ini: <b>" . number_format($currentMonthKwh, 2) . " kWh</b>";
                            $this->sendTelegram($botToken, $chatId, $message);
                            Cache::put($kwh100Key, true, now()->addDay()); // 24-hour cooldown
                        }
                    } elseif ($currentMonthKwh >= ($budgetKwh * 0.8)) {
                        if (!Cache::has($kwh80Key)) {
                            $message = "⚠️ <b>BATAS ANGGARAN ENERGI 80%</b>\nPerangkat <b>{$device->name}</b> telah melebihi 80% anggaran energi bulanan ({$budgetKwh} kWh).\nPenggunaan saat ini: <b>" . number_format($currentMonthKwh, 2) . " kWh</b>";
                            $this->sendTelegram($botToken, $chatId, $message);
                            Cache::put($kwh80Key, true, now()->addDay()); // 24-hour cooldown
                        }
                    }
                }

                // Cost Budget Check
                if ($device->monthly_budget_cost) {
                    $budgetCost = floatval($device->monthly_budget_cost);
                    $currentMonthCost = $currentMonthKwh * $plnTariff;
                    $cost100Key = "budget_cost_100_alert_sent:{$device->device_id}";
                    $cost80Key = "budget_cost_80_alert_sent:{$device->device_id}";

                    if ($currentMonthCost >= $budgetCost) {
                        if (!Cache::has($cost100Key)) {
                            $message = "⚠️ <b>BATAS ANGGARAN BIAYA 100%</b>\nPerangkat <b>{$device->name}</b> telah melebihi 100% anggaran biaya bulanan (Rp " . number_format($budgetCost, 0, ',', '.') . ").\nEstimasi biaya saat ini: <b>Rp " . number_format($currentMonthCost, 0, ',', '.') . "</b>";
                            $this->sendTelegram($botToken, $chatId, $message);
                            Cache::put($cost100Key, true, now()->addDay()); // 24-hour cooldown
                        }
                    } elseif ($currentMonthCost >= ($budgetCost * 0.8)) {
                        if (!Cache::has($cost80Key)) {
                            $message = "⚠️ <b>BATAS ANGGARAN BIAYA 80%</b>\nPerangkat <b>{$device->name}</b> telah melebihi 80% anggaran biaya bulanan (Rp " . number_format($budgetCost, 0, ',', '.') . ").\nEstimasi biaya saat ini: <b>Rp " . number_format($currentMonthCost, 0, ',', '.') . "</b>";
                            $this->sendTelegram($botToken, $chatId, $message);
                            Cache::put($cost80Key, true, now()->addDay()); // 24-hour cooldown
                        }
                    }
                }
            }
        }

        // 3. Outage Logs Tracking (Detect if ALL devices are offline -> full power outage)
        if ($totalDevices > 0) {
            $allOffline = ($offlineCount === $totalDevices);
            $activeOutage = \App\Models\OutageLog::whereNull('outage_end')->first();

            if ($allOffline) {
                if (!$activeOutage) {
                    \App\Models\OutageLog::create([
                        'outage_start' => now(),
                    ]);
                }
            } else {
                if ($activeOutage) {
                    $end = now();
                    $duration = $end->diffInSeconds($activeOutage->outage_start);
                    $activeOutage->update([
                        'outage_end' => $end,
                        'duration_seconds' => $duration,
                    ]);
                }
            }
        }

        $this->info('Device monitoring completed successfully.');
    }

    protected function sendTelegram($botToken, $chatId, $messageText)
    {
        try {
            $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
            \Illuminate\Support\Facades\Http::post($url, [
                'chat_id' => $chatId,
                'text' => $messageText,
                'parse_mode' => 'HTML'
            ]);
            $this->info("Telegram Alert Sent: " . $messageText);
        } catch (\Exception $e) {
            $this->error("Failed to send Telegram alert: " . $e->getMessage());
        }
    }
}
