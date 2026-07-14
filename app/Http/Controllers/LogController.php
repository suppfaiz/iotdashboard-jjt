<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DailyEnergyLog;
use App\Models\HourlyEnergyLog;
use App\Models\OutageLog;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('type', 'daily');
        
        if ($type === 'hourly') {
            $logs = HourlyEnergyLog::with(['device.group'])
                ->orderBy('logged_at', 'desc')
                ->paginate(20);
            return view('logs.index', compact('logs', 'type'));
        } elseif ($type === 'outages') {
            $logs = OutageLog::orderBy('outage_start', 'desc')->paginate(15);

            // Compute statistics
            $totalOutagesThisMonth = OutageLog::where('outage_start', '>=', now()->startOfMonth())->count();
            
            $avgDurationSeconds = OutageLog::whereNotNull('duration_seconds')->avg('duration_seconds') ?? 0;
            $avgDurationStr = $this->formatDuration(intval($avgDurationSeconds));

            $maxDurationSeconds = OutageLog::whereNotNull('duration_seconds')->max('duration_seconds') ?? 0;
            $maxDurationStr = $this->formatDuration(intval($maxDurationSeconds));

            // Read Server Uptime
            $serverUptime = $this->getServerUptime();

            return view('logs.index', compact(
                'logs',
                'type',
                'totalOutagesThisMonth',
                'avgDurationStr',
                'maxDurationStr',
                'serverUptime'
            ));
        } else {
            $logs = DailyEnergyLog::with(['device.group'])
                ->orderBy('date', 'desc')
                ->paginate(20);
            return view('logs.index', compact('logs', 'type'));
        }
    }

    private function formatDuration($seconds)
    {
        if ($seconds <= 0) return '0 detik';
        
        $parts = [];
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $remainingSeconds = $seconds % 60;

        if ($hours > 0) {
            $parts[] = "{$hours} jam";
        }
        if ($minutes > 0) {
            $parts[] = "{$minutes} menit";
        }
        if ($remainingSeconds > 0 || empty($parts)) {
            $parts[] = "{$remainingSeconds} detik";
        }

        return implode(' ', $parts);
    }

    private function getServerUptime()
    {
        if (is_readable('/proc/uptime')) {
            $uptimeData = explode(' ', file_get_contents('/proc/uptime'));
            $uptimeSeconds = intval($uptimeData[0]);
            
            $days = floor($uptimeSeconds / 86400);
            $hours = floor(($uptimeSeconds % 86400) / 3600);
            $minutes = floor(($uptimeSeconds % 3600) / 60);
            
            $parts = [];
            if ($days > 0) $parts[] = "{$days} hari";
            if ($hours > 0) $parts[] = "{$hours} jam";
            if ($minutes > 0 || empty($parts)) $parts[] = "{$minutes} menit";
            
            return implode(', ', $parts);
        }

        try {
            $output = @shell_exec('uptime -p 2>/dev/null');
            if ($output) {
                return trim(str_replace('up ', '', $output));
            }
        } catch (\Exception $e) {}

        return 'Tidak tersedia';
    }
}
