<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DailyEnergyLog;
use App\Models\SystemConfig;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index()
    {
        $reports = DailyEnergyLog::select(
            'date',
            DB::raw('count(distinct device_id) as device_count'),
            DB::raw('sum(total_kwh_harian) as total_kwh')
        )
        ->groupBy('date')
        ->orderBy('date', 'desc')
        ->paginate(15);

        $plnTariff = SystemConfig::where('key', 'pln_tariff')->value('value') ?? 1444.70;

        return view('reports.index', compact('reports', 'plnTariff'));
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

        $plnTariff = SystemConfig::where('key', 'pln_tariff')->value('value') ?? 1444.70;
        
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
}
