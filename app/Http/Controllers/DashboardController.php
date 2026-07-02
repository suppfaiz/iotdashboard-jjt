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
        $electricianWhatsapp = SystemConfig::where('key', 'electrician_whatsapp')->value('value') ?? '';

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

        return view('dashboard', compact('groups', 'plnTariff', 'totalVolatileKwh', 'estimatedCost', 'chartData', 'topDevices', 'projectedBilling', 'activeWarnings', 'vMin', 'vMax', 'pMax', 'electricianWhatsapp'));
    }

    public function changelog()
    {
        return view('changelog');
    }

    public function chatbotAnalysis()
    {
        $plnTariff = floatval(SystemConfig::where('key', 'pln_tariff')->value('value') ?? 1444.70);
        $devices = Device::where('status', true)->get();
        
        $totalDevices = $devices->count();
        $onlineDevices = 0;
        $offlineDevices = 0;
        $totalKwhToday = 0.0;

        $deviceEnergyList = collect();

        foreach ($devices as $device) {
            $lastSeen = Cache::get("last_seen:{$device->device_id}", 0);
            $isOnline = $lastSeen > 0 && (now()->timestamp - $lastSeen) < 300;
            if ($isOnline) {
                $onlineDevices++;
            } else {
                $offlineDevices++;
            }

            $energy = floatval(Cache::get("daily_energy:{$device->device_id}", 0.0));
            $totalKwhToday += $energy;

            $deviceEnergyList->push([
                'name' => $device->name,
                'energy' => $energy
            ]);
        }

        // Top Consumer today
        $topConsumer = $deviceEnergyList->sortByDesc('energy')->first();

        // Past 7 days logs
        $past7DaysLogs = \Illuminate\Support\Facades\DB::table('daily_energy_logs')
            ->selectRaw('date, SUM(total_kwh_harian) as daily_sum')
            ->where('date', '>=', now()->subDays(7)->toDateString())
            ->groupBy('date')
            ->get();

        $numDays = $past7DaysLogs->count();
        $avgDailyKwh = $numDays > 0 ? $past7DaysLogs->sum('daily_sum') / $numDays : $totalKwhToday;
        $avgDailyKwh = round($avgDailyKwh, 3);

        $currentMonthStart = now()->startOfMonth()->toDateString();
        $currentMonthKwh = \Illuminate\Support\Facades\DB::table('daily_energy_logs')
            ->where('date', '>=', $currentMonthStart)
            ->sum('total_kwh_harian') ?? 0.0;

        $currentMonthCost = $currentMonthKwh * $plnTariff;
        $remainingDays = max(0, now()->daysInMonth - now()->day);
        $projectedKwh = $currentMonthKwh + ($avgDailyKwh * $remainingDays);
        $projectedBilling = $projectedKwh * $plnTariff;

        // Warnings count
        $warningsCount = 0;
        $vMin = floatval(SystemConfig::where('key', 'alert_voltage_min')->value('value') ?? 200.00);
        $vMax = floatval(SystemConfig::where('key', 'alert_voltage_max')->value('value') ?? 240.00);
        $pMax = floatval(SystemConfig::where('key', 'alert_power_max')->value('value') ?? 2200.00);

        foreach ($devices as $device) {
            $lastSeen = Cache::get("last_seen:{$device->device_id}", 0);
            $isOffline = ($lastSeen === 0 || (now()->timestamp - $lastSeen) > 300);
            if ($isOffline) {
                $warningsCount++;
            } else {
                $voltage = floatval(Cache::get("voltage:{$device->device_id}", 0));
                if ($voltage > 0 && ($voltage < $vMin || $voltage > $vMax)) {
                    $warningsCount++;
                }
                $power = floatval(Cache::get("power:{$device->device_id}", 0));
                if ($power > $pMax) {
                    $warningsCount++;
                }
            }
        }
        // Generate prompt for AI
        $prompt = "Kamu adalah YukAnalisaListrikmu, asisten AI profesional untuk sistem IoT pemantauan energi kelistrikan PT Jamkrida Jateng.
Tugasmu adalah memberikan analisis dan saran penghematan listrik yang ramah, sopan, ringkas, dan profesional.

Berikut adalah data sensor real-time saat ini:
- PLN Tariff: Rp " . number_format($plnTariff, 2, ',', '.') . " per kWh
- Total konsumsi energi hari ini: " . number_format($totalKwhToday, 3) . " kWh
- Estimasi biaya listrik hari ini: Rp " . number_format($totalKwhToday * $plnTariff, 0, ',', '.') . "
- Rata-rata konsumsi harian (7 hari terakhir): " . number_format($avgDailyKwh, 3) . " kWh
- Proyeksi total konsumsi energi akhir bulan ini: " . number_format($projectedKwh, 2) . " kWh
- Proyeksi estimasi tagihan listrik akhir bulan ini: Rp " . number_format($projectedBilling, 0, ',', '.') . "
- Jumlah perangkat aktif: " . $totalDevices . " perangkat (" . $onlineDevices . " Online, " . $offlineDevices . " Offline)
- Jumlah peringatan (alerts/warnings) aktif saat ini: " . $warningsCount . " peringatan.
" . ($topConsumer && $topConsumer['energy'] > 0 ? "- Konsumen listrik terbesar hari ini: " . $topConsumer['name'] . " dengan pemakaian " . number_format($topConsumer['energy'], 3) . " kWh." : "") . "

Berikan laporan analisis singkat, sebutkan jika ada pemborosan (pemakaian hari ini di atas rata-rata) atau alat offline, dan berikan 2-3 rekomendasi penghematan praktis. 
Tuliskan jawaban langsung dalam format HTML/Blade bersih yang rapi (gunakan tag seperti <b>, <ul>, <li>, ⚠️, 💡, ✅, 🔮, ⚡) agar nyaman dibaca di chat widget. Jawab secara ringkas (maksimal 250 kata) dan mulailah dengan sapaan hormat.";

        $geminiKey = trim(SystemConfig::where('key', 'gemini_api_key')->value('value') ?? '');
        if (empty($geminiKey)) {
            $geminiKey = trim(config('services.gemini.key') ?? '');
        }
        if (!empty($geminiKey)) {
            try {
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'x-goog-api-key' => $geminiKey,
                ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent", [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ]
                ]);

                if ($response->successful()) {
                    $result = $response->json();
                    $aiResponse = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;
                    if (!empty($aiResponse)) {
                        $aiResponse = preg_replace('/^```(?:html)?|```$/i', '', trim($aiResponse));
                        return response()->json([
                            'status' => 'success',
                            'analysis' => $aiResponse
                        ]);
                    }
                }
            } catch (\Exception $e) {
                // Fallback to static template
            }
        }

        // Generate dynamic fallback advice
        $analysisText = "📊 <b>LAPORAN ANALISIS ENERGI KELISTRIKAN</b><br>";
        $analysisText .= "<b>PT JAMKRIDA JATENG</b><br><br>";
        $analysisText .= "Yth. Manajemen / Administrator,<br><br>";
        $analysisText .= "Berikut adalah laporan ringkas pemantauan dan analisis penggunaan energi listrik real-time:<br><br>";

        $analysisText .= "⚡ <b>1. KONDISI KONSUMSI DAYA HARI INI</b><br>";
        $analysisText .= "• Akumulasi Energi: <b>" . number_format($totalKwhToday, 3) . " kWh</b><br>";
        $analysisText .= "• Estimasi Biaya Harian: <b>Rp " . number_format($totalKwhToday * $plnTariff, 0, ',', '.') . "</b><br>";
        $analysisText .= "• Rata-rata Harian (7 Hari Terakhir): <b>" . number_format($avgDailyKwh, 3) . " kWh/hari</b><br><br>";

        $analysisText .= "🔮 <b>2. PROYEKSI AKHIR BULAN</b><br>";
        $analysisText .= "• Estimasi Total Energi: <b>" . number_format($projectedKwh, 2) . " kWh</b><br>";
        $analysisText .= "• Proyeksi Tagihan Listrik: <b>Rp " . number_format($projectedBilling, 0, ',', '.') . "</b><br><br>";

        $analysisText .= "🖥️ <b>3. STATUS INFRASTRUKTUR & PERANGKAT</b><br>";
        $analysisText .= "• Total Perangkat: <b>{$totalDevices} Unit</b> ({$onlineDevices} Online / {$offlineDevices} Offline)<br>";
        if ($topConsumer && $topConsumer['energy'] > 0) {
            $analysisText .= "• Konsumen Terbesar Hari Ini: Perangkat <b>{$topConsumer['name']}</b> (" . number_format($topConsumer['energy'], 3) . " kWh)<br>";
        }

        $analysisText .= "<br>📢 <b>4. ANALISIS & REKOMENDASI PINTAR</b><br>";
        if ($avgDailyKwh > 0 && $totalKwhToday > ($avgDailyKwh * 1.2)) {
            $analysisText .= "⚠️ Pemakaian listrik hari ini terdeteksi <b>di atas rata-rata biasanya (naik " . round((($totalKwhToday - $avgDailyKwh) / $avgDailyKwh) * 100) . "%)</b>. Mohon periksa apakah ada perangkat cadangan atau AC yang menyala tidak terpakai.<br>";
        } else {
            $analysisText .= "✅ Pemakaian listrik hari ini relatif stabil dan berada dalam batas wajar rata-rata harian Anda.<br>";
        }

        if ($offlineDevices > 0) {
            $analysisText .= "❌ Terdeteksi ada <b>{$offlineDevices} perangkat mati/offline</b>. Segera lakukan pengecekan pada koneksi daya/Wi-Fi perangkat.<br>";
        } else {
            $analysisText .= "✅ Seluruh perangkat IoT berfungsi dengan baik.<br>";
        }

        $analysisText .= "<br>Semoga laporan ini membantu dalam pengelolaan efisiensi energi kantor. Terima kasih.";

        return response()->json([
            'status' => 'success',
            'analysis' => $analysisText
        ]);
    }

    public function chatbotChat(Request $request)
    {
        $request->validate([
            'message' => 'nullable|string|max:1000',
            'chatInput' => 'nullable|string|max:1000',
        ]);

        $userMessage = $request->input('message') ?? $request->input('chatInput') ?? '';

        $plnTariff = floatval(SystemConfig::where('key', 'pln_tariff')->value('value') ?? 1444.70);
        $devices = Device::where('status', true)->get();
        
        $totalDevices = $devices->count();
        $onlineDevices = 0;
        $offlineDevices = 0;
        $totalKwhToday = 0.0;

        $deviceEnergyList = collect();

        foreach ($devices as $device) {
            $lastSeen = Cache::get("last_seen:{$device->device_id}", 0);
            $isOnline = $lastSeen > 0 && (now()->timestamp - $lastSeen) < 300;
            if ($isOnline) {
                $onlineDevices++;
            } else {
                $offlineDevices++;
            }

            $energy = floatval(Cache::get("daily_energy:{$device->device_id}", 0.0));
            $totalKwhToday += $energy;

            $deviceEnergyList->push([
                'name' => $device->name,
                'energy' => $energy
            ]);
        }

        // Top Consumer today
        $topConsumer = $deviceEnergyList->sortByDesc('energy')->first();

        // Past 7 days logs
        $past7DaysLogs = \Illuminate\Support\Facades\DB::table('daily_energy_logs')
            ->selectRaw('date, SUM(total_kwh_harian) as daily_sum')
            ->where('date', '>=', now()->subDays(7)->toDateString())
            ->groupBy('date')
            ->get();

        $numDays = $past7DaysLogs->count();
        $avgDailyKwh = $numDays > 0 ? $past7DaysLogs->sum('daily_sum') / $numDays : $totalKwhToday;
        $avgDailyKwh = round($avgDailyKwh, 3);

        $currentMonthStart = now()->startOfMonth()->toDateString();
        $currentMonthKwh = \Illuminate\Support\Facades\DB::table('daily_energy_logs')
            ->where('date', '>=', $currentMonthStart)
            ->sum('total_kwh_harian') ?? 0.0;

        $currentMonthCost = $currentMonthKwh * $plnTariff;
        $remainingDays = max(0, now()->daysInMonth - now()->day);
        $projectedKwh = $currentMonthKwh + ($avgDailyKwh * $remainingDays);
        $projectedBilling = $projectedKwh * $plnTariff;

        // Warnings count
        $warningsCount = 0;
        $vMin = floatval(SystemConfig::where('key', 'alert_voltage_min')->value('value') ?? 200.00);
        $vMax = floatval(SystemConfig::where('key', 'alert_voltage_max')->value('value') ?? 240.00);
        $pMax = floatval(SystemConfig::where('key', 'alert_power_max')->value('value') ?? 2200.00);

        foreach ($devices as $device) {
            $lastSeen = Cache::get("last_seen:{$device->device_id}", 0);
            $isOffline = ($lastSeen === 0 || (now()->timestamp - $lastSeen) > 300);
            if ($isOffline) {
                $warningsCount++;
            } else {
                $voltage = floatval(Cache::get("voltage:{$device->device_id}", 0));
                if ($voltage > 0 && ($voltage < $vMin || $voltage > $vMax)) {
                    $warningsCount++;
                }
                $power = floatval(Cache::get("power:{$device->device_id}", 0));
                if ($power > $pMax) {
                    $warningsCount++;
                }
            }
        }

        $prompt = "Kamu adalah YukAnalisaListrikmu, asisten AI profesional untuk sistem IoT pemantauan energi kelistrikan PT Jamkrida Jateng.
Kamu diajak mengobrol oleh pengguna. Jawablah pertanyaannya secara sopan, ramah, dan profesional berdasarkan konteks data listrik di bawah ini jika relevan. Jika pertanyaan tidak relevan dengan listrik atau sistem ini, jawablah secara umum dan sopan tetapi hubungkan kembali ke topik kelistrikan jika memungkinkan.

Konteks Data Listrik Real-Time Saat Ini:
- Tarif PLN: Rp " . number_format($plnTariff, 2, ',', '.') . "/kWh
- Konsumsi listrik hari ini: " . number_format($totalKwhToday, 3) . " kWh
- Estimasi biaya hari ini: Rp " . number_format($totalKwhToday * $plnTariff, 0, ',', '.') . "
- Rata-rata harian (7 hari terakhir): " . number_format($avgDailyKwh, 3) . " kWh/hari
- Proyeksi total konsumsi energi akhir bulan ini: " . number_format($projectedKwh, 2) . " kWh
- Proyeksi tagihan akhir bulan: Rp " . number_format($projectedBilling, 0, ',', '.') . "
- Perangkat: " . $totalDevices . " total (" . $onlineDevices . " Online, " . $offlineDevices . " Offline)
" . ($topConsumer && $topConsumer['energy'] > 0 ? "- Konsumen terbesar hari ini: " . $topConsumer['name'] . " (" . number_format($topConsumer['energy'], 3) . " kWh)" : "") . "

Pertanyaan Pengguna: \"{$userMessage}\"

Jawablah langsung menggunakan format HTML/Blade bersih (tag seperti <b>, <ul>, <li>, 💡, ⚡, ✅) untuk kenyamanan membaca di chat widget. Jawab secara ringkas (maksimal 200 kata) dan bersahabat.";

        $geminiKey = trim(SystemConfig::where('key', 'gemini_api_key')->value('value') ?? '');
        if (empty($geminiKey)) {
            $geminiKey = trim(config('services.gemini.key') ?? '');
        }
        if (!empty($geminiKey)) {
            try {
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'x-goog-api-key' => $geminiKey,
                ])->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent", [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ]
                ]);

                if ($response->successful()) {
                    $result = $response->json();
                    $aiResponse = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;
                    if (!empty($aiResponse)) {
                        $aiResponse = preg_replace('/^```(?:html)?|```$/i', '', trim($aiResponse));
                        return response()->json([
                            'status' => 'success',
                            'reply' => $aiResponse,
                            'output' => $aiResponse
                        ]);
                    }
                }
            } catch (\Exception $e) {
                // Fallback to local responder
            }
        }

        // Return static fallback response if Gemini is not configured/offline
        $staticReply = $this->getStaticBotResponse($userMessage);
        return response()->json([
            'status' => 'success',
            'reply' => $staticReply,
            'output' => $staticReply
        ]);
    }

    private function getStaticBotResponse($input)
    {
        $text = strtolower($input);
        $electricianWhatsapp = SystemConfig::where('key', 'electrician_whatsapp')->value('value') ?? '';

        if (str_contains($text, 'hubungi') || str_contains($text, 'tukang') || str_contains($text, 'teknisi') || str_contains($text, 'listrik')) {
            if ($electricianWhatsapp) {
                return "📞 <b>Hubungi Tukang Listrik:</b><br><br>
                    Terjadi masalah listrik atau alarm menyala? Anda dapat langsung mengirimkan chat WhatsApp ke teknisi listrik resmi:<br><br>
                    👉 <a href=\"https://wa.me/{$electricianWhatsapp}?text=Halo%20Bapak%2FIbu%2C%20kami%20ingin%20melaporkan%20adanya%20masalah%20kelistrikan%20pada%20sistem%20pemantauan%20daya%20IoT%20Jamkrida%20Jateng.%20Mohon%20bantuannya%20untuk%20memeriksa.%20Terima%20kasih.\" target=\"_blank\" class=\"inline-block px-3 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg font-bold text-xs shadow-sm transition-colors\">Hubungi via WhatsApp</a>";
            } else {
                return "📞 Nomor kontak tukang listrik belum dikonfigurasi oleh Administrator di menu Settings.";
            }
        }

        if (str_contains($text, 'tips') || str_contains($text, 'hemat')) {
            return "💡 <b>Berikut Tips Praktis Menghemat Listrik Anda:</b><br><br>
                1. <b>Matikan Beban Standby</b>: Cabut colokan TV, komputer, atau charger HP yang tidak dipakai. Beban standby berkontribusi hingga 10% tagihan bulanan.<br>
                2. <b>Gunakan LED Berkualitas</b>: Ganti lampu pijar Anda dengan LED. Lampu LED menggunakan daya 80% lebih sedikit untuk tingkat kecerahan yang sama.<br>
                3. <b>Atur Limit Alarm Anggaran</b>: Gunakan fitur <b>Monthly Cost/Kwh Budget</b> di menu Settings pada dashboard ini untuk memantau konsumsi agar tidak melebihi anggaran bulanan Anda.";
        }

        if (str_contains($text, 'grafik') || str_contains($text, 'baca')) {
            return "📊 <b>Cara Membaca Grafik Sensor Dashboard:</b><br><br>
                * <b>Grafik Voltase (V)</b>: Memantau kestabilan tegangan listrik. Normalnya berkisar di <b>220V</b>. Jika turun di bawah 200V atau di atas 240V, instalasi Anda berisiko merusak peralatan elektronik.<br>
                * <b>Grafik Arus (A)</b>: Menampilkan besarnya arus listrik yang mengalir ke beban Anda.<br>
                * <b>Grafik Daya (W)</b>: Menunjukkan daya aktif nyata yang sedang disedot alat listrik Anda saat ini (V x A).<br>
                * <b>Grafik Energi (kWh)</b>: Menampilkan akumulasi total pemakaian listrik harian Anda.";
        }

        if (str_contains($text, 'tarif') || str_contains($text, 'pln') || str_contains($text, 'biaya') || str_contains($text, 'wbp')) {
            return "🔋 <b>Estimasi Tarif PLN (Time of Use - ToU):</b><br><br>
                Sistem di dashboard ini menghitung estimasi biaya harian Anda berdasarkan dua tarif:<br>
                * <b>WBP (Waktu Beban Puncak)</b>: Berlaku pukul <b>17:00 - 22:00</b> dengan tarif lebih tinggi (misal Rp2.000/kWh) karena beban puncak jaringan listrik.<br>
                * <b>LWBP (Luar Waktu Beban Puncak)</b>: Berlaku pukul <b>22:00 - 17:00</b> dengan tarif standar (misal Rp1.444,70/kWh).<br><br>
                Anda bisa mengubah nilai tarif ini kapan saja di menu <b>Settings</b>.";
        }

        if (str_contains($text, 'telegram') || str_contains($text, 'notif') || str_contains($text, 'mati') || str_contains($text, 'offline')) {
            return "⚠️ <b>Fitur Notifikasi Telegram Alert:</b><br><br>
                * Bot akan otomatis mengirimkan chat ke Telegram Anda jika voltase berada di luar batas aman (di bawah 200V / di atas 240V).<br>
                * Jika alat sensor ESP32 terputus (mati listrik atau Wi-Fi mati) selama <b>5 menit</b>, Anda akan mendapat chat peringatan <b>DEVICE OFFLINE</b>.<br>
                * Ketika alat menyala lagi, bot mengirimkan chat pemulihan <b>DEVICE ONLINE RECOVERY</b>.";
        }

        return "🤖 <b>Halo! Saya YukAnalisaListrikmu.</b><br><br>
            Ada yang bisa saya bantu tentang pemantauan energi Anda?<br>
            Silakan tanyakan hal berikut:<br>
            * 💡 <i>\"Tips hemat listrik\"</i><br>
            * 📊 <i>\"Cara membaca grafik\"</i><br>
            * 🔋 <i>\"Bagaimana tarif PLN WBP/LWBP dihitung?\"</i><br>
            * ⚠️ <i>\"Bagaimana cara kerja notifikasi Telegram?\"</i>";
    }

    public function tvMode()
    {
        $devices = \App\Models\Device::where('status', true)->get();
        $plnTariff = SystemConfig::where('key', 'pln_tariff')->value('value') ?? 1444.70;
        
        $totalPower = 0.0;
        $totalTodayCost = 0.0;
        $totalActiveCount = 0;
        $now = now()->timestamp;

        foreach ($devices as $device) {
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

            $device->voltage = floatval($voltage ?? 0.0);
            $device->current = floatval($current ?? 0.0);
            $device->power = floatval($power ?? 0.0);
            $device->energy = floatval($energy ?? 0.0);

            $lastSeen = Cache::get("last_seen:{$device->device_id}", 0);
            $device->last_seen = $lastSeen;
            $diff = $now - $lastSeen;
            
            if ($lastSeen > 0 && $diff < 15) {
                $device->is_online = true;
                $totalActiveCount++;
                $totalPower += $device->power;
            } else {
                $device->is_online = false;
            }

            $deviceCost = Cache::get("daily_cost:{$device->device_id}");
            if ($deviceCost === null) {
                $deviceCost = $device->energy * $plnTariff;
            }
            $totalTodayCost += $deviceCost;
        }

        $alertConfig = [
            'voltage_min' => SystemConfig::where('key', 'alert_voltage_min')->value('value') ?? 200,
            'voltage_max' => SystemConfig::where('key', 'alert_voltage_max')->value('value') ?? 240,
            'power_max' => SystemConfig::where('key', 'alert_power_max')->value('value') ?? 5000,
        ];

        return view('devices.tv_mode', compact('devices', 'totalPower', 'totalTodayCost', 'totalActiveCount', 'alertConfig', 'plnTariff'));
    }

    public function buildingMap()
    {
        $groups = Group::with(['devices' => function($q) {
            $q->where('status', true);
        }])->orderBy('floor', 'desc')->get();

        $plnTariff = SystemConfig::where('key', 'pln_tariff')->value('value') ?? 1444.70;

        foreach ($groups as $group) {
            foreach ($group->devices as $device) {
                $energy = Cache::get("daily_energy:{$device->device_id}");
                $voltage = Cache::get("voltage:{$device->device_id}");
                $current = Cache::get("current:{$device->device_id}");
                $power = Cache::get("power:{$device->device_id}");
                $lastSeen = Cache::get("last_seen:{$device->device_id}");

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

                $device->voltage = floatval($voltage ?? 0.0);
                $device->current = floatval($current ?? 0.0);
                $device->power = floatval($power ?? 0.0);
                $device->energy = floatval($energy ?? 0.0);
                $device->last_seen = $lastSeen ? intval($lastSeen) : null;
                $device->is_online = $lastSeen ? (time() - intval($lastSeen) < 15) : false;
            }
        }

        $floors = $groups->groupBy('floor');

        $alertConfig = [
            'voltage_min' => SystemConfig::where('key', 'alert_voltage_min')->value('value') ?? 200,
            'voltage_max' => SystemConfig::where('key', 'alert_voltage_max')->value('value') ?? 240,
            'power_max' => SystemConfig::where('key', 'alert_power_max')->value('value') ?? 5000,
        ];

        return view('devices.building_map', compact('floors', 'plnTariff', 'alertConfig'));
    }

    public function officeControl()
    {
        // Default mock rooms data which can be updated dynamically via MQTT
        $rooms = [
            'server_room' => [
                'id' => 'server_room',
                'name' => 'Server Room',
                'temp' => floatval(Cache::get('office_temp:server_room', 19.8)),
                'humi' => floatval(Cache::get('office_humi:server_room', 42.5)),
                'comfort' => 'Cool',
                'status_color' => 'text-blue-600 bg-blue-50 border-blue-100',
            ],
            'main_workspace' => [
                'id' => 'main_workspace',
                'name' => 'Main Workspace',
                'temp' => floatval(Cache::get('office_temp:main_workspace', 24.2)),
                'humi' => floatval(Cache::get('office_humi:main_workspace', 53.0)),
                'comfort' => 'Comfortable',
                'status_color' => 'text-emerald-600 bg-emerald-50 border-emerald-100',
            ],
            'meeting_room_a' => [
                'id' => 'meeting_room_a',
                'name' => 'Meeting Room A',
                'temp' => floatval(Cache::get('office_temp:meeting_room_a', 22.5)),
                'humi' => floatval(Cache::get('office_humi:meeting_room_a', 48.0)),
                'comfort' => 'Cool',
                'status_color' => 'text-blue-600 bg-blue-50 border-blue-100',
            ],
            'lobby_reception' => [
                'id' => 'lobby_reception',
                'name' => 'Lobby Reception',
                'temp' => floatval(Cache::get('office_temp:lobby_reception', 25.1)),
                'humi' => floatval(Cache::get('office_humi:lobby_reception', 58.5)),
                'comfort' => 'Comfortable',
                'status_color' => 'text-emerald-600 bg-emerald-50 border-emerald-100',
            ],
        ];

        // Retrieve actual state of appliances from Cache (0 = Off, 1 = On)
        $appliances = [
            [
                'id' => 'ac_server_1',
                'name' => 'AC Server Room 1',
                'category' => 'Air Conditioning',
                'state' => (int)Cache::get('office_switch:ac_server_1', 1),
                'icon' => '❄️',
            ],
            [
                'id' => 'ac_server_2',
                'name' => 'AC Server Room 2 (Backup)',
                'category' => 'Air Conditioning',
                'state' => (int)Cache::get('office_switch:ac_server_2', 0),
                'icon' => '❄️',
            ],
            [
                'id' => 'ac_workspace_1',
                'name' => 'AC Workspace Left',
                'category' => 'Air Conditioning',
                'state' => (int)Cache::get('office_switch:ac_workspace_1', 1),
                'icon' => '❄️',
            ],
            [
                'id' => 'ac_workspace_2',
                'name' => 'AC Workspace Right',
                'category' => 'Air Conditioning',
                'state' => (int)Cache::get('office_switch:ac_workspace_2', 1),
                'icon' => '❄️',
            ],
            [
                'id' => 'vent_fan',
                'name' => 'Office Ventilation Fan',
                'category' => 'Ventilation',
                'state' => (int)Cache::get('office_switch:vent_fan', 1),
                'icon' => '💨',
            ],
            [
                'id' => 'lights_workspace',
                'name' => 'Main Workspace Lighting',
                'category' => 'Lighting',
                'state' => (int)Cache::get('office_switch:lights_workspace', 1),
                'icon' => '💡',
            ],
            [
                'id' => 'lights_lobby',
                'name' => 'Lobby & Reception Lights',
                'category' => 'Lighting',
                'state' => (int)Cache::get('office_switch:lights_lobby', 0),
                'icon' => '💡',
            ],
            [
                'id' => 'coffee_maker',
                'name' => 'Pantry Coffee Machine',
                'category' => 'Smart Appliances',
                'state' => (int)Cache::get('office_switch:coffee_maker', 0),
                'icon' => '☕',
            ],
        ];

        return view('devices.office_control', compact('rooms', 'appliances'));
    }

    public function toggleOfficeAppliance(Request $request)
    {
        $request->validate([
            'appliance_id' => 'required|string',
            'state' => 'required|integer|in:0,1',
        ]);

        $applianceId = $request->appliance_id;
        $state = (int)$request->state;

        // Store target state in Cache
        Cache::put("office_switch:{$applianceId}", $state, now()->addDays(30));

        // Create log record to audit the action
        \Illuminate\Support\Facades\Log::info("Pengguna " . auth()->user()->name . " mengubah status peralatan '" . $applianceId . "' menjadi " . ($state ? 'ON' : 'OFF') . ".", [
            'appliance_id' => $applianceId,
            'state' => $state,
            'ip' => $request->ip()
        ]);

        // Dispatch MQTT payload to command topic to tell actual relays to toggle
        $server   = SystemConfig::where('key', 'mqtt_host')->value('value') ?? config('mqtt.host', 'broker.emqx.io');
        $port     = SystemConfig::where('key', 'mqtt_port')->value('value') ?? config('mqtt.port', 1883);
        $username = SystemConfig::where('key', 'mqtt_user')->value('value') ?? config('mqtt.username');
        $password = SystemConfig::where('key', 'mqtt_password')->value('value') ?? config('mqtt.password');
        
        try {
            $mqtt = new \PhpMqtt\Client\MqttClient($server, (int)$port, 'laravel_control_' . rand(1000, 9999));
            $connectionSettings = (new \PhpMqtt\Client\ConnectionSettings)
                ->setKeepAliveInterval(10)
                ->setUseTls(false);
            if (!empty($username)) {
                $connectionSettings = $connectionSettings->setUsername($username);
            }
            if (!empty($password)) {
                $connectionSettings = $connectionSettings->setPassword($password);
            }
            $mqtt->connect($connectionSettings, true);
            $mqtt->publish("cmd/office-control", json_encode([
                'appliance' => $applianceId,
                'state' => $state
            ]), 0);
            $mqtt->disconnect();
        } catch (\Exception $e) {
            \Log::warning("Failed to publish MQTT control message: " . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'appliance_id' => $applianceId,
            'state' => $state
        ]);
    }
}

