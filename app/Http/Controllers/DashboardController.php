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

        // Calculate total current accumulated energy from cache for display
        $totalVolatileKwh = 0;
        foreach ($groups as $group) {
            foreach ($group->devices as $device) {
                $energy = Cache::get("energy:{$device->device_id}");
                if ($energy) {
                    $totalVolatileKwh += $energy;
                }
                $device->last_seen = Cache::get("last_seen:{$device->device_id}", 0);
            }
        }

        $estimatedCost = $totalVolatileKwh * $plnTariff;

        // Calculate Top 3 Devices using kWh
        $deviceEnergyList = collect();
        foreach ($groups as $group) {
            foreach ($group->devices as $device) {
                $energy = Cache::get("energy:{$device->device_id}", 0);
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

        $monthlyLogs = \Illuminate\Support\Facades\DB::table('daily_energy_logs')
            ->selectRaw('DATE_FORMAT(date, "%Y-%m") as label, SUM(total_kwh_harian) as total')
            ->where('date', '>=', now()->subMonths(12))
            ->groupBy('label')
            ->orderBy('label')
            ->get();

        $yearlyLogs = \Illuminate\Support\Facades\DB::table('daily_energy_logs')
            ->selectRaw('YEAR(date) as label, SUM(total_kwh_harian) as total')
            ->groupBy('label')
            ->orderBy('label')
            ->get();

        $chartData = [
            'daily' => $dailyLogs,
            'monthly' => $monthlyLogs,
            'yearly' => $yearlyLogs,
        ];

        return view('dashboard', compact('groups', 'plnTariff', 'totalVolatileKwh', 'estimatedCost', 'chartData', 'topDevices'));
    }
}
