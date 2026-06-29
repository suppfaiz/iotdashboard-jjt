<?php

namespace App\Http\Controllers;

use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    public function exportCsv(Device $device)
    {
        // Explicit auth verification check
        if (!auth()->check()) {
            abort(403, 'Unauthorized.');
        }

        $plnTariff = floatval(\App\Models\SystemConfig::where('key', 'pln_tariff')->value('value') ?? 1444.70);

        $logs = DB::table('daily_energy_logs')
            ->where('device_id', $device->id)
            ->orderBy('date', 'desc')
            ->get();

        $response = new StreamedResponse(function () use ($logs, $plnTariff, $device) {
            $handle = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($handle, ['Device Name', 'Device ID', 'Date', 'Daily Energy Consumption (kWh)', 'PLN Tariff (IDR/kWh)', 'Estimated Cost (IDR)']);

            foreach ($logs as $log) {
                $cost = floatval($log->total_kwh_harian) * $plnTariff;
                fputcsv($handle, [
                    $device->name,
                    $device->device_id,
                    $log->date,
                    number_format(floatval($log->total_kwh_harian), 4, '.', ''),
                    number_format($plnTariff, 2, '.', ''),
                    number_format($cost, 2, '.', '')
                ]);
            }

            fclose($handle);
        });

        $fileName = 'energy_log_' . $device->device_id . '_' . now()->format('Ymd') . '.csv';

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }

    public function exportMonthlyCsv($month)
    {
        if (!auth()->check()) {
            abort(403, 'Unauthorized.');
        }

        if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
            abort(400, 'Invalid month format.');
        }

        $startOfMonth = \Carbon\Carbon::parse($month . '-01')->startOfMonth()->toDateString();
        $endOfMonth = \Carbon\Carbon::parse($month . '-01')->endOfMonth()->toDateString();

        $plnTariff = floatval(\App\Models\SystemConfig::where('key', 'pln_tariff')->value('value') ?? 1444.70);

        $logs = DB::table('daily_energy_logs')
            ->join('devices', 'daily_energy_logs.device_id', '=', 'devices.id')
            ->leftJoin('groups', 'devices.group_id', '=', 'groups.id')
            ->select('daily_energy_logs.*', 'devices.name as device_name', 'devices.device_id as dev_id', 'groups.name as group_name')
            ->whereBetween('daily_energy_logs.date', [$startOfMonth, $endOfMonth])
            ->orderBy('daily_energy_logs.date', 'asc')
            ->orderBy('devices.name', 'asc')
            ->get();

        $response = new StreamedResponse(function () use ($logs, $plnTariff) {
            $handle = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($handle, ['Date', 'Device Name', 'Device ID', 'Group Name', 'Energy Consumption (kWh)', 'PLN Tariff (IDR/kWh)', 'Estimated Cost (IDR)']);

            foreach ($logs as $log) {
                $cost = floatval($log->total_kwh_harian) * $plnTariff;
                fputcsv($handle, [
                    $log->date,
                    $log->device_name,
                    $log->dev_id,
                    $log->group_name ?? 'N/A',
                    number_format(floatval($log->total_kwh_harian), 4, '.', ''),
                    number_format($plnTariff, 2, '.', ''),
                    number_format($cost, 2, '.', '')
                ]);
            }

            fclose($handle);
        });

        $fileName = 'monthly_energy_report_' . $month . '.csv';

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $fileName . '"');
        $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');

        return $response;
    }
}
