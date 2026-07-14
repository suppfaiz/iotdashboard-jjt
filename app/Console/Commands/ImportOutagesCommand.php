<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\OutageLog;
use Carbon\Carbon;

class ImportOutagesCommand extends Command
{
    protected $signature = 'outages:import';
    protected $description = 'Import historical outages from Mosquitto log via STDIN';

    public function handle()
    {
        $this->info("Parsing logs from STDIN. Please wait...");

        $stdin = fopen('php://stdin', 'r');
        $activeDevices = [];
        $outageStart = null;
        $firstConnectSeen = false;
        $count = 0;

        // Truncate existing outage logs to do a fresh seed/import
        OutageLog::truncate();

        while ($line = fgets($stdin)) {
            // Match 10-digit timestamp in the log line
            if (!preg_match('/(\d{10})/', $line, $matches)) {
                continue;
            }
            $timestamp = intval($matches[1]);
            $time = Carbon::createFromTimestamp($timestamp);
            
            $isConnect = false;
            $isDisconnect = false;
            $deviceId = null;

            if (preg_match('/New client connected.*as (dev_\w+)/', $line, $devMatches)) {
                $isConnect = true;
                $deviceId = $devMatches[1];
            } elseif (preg_match('/Client (dev_\w+).*disconnected/', $line, $devMatches)) {
                $isDisconnect = true;
                $deviceId = $devMatches[1];
            }

            if ($deviceId) {
                if ($isConnect) {
                    $firstConnectSeen = true;
                    $activeDevices[$deviceId] = true;
                    if ($outageStart !== null) {
                        $duration = $timestamp - $outageStart;
                        
                        OutageLog::create([
                            'outage_start' => Carbon::createFromTimestamp($outageStart),
                            'outage_end' => $time,
                            'duration_seconds' => $duration
                        ]);
                        $count++;
                        $outageStart = null;
                    }
                } elseif ($isDisconnect) {
                    unset($activeDevices[$deviceId]);
                    if (empty($activeDevices) && $firstConnectSeen && $outageStart === null) {
                        $outageStart = $timestamp;
                    }
                }
            }
        }
        
        fclose($stdin);

        // If there is an ongoing outage at the end of the log
        if ($outageStart !== null) {
            OutageLog::create([
                'outage_start' => Carbon::createFromTimestamp($outageStart),
                'outage_end' => null,
                'duration_seconds' => null
            ]);
            $count++;
        }

        $this->info("Successfully imported {$count} outage events into the database!");
    }
}
