<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DailyEnergyLog;
use App\Models\SystemConfig;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $plnTariff = floatval(SystemConfig::where('key', 'pln_tariff')->value('value') ?? 1444.70);

        // Daily Reports
        $reports = DailyEnergyLog::select(
            'date',
            DB::raw('count(distinct device_id) as device_count'),
            DB::raw('sum(total_kwh_harian) as total_kwh')
        )
        ->groupBy('date')
        ->orderBy('date', 'desc')
        ->paginate(15, ['*'], 'daily_page');

        // Monthly Reports
        $driver = DB::connection()->getDriverName();
        $monthlyFormat = $driver === 'sqlite' ? "strftime('%Y-%m', date)" : 'DATE_FORMAT(date, "%Y-%m")';

        $monthlyReports = DailyEnergyLog::select(
            DB::raw("{$monthlyFormat} as month"),
            DB::raw('count(distinct device_id) as device_count'),
            DB::raw('sum(total_kwh_harian) as total_kwh')
        )
        ->groupBy('month')
        ->orderBy('month', 'desc')
        ->paginate(15, ['*'], 'monthly_page');

        return view('reports.index', compact('reports', 'monthlyReports', 'plnTariff'));
    }

    public function download($date)
    {
        $dateFormatted = Carbon::parse($date)->format('Y-m-d');
        
        $logs = DailyEnergyLog::with(['device.group'])
            ->whereDate('date', $dateFormatted)
            ->get();

        if ($logs->isEmpty()) {
            return redirect()->back()->with('error', 'No data found for the selected date.');
        }

        $plnTariff = floatval(SystemConfig::where('key', 'pln_tariff')->value('value') ?? 1444.70);
        
        $totalKwh = $logs->sum('total_kwh_harian');
        $totalCost = $totalKwh * $plnTariff;

        // Path to the base64 logo for PDF rendering
        $logoPath = public_path('logo.png');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoData = file_get_contents($logoPath);
            $logoBase64 = 'data:image/' . pathinfo($logoPath, PATHINFO_EXTENSION) . ';base64,' . base64_encode($logoData);
        }

        $pdf = Pdf::loadView('reports.pdf', [
            'logs' => $logs,
            'date' => Carbon::parse($date)->format('F d, Y'),
            'plnTariff' => $plnTariff,
            'totalKwh' => $totalKwh,
            'totalCost' => $totalCost,
            'logoBase64' => $logoBase64
        ]);

        return $pdf->download("daily_energy_report_{$dateFormatted}.pdf");
    }

    public function downloadMonthly($month)
    {
        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            return redirect()->back()->with('error', 'Invalid month format.');
        }

        $startOfMonth = Carbon::parse($month . '-01')->startOfMonth()->toDateString();
        $endOfMonth = Carbon::parse($month . '-01')->endOfMonth()->toDateString();

        $logs = DailyEnergyLog::with(['device.group'])
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->get();

        if ($logs->isEmpty()) {
            return redirect()->back()->with('error', 'No data found for the selected month.');
        }

        $plnTariff = floatval(SystemConfig::where('key', 'pln_tariff')->value('value') ?? 1444.70);
        
        $totalKwh = $logs->sum('total_kwh_harian');
        $totalCost = $totalKwh * $plnTariff;

        $uniqueDatesCount = $logs->pluck('date')->unique()->count();
        $avgDailyKwh = $uniqueDatesCount > 0 ? $totalKwh / $uniqueDatesCount : 0.0;

        // Group by Device Summary
        $deviceSummary = $logs->groupBy('device_id')->map(function ($deviceLogs) {
            $firstLog = $deviceLogs->first();
            return [
                'name' => $firstLog->device->name ?? 'Unknown Device',
                'device_id' => $firstLog->device->device_id ?? 'N/A',
                'group' => $firstLog->device->group->name ?? 'N/A',
                'total_kwh' => $deviceLogs->sum('total_kwh_harian')
            ];
        });

        // Group by Date Summary (Sorted by date)
        $dailySummary = $logs->groupBy('date')->map(function ($dayLogs) {
            return $dayLogs->sum('total_kwh_harian');
        })->sortKeys();

        // 1. Generate QuickChart URL
        $labels = $dailySummary->keys()->map(function ($d) {
            return Carbon::parse($d)->format('d');
        })->toArray();
        $data = $dailySummary->values()->toArray();

        $chartConfig = [
            'type' => 'bar',
            'data' => [
                'labels' => $labels,
                'datasets' => [[
                    'label' => 'Total Energy (kWh)',
                    'data' => $data,
                    'backgroundColor' => '#2563eb',
                    'borderWidth' => 0
                ]]
            ],
            'options' => [
                'title' => [
                    'display' => true,
                    'text' => 'Daily Energy Consumption (kWh) - ' . Carbon::parse($month . '-01')->format('F Y'),
                    'fontSize' => 14,
                    'fontColor' => '#1e293b'
                ],
                'legend' => [
                    'display' => false
                ],
                'scales' => [
                    'yAxes' => [[
                        'ticks' => [
                            'beginAtZero' => true,
                            'fontColor' => '#64748b'
                        ],
                        'gridLines' => [
                            'color' => '#e2e8f0'
                        ]
                    ]],
                    'xAxes' => [[
                        'ticks' => [
                            'fontColor' => '#64748b'
                        ],
                        'gridLines' => [
                            'display' => false
                        ]
                    ]]
                ]
            ]
        ];

        $chartUrl = 'https://quickchart.io/chart?w=600&h=250&c=' . urlencode(json_encode($chartConfig));

        // 2. Fetch Chart as Base64 (Robust & Offline-Safe if call fails)
        $chartBase64 = null;
        try {
            $chartResponse = Http::timeout(5)->get($chartUrl);
            if ($chartResponse->successful()) {
                $chartBase64 = 'data:image/png;base64,' . base64_encode($chartResponse->body());
            }
        } catch (\Exception $e) {
            // Graceful degradation
        }

        // Logo Base64
        $logoPath = public_path('logo.png');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoData = file_get_contents($logoPath);
            $logoBase64 = 'data:image/' . pathinfo($logoPath, PATHINFO_EXTENSION) . ';base64,' . base64_encode($logoData);
        }

        $pdf = Pdf::loadView('reports.monthly_pdf', [
            'month' => Carbon::parse($month . '-01')->format('F Y'),
            'plnTariff' => $plnTariff,
            'totalKwh' => $totalKwh,
            'totalCost' => $totalCost,
            'avgDailyKwh' => $avgDailyKwh,
            'deviceSummary' => $deviceSummary,
            'dailySummary' => $dailySummary,
            'chartBase64' => $chartBase64,
            'logoBase64' => $logoBase64
        ]);

        return $pdf->download("monthly_energy_report_{$month}.pdf");
    }
}
