<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\SystemConfig;
use App\Models\Device;

class TelegramBotPoll extends Command
{
    protected $signature = 'telegram:poll';
    protected $description = 'Poll Telegram API for incoming commands and reply with device statuses';

    public function handle()
    {
        $this->info("Starting Telegram Bot Polling listener...");

        while (true) {
            try {
                $botToken = SystemConfig::where('key', 'telegram_bot_token')->value('value');
                if (empty($botToken)) {
                    $this->error("Telegram Bot Token is not configured. Retrying in 10 seconds...");
                    sleep(10);
                    continue;
                }

                $offset = Cache::get('telegram_poll_offset', 0);

                // Call Telegram getUpdates API with timeout to block long polling
                $url = "https://api.telegram.org/bot{$botToken}/getUpdates";
                $response = Http::timeout(35)->post($url, [
                    'offset' => $offset,
                    'timeout' => 30,
                ]);

                if ($response->successful()) {
                    $result = $response->json('result') ?? [];
                    foreach ($result as $update) {
                        $updateId = $update['update_id'];
                        $message = $update['message'] ?? null;
                        
                        if ($message) {
                            $chatId = $message['chat']['id'] ?? null;
                            $text = trim($message['text'] ?? '');

                            if ($chatId && !empty($text)) {
                                $this->processMessage($botToken, $chatId, $text);
                            }
                        }

                        $offset = $updateId + 1;
                        Cache::put('telegram_poll_offset', $offset);
                    }
                } else {
                    $this->error("Failed to fetch updates from Telegram: " . $response->body());
                    sleep(5);
                }
            } catch (\Exception $e) {
                $this->error("Error in Telegram Polling loop: " . $e->getMessage());
                sleep(5);
            }
            
            // Sleep briefly to avoid absolute CPU throttling when no timeout is triggered
            sleep(1);
        }
    }

    protected function processMessage($botToken, $chatId, $text)
    {
        $this->info("Processing message from Chat ID {$chatId}: '{$text}'");

        // Match commands (case-insensitive)
        $textLower = strtolower($text);
        
        if (str_starts_with($textLower, '/start')) {
            $this->sendHelpMessage($botToken, $chatId);
            return;
        }

        if (str_starts_with($textLower, '/help')) {
            $this->sendHelpMessage($botToken, $chatId);
            return;
        }

        if (str_starts_with($textLower, '/check') || str_starts_with($textLower, '/status') || str_starts_with($textLower, '/devices')) {
            $this->sendDeviceStatusMessage($botToken, $chatId);
            return;
        }
    }

    protected function sendHelpMessage($botToken, $chatId)
    {
        $messageText = "🔌 <b>Jamkrida IoT Monitor Bot</b>\n\n" .
                      "Halo! Bot ini digunakan untuk memantau status perangkat IoT Jamkrida secara real-time.\n\n" .
                      "<b>Daftar Perintah:</b>\n" .
                      "👉 `/check` - Memeriksa status & data semua perangkat aktif\n" .
                      "👉 `/status` - Sama dengan /check\n" .
                      "👉 `/devices` - Sama dengan /check\n" .
                      "👉 `/help` - Menampilkan bantuan ini";

        $this->sendReply($botToken, $chatId, $messageText);
    }

    protected function sendDeviceStatusMessage($botToken, $chatId)
    {
        $devices = Device::with('group')->get();

        if ($devices->isEmpty()) {
            $this->sendReply($botToken, $chatId, "📭 <b>Belum ada perangkat yang terdaftar di sistem.</b>");
            return;
        }

        $messageText = "📊 <b>Status Perangkat Jamkrida IoT</b>\n";
        $messageText .= "Waktu Cek: " . now()->format('d M Y H:i:s') . "\n\n";

        foreach ($devices as $device) {
            $deviceId = $device->device_id;
            
            // Check if online (last seen within 35 seconds)
            $lastSeen = Cache::get("last_seen:{$deviceId}");
            $isOnline = false;
            if ($lastSeen && (now()->timestamp - intval($lastSeen)) < 35) {
                $isOnline = true;
            }

            $statusIndicator = $isOnline ? "🟢 <b>ONLINE</b>" : "🔴 <b>OFFLINE</b>";
            
            $messageText .= "🔹 <b>{$device->name}</b>\n";
            $messageText .= "ID: <code>{$deviceId}</code>\n";
            $messageText .= "Area: " . ($device->group->name ?? 'Tidak ada') . "\n";
            $messageText .= "Status: {$statusIndicator}\n";

            if ($isOnline) {
                // Fetch metrics from Cache
                $voltage = Cache::get("voltage:{$deviceId}", 0.00);
                $current = Cache::get("current:{$deviceId}", 0.0000);
                $power = Cache::get("power:{$deviceId}", 0.00);
                $energy = Cache::get("energy:{$deviceId}", 0.0000);

                $messageText .= "⚡ Voltase: <code>{$voltage} V</code>\n";
                $messageText .= "🔌 Arus: <code>{$current} A</code>\n";
                $messageText .= "🔥 Daya: <code>{$power} W</code>\n";
                $messageText .= "🔋 Energi: <code>{$energy} kWh</code>\n";
            } else {
                if ($lastSeen) {
                    $diff = now()->timestamp - intval($lastSeen);
                    if ($diff < 60) {
                        $lastSeenText = "{$diff} detik lalu";
                    } elseif ($diff < 3600) {
                        $lastSeenText = floor($diff / 60) . " menit lalu";
                    } else {
                        $lastSeenText = date('d M H:i', $lastSeen);
                    }
                    $messageText .= "⏳ Terakhir aktif: {$lastSeenText}\n";
                } else {
                    $messageText .= "⏳ Terakhir aktif: Belum pernah aktif\n";
                }
            }
            $messageText .= "\n";
        }

        $this->sendReply($botToken, $chatId, $messageText);
    }

    protected function sendReply($botToken, $chatId, $messageText)
    {
        try {
            $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
            Http::post($url, [
                'chat_id' => $chatId,
                'text' => $messageText,
                'parse_mode' => 'HTML'
            ]);
        } catch (\Exception $e) {
            $this->error("Failed to send reply to chat {$chatId}: " . $e->getMessage());
        }
    }
}
