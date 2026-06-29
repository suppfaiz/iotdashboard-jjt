<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Group;
use App\Models\Device;
use App\Models\SystemConfig;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $groups = Group::with(['devices' => function($q) {
            $q->where('status', true);
        }])->get();

        $plnTariff = SystemConfig::where('key', 'pln_tariff')->value('value') ?? 1444.70;

        // Fetch current month daily log sums grouped by device
        $monthlyLogsGrouped = \Illuminate\Support\Facades\DB::table('daily_energy_logs')
            ->selectRaw('device_id, SUM(total_kwh_harian) as monthly_sum')
            ->where('date', '>=', now()->startOfMonth()->toDateString())
            ->groupBy('device_id')
            ->get()
            ->keyBy('device_id');

        $daysElapsed = max(1, now()->day);
        $remainingDays = max(0, now()->daysInMonth - $daysElapsed);

        // Calculate total current accumulated energy and multi-tariff cost from cache
        $totalVolatileKwh = 0;
        $estimatedCost = 0;
        foreach ($groups as $group) {
            foreach ($group->devices as $device) {
                $energy = Cache::get("daily_energy:{$device->device_id}");
                $voltage = Cache::get("voltage:{$device->device_id}");
                $current = Cache::get("current:{$device->device_id}");
                $power = Cache::get("power:{$device->device_id}");

                if ($energy === null || $voltage === null || $current === null || $power === null) {
                    $lastLog = \App\Models\HourlyEnergyLog::where('device_id', $device->id)
                        ->orderBy('logged_at', 'desc')
                        ->first();
                    if ($lastLog) {
                        $energy = $energy ?? $lastLog->energy;
                        $voltage = $voltage ?? $lastLog->voltage;
                        $current = $current ?? $lastLog->current;
                        $power = $power ?? $lastLog->power;
                    }
                }

                $energy = floatval($energy ?? 0.0);
                $device->voltage = floatval($voltage ?? 0.0);
                $device->current = floatval($current ?? 0.0);
                $device->power = floatval($power ?? 0.0);
                $device->energy = $energy;

                $totalVolatileKwh += $energy;
                
                $deviceCost = Cache::get("daily_cost:{$device->device_id}");
                if ($deviceCost === null) {
                    $deviceCost = $energy * $plnTariff;
                }
                $estimatedCost += $deviceCost;
                
                $device->last_seen = Cache::get("last_seen:{$device->device_id}", 0);

                // Calculate per-device budget projections
                $deviceMonthlySum = floatval($monthlyLogsGrouped->get($device->id)->monthly_sum ?? 0.0);
                $deviceMonthlySum += $energy; // Add volatile real-time today energy

                $deviceAvgDaily = $deviceMonthlySum / $daysElapsed;
                $deviceProjectedKwh = $deviceMonthlySum + ($deviceAvgDaily * $remainingDays);

                $device->current_month_kwh = $deviceMonthlySum;
                $device->current_month_cost = $deviceMonthlySum * $plnTariff;
                $device->projected_kwh = $deviceProjectedKwh;
                $device->projected_cost = $deviceProjectedKwh * $plnTariff;
            }
        }

        // Predictive Billing Forecasting
        // Get daily logs for all active devices in the past 7 days
        $past7DaysLogs = \Illuminate\Support\Facades\DB::table('daily_energy_logs')
            ->selectRaw('date, SUM(total_kwh_harian) as daily_sum')
            ->where('date', '>=', now()->subDays(7)->toDateString())
            ->groupBy('date')
            ->get();

        $numDays = $past7DaysLogs->count();
        $avgDailyKwh = 0;
        if ($numDays > 0) {
            $avgDailyKwh = $past7DaysLogs->sum('daily_sum') / $numDays;
        } else {
            $avgDailyKwh = $totalVolatileKwh;
        }

        // Current Month Energy
        $currentMonthStart = now()->startOfMonth()->toDateString();
        $currentMonthKwh = \Illuminate\Support\Facades\DB::table('daily_energy_logs')
            ->where('date', '>=', $currentMonthStart)
            ->sum('total_kwh_harian') ?? 0;

        $currentMonthCost = $currentMonthKwh * $plnTariff;
        $remainingDays = max(0, now()->daysInMonth - now()->day);
        
        $projectedBilling = $currentMonthCost + ($avgDailyKwh * $remainingDays * $plnTariff);

        // Calculate Top 3 Devices using kWh
        $deviceEnergyList = collect();
        foreach ($groups as $group) {
            foreach ($group->devices as $device) {
                $energy = Cache::get("daily_energy:{$device->device_id}", 0);
                $deviceEnergyList->push([
                    'name' => $device->name,
                    'device_id' => $device->device_id,
                    'group_name' => $group->name,
                    'energy' => floatval($energy),
                ]);
            }
        }
        $topDevices = $deviceEnergyList->sortByDesc('energy')->take(3);

        // Compute active warnings dynamically on page load
        $activeWarnings = [];
        $vMin = floatval(SystemConfig::where('key', 'alert_voltage_min')->value('value') ?? 200.00);
        $vMax = floatval(SystemConfig::where('key', 'alert_voltage_max')->value('value') ?? 240.00);
        $pMax = floatval(SystemConfig::where('key', 'alert_power_max')->value('value') ?? 2200.00);

        foreach ($groups as $group) {
            foreach ($group->devices as $device) {
                // 1. Heartbeat check (offline/inactive)
                $lastSeen = Cache::get("last_seen:{$device->device_id}", 0);
                $isOffline = ($lastSeen === 0 || (now()->timestamp - $lastSeen) > 300); // 5 minutes
                if ($isOffline) {
                    $activeWarnings[] = [
                        'device_id' => $device->device_id,
                        'device_name' => $device->name,
                        'type' => 'offline',
                        'message' => "Perangkat offline. Terakhir terlihat: " . ($lastSeen > 0 ? \Carbon\Carbon::createFromTimestamp($lastSeen)->diffForHumans() : 'Belum pernah online'),
                        'severity' => 'danger'
                    ];
                } else {
                    // 2. Voltage check
                    $voltage = floatval(Cache::get("voltage:{$device->device_id}", 0));
                    if ($voltage > 0 && ($voltage < $vMin || $voltage > $vMax)) {
                        $activeWarnings[] = [
                            'device_id' => $device->device_id,
                            'device_name' => $device->name,
                            'type' => 'voltage',
                            'message' => "Voltase tidak stabil: {$voltage} V (Batas aman: {$vMin} - {$vMax} V)",
                            'severity' => 'warning'
                        ];
                    }

                    // 3. Power check
                    $power = floatval(Cache::get("power:{$device->device_id}", 0));
                    if ($power > $pMax) {
                        $activeWarnings[] = [
                            'device_id' => $device->device_id,
                            'device_name' => $device->name,
                            'type' => 'power',
                            'message' => "Konsumsi daya melebihi batas beban maksimum: {$power} W (Batas aman: maks {$pMax} W)",
                            'severity' => 'warning'
                        ];
                    }
                }

                // 4. Budget check
                if ($device->monthly_budget_kwh) {
                    $kwhPercent = $device->monthly_budget_kwh > 0 ? ($device->current_month_kwh / $device->monthly_budget_kwh) * 100 : 0;
                    if ($kwhPercent >= 100) {
                        $activeWarnings[] = [
                            'device_id' => $device->device_id,
                            'device_name' => $device->name,
                            'type' => 'budget',
                            'message' => "Anggaran energi bulanan terlampaui 100% (" . number_format($device->current_month_kwh, 2) . " / " . number_format($device->monthly_budget_kwh, 0) . " kWh)",
                            'severity' => 'danger'
                        ];
                    } elseif ($kwhPercent >= 80) {
                        $activeWarnings[] = [
                            'device_id' => $device->device_id,
                            'device_name' => $device->name,
                            'type' => 'budget',
                            'message' => "Anggaran energi bulanan terlampaui 80% (" . number_format($device->current_month_kwh, 2) . " / " . number_format($device->monthly_budget_kwh, 0) . " kWh)",
                            'severity' => 'warning'
                        ];
                    }
                }
            }
        }

        // Fetch Graph Data (Daily, Monthly, Yearly)
        $dailyLogs = \Illuminate\Support\Facades\DB::table('daily_energy_logs')
            ->selectRaw('date as label, SUM(total_kwh_harian) as total')
            ->where('date', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $driver = \Illuminate\Support\Facades\DB::connection()->getDriverName();
        $monthlyFormat = $driver === 'sqlite' ? "strftime('%Y-%m', date)" : 'DATE_FORMAT(date, "%Y-%m")';
        $yearlyFormat = $driver === 'sqlite' ? "strftime('%Y', date)" : 'YEAR(date)';

        $monthlyLogs = \Illuminate\Support\Facades\DB::table('daily_energy_logs')
            ->selectRaw("{$monthlyFormat} as label, SUM(total_kwh_harian) as total")
            ->where('date', '>=', now()->subMonths(12))
            ->groupBy('label')
            ->orderBy('label')
            ->get();

        $yearlyLogs = \Illuminate\Support\Facades\DB::table('daily_energy_logs')
            ->selectRaw("{$yearlyFormat} as label, SUM(total_kwh_harian) as total")
            ->groupBy('label')
            ->orderBy('label')
            ->get();

        // Weekly comparison (This Week vs Last Week)
        $rawComparisonLogs = \Illuminate\Support\Facades\DB::table('daily_energy_logs')
            ->selectRaw('date, SUM(total_kwh_harian) as daily_sum')
            ->where('date', '>=', now()->subDays(13)->toDateString())
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $thisWeekData = [];
        $lastWeekData = [];
        $comparisonLabels = [];

        for ($i = 6; $i >= 0; $i--) {
            $thisWeekDateObj = now()->subDays($i);
            $thisWeekDateStr = $thisWeekDateObj->toDateString();
            $comparisonLabels[] = $thisWeekDateObj->isoFormat('dddd'); // e.g. "Monday"

            $lastWeekDateStr = now()->subDays($i + 7)->toDateString();

            $thisWeekData[] = floatval($rawComparisonLogs->get($thisWeekDateStr)->daily_sum ?? 0.0);
            $lastWeekData[] = floatval($rawComparisonLogs->get($lastWeekDateStr)->daily_sum ?? 0.0);
        }

        $chartData = [
            'daily' => $dailyLogs,
            'monthly' => $monthlyLogs,
            'yearly' => $yearlyLogs,
            'comparison' => [
                'labels' => $comparisonLabels,
                'this_week' => $thisWeekData,
                'last_week' => $lastWeekData,
            ],
        ];

        return view('dashboard', compact('groups', 'plnTariff', 'totalVolatileKwh', 'estimatedCost', 'chartData', 'topDevices', 'projectedBilling', 'activeWarnings', 'vMin', 'vMax', 'pMax'));
    }

    public function changelog()
    {
        return view('changelog');
    }
}
