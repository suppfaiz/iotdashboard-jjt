<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DailyEnergyLog;
use App\Models\HourlyEnergyLog;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->query('type', 'daily');
        
        if ($type === 'hourly') {
            $logs = HourlyEnergyLog::with(['device.group'])
                ->orderBy('logged_at', 'desc')
                ->paginate(20);
        } else {
            $logs = DailyEnergyLog::with(['device.group'])
                ->orderBy('date', 'desc')
                ->paginate(20);
        }
            
        return view('logs.index', compact('logs', 'type'));
    }
}
