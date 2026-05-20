<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DailyEnergyLog;

class LogController extends Controller
{
    public function index()
    {
        $logs = DailyEnergyLog::with(['device.group'])
            ->orderBy('date', 'desc')
            ->paginate(15);
            
        return view('logs.index', compact('logs'));
    }
}
