<?php
// Set default timezone to Asia/Jakarta (WIB)
date_default_timezone_set('Asia/Jakarta');

$stdin = fopen('php://stdin', 'r');
$activeDevices = [];
$outageStart = null;
$firstConnectSeen = false;

echo "========================================================\n";
echo "📊 ANALISIS MATI LAMPU TOTAL (SEMUA DEVICE OFFLINE)\n";
echo "========================================================\n";

while ($line = fgets($stdin)) {
    // Match 10-digit timestamp in the log line
    if (!preg_match('/(\d{10})/', $line, $matches)) {
        continue;
    }
    $timestamp = intval($matches[1]);
    
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
                $durationStr = "";
                if ($duration >= 3600) {
                    $durationStr .= floor($duration / 3600) . " jam ";
                    $duration = $duration % 3600;
                }
                if ($duration >= 60) {
                    $durationStr .= floor($duration / 60) . " menit ";
                    $duration = $duration % 60;
                }
                if ($duration > 0 || empty($durationStr)) {
                    $durationStr .= $duration . " detik";
                }
                echo "⚡️ KONEKSI PULIH (Listrik Menyala): " . date('d M Y - H:i:s', $timestamp) . " WIB (Padam selama: " . trim($durationStr) . ")\n";
                $outageStart = null;
            }
        } elseif ($isDisconnect) {
            unset($activeDevices[$deviceId]);
            if (empty($activeDevices) && $firstConnectSeen && $outageStart === null) {
                $outageStart = $timestamp;
                echo "🚨 MATI LAMPU TOTAL (Semua Device Offline): " . date('d M Y - H:i:s', $timestamp) . " WIB\n";
            }
        }
    }
}
fclose($stdin);
echo "========================================================\n";
