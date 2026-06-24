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

        // Calculate total current accumulated energy and multi-tariff cost from cache
        $totalVolatileKwh = 0;
        $estimatedCost = 0;
        foreach ($groups as $group) {
            foreach ($group->devices as $device) {
                $energy = Cache::get("daily_energy:{$device->device_id}", 0);
                $totalVolatileKwh += $energy;
                
                $deviceCost = Cache::get("daily_cost:{$device->device_id}");
                if ($deviceCost === null) {
                    $deviceCost = $energy * $plnTariff;
                }
                $estimatedCost += $deviceCost;
                
                $device->last_seen = Cache::get("last_seen:{$device->device_id}", 0);
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

        return view('dashboard', compact('groups', 'plnTariff', 'totalVolatileKwh', 'estimatedCost', 'chartData', 'topDevices', 'projectedBilling'));
    }

    public function changelog()
    {
        return view('changelog');
    }
}
