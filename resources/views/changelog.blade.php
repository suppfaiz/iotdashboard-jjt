@extends('layouts.app')

@section('content')
<style>
    .changelog-card {
        background: rgba(255, 255, 255, 0.45);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(0, 0, 0, 0.06);
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .changelog-card:hover {
        background: rgba(255, 255, 255, 0.75);
        border-color: rgba(59, 130, 246, 0.2);
        box-shadow: 0 10px 30px rgba(59, 130, 246, 0.05);
        transform: translateY(-2px);
    }
    .timeline-dot {
        position: relative;
    }
    .timeline-dot::after {
        content: '';
        position: absolute;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #3b82f6;
        border: 3px solid #ffffff;
        left: -6px;
        top: 24px;
        box-shadow: 0 0 10px rgba(59, 130, 246, 0.5);
        z-index: 10;
    }
    .timeline-line {
        position: relative;
    }
    .timeline-line::before {
        content: '';
        position: absolute;
        width: 2px;
        background: rgba(203, 213, 225, 0.6);
        top: 32px;
        bottom: -20px;
        left: -1px;
    }
    .timeline-item:last-child .timeline-line::before {
        display: none;
    }
</style>

<div class="max-w-4xl mx-auto mb-10">
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('dashboard') }}" class="p-2 bg-white/80 border border-slate-200 hover:border-slate-300 text-slate-650 rounded-2xl shadow-sm transition-all duration-300">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h1 class="text-3xl font-extrabold text-slate-900 tracking-tight mb-2">Changelog & Updates</h1>
            <p class="text-slate-500 font-medium">Historical timeline of development updates and secure features deployed to the Jamkrida IoT Dashboard.</p>
        </div>
    </div>

    <!-- Timeline Container -->
    <div class="relative pl-8 md:pl-10 space-y-12">
        
        <!-- Version 2.0.0 (Latest) -->
        <div class="timeline-item timeline-dot timeline-line">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 mb-4">
                <div class="flex items-center gap-3">
                    <span class="px-3.5 py-1 bg-blue-600 text-white text-xs font-bold rounded-full shadow-sm">v2.0.0</span>
                    <h2 class="text-xl font-bold text-slate-950">Secure Advanced IoT Extensions</h2>
                </div>
                <span class="text-xs font-bold text-slate-400 font-mono">June 24, 2026</span>
            </div>
            
            <div class="changelog-card rounded-3xl p-6 shadow-sm">
                <p class="text-sm font-semibold text-blue-600 mb-4 uppercase tracking-widest">Major Updates & Security Hardening</p>
                <div class="space-y-4 text-sm text-slate-600">
                    <div class="flex items-start gap-3">
                        <span class="w-2 h-2 rounded-full bg-blue-500 mt-2 flex-shrink-0"></span>
                        <div>
                            <span class="font-bold text-slate-800">Device Calibration Multipliers:</span>
                            Menerapkan multiplier kalibrasi voltase dan arus secara dinamis untuk menyamakan pembacaan PZEM dengan alat ukur fisik PLN.
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="w-2 h-2 rounded-full bg-blue-500 mt-2 flex-shrink-0"></span>
                        <div>
                            <span class="font-bold text-slate-800">Telegram Bot Threshold Alarms:</span>
                            Alert real-time otomatis untuk mendeteksi perangkat offline (>5 menit), pemulihan online, dan konsumsi daya melebihi batas batas aman.
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="w-2 h-2 rounded-full bg-blue-500 mt-2 flex-shrink-0"></span>
                        <div>
                            <span class="font-bold text-slate-800">Monthly Cost & Consumption Budgets:</span>
                            Manajemen target anggaran biaya bulanan (Rp) dan kWh dengan alarm notifikasi bertahap (di batas 80% dan 100% dari target).
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="w-2 h-2 rounded-full bg-blue-500 mt-2 flex-shrink-0"></span>
                        <div>
                            <span class="font-bold text-slate-800">Interactive Remote Console Terminal:</span>
                            Terminal emulator di halaman perangkat (khusus Admin) untuk mengirimkan custom perintah dalam format JSON ke mikrokontroler.
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="w-2 h-2 rounded-full bg-blue-500 mt-2 flex-shrink-0"></span>
                        <div>
                            <span class="font-bold text-slate-800">Weekly Energy Comparison Analytics:</span>
                            Mode grafik analisis mingguan yang membandingkan performa pemakaian energi This Week vs. Last Week berdampingan.
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="w-2 h-2 rounded-full bg-blue-500 mt-2 flex-shrink-0"></span>
                        <div>
                            <span class="font-bold text-slate-800">Secure CSV Report Streaming:</span>
                            Download laporan log harian yang dilindungi oleh autentikasi penuh (`auth` middleware) untuk keamanan data operasional.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Version 1.2.0 -->
        <div class="timeline-item timeline-dot timeline-line">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 mb-4">
                <div class="flex items-center gap-3">
                    <span class="px-3.5 py-1 bg-slate-200 text-slate-700 text-xs font-bold rounded-full border border-slate-300">v1.2.0</span>
                    <h2 class="text-xl font-bold text-slate-950">Reboot Resilience & Buffering</h2>
                </div>
                <span class="text-xs font-bold text-slate-400 font-mono">June 24, 2026</span>
            </div>
            
            <div class="changelog-card rounded-3xl p-6 shadow-sm">
                <p class="text-sm font-semibold text-slate-600 mb-4 uppercase tracking-widest">Reliability Improvements</p>
                <div class="space-y-4 text-sm text-slate-600">
                    <div class="flex items-start gap-3">
                        <span class="w-2 h-2 rounded-full bg-slate-400 mt-2 flex-shrink-0"></span>
                        <div>
                            <span class="font-bold text-slate-800">Reboot-Resilient standard Telemetry Accumulation:</span>
                            Menghitung delta energy secara dinamis berdasarkan cache telemetry terakhir. Mencegah nilai log hari itu me-reset kembali ke 0.0 jika ESP32 mendadak reboot.
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="w-2 h-2 rounded-full bg-slate-400 mt-2 flex-shrink-0"></span>
                        <div>
                            <span class="font-bold text-slate-800">Chronological Offline Historical Reconstruction:</span>
                            Mendukung pemrosesan data offline (LitteFS buffer dari ESP32) secara berurutan dan mengintegrasikannya dengan tanggal log yang sesuai.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Version 1.1.0 -->
        <div class="timeline-item timeline-dot timeline-line">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 mb-4">
                <div class="flex items-center gap-3">
                    <span class="px-3.5 py-1 bg-slate-200 text-slate-700 text-xs font-bold rounded-full border border-slate-300">v1.1.0</span>
                    <h2 class="text-xl font-bold text-slate-950">Containerization & Dev Tools</h2>
                </div>
                <span class="text-xs font-bold text-slate-400 font-mono">June 23, 2026</span>
            </div>
            
            <div class="changelog-card rounded-3xl p-6 shadow-sm">
                <p class="text-sm font-semibold text-slate-600 mb-4 uppercase tracking-widest">Deployment & Configuration</p>
                <div class="space-y-4 text-sm text-slate-600">
                    <div class="flex items-start gap-3">
                        <span class="w-2 h-2 rounded-full bg-slate-400 mt-2 flex-shrink-0"></span>
                        <div>
                            <span class="font-bold text-slate-800">Secure Mosquitto Authentication:</span>
                            Menghasilkan file password terenkripsi mosquitto menggunakan generator otomatis Docker.
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="w-2 h-2 rounded-full bg-slate-400 mt-2 flex-shrink-0"></span>
                        <div>
                            <span class="font-bold text-slate-800">VPS Quick Deployer Script:</span>
                            Membuat script `install.sh` untuk otomasi instalasi Docker, SSL, Reverb, MQTT, kompilasi aset NPM, dan inisialisasi SQLite secara sekali jalan.
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="w-2 h-2 rounded-full bg-slate-400 mt-2 flex-shrink-0"></span>
                        <div>
                            <span class="font-bold text-slate-800">Developer Documentation Page:</span>
                            Membuat modul panduan firmware C++ mikrokontroler dan dokumentasi infrastruktur sistem (`/docs`).
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Version 1.0.0 -->
        <div class="timeline-item timeline-dot timeline-line">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 mb-4">
                <div class="flex items-center gap-3">
                    <span class="px-3.5 py-1 bg-slate-200 text-slate-700 text-xs font-bold rounded-full border border-slate-300">v1.0.0</span>
                    <h2 class="text-xl font-bold text-slate-950">Initial Product Release</h2>
                </div>
                <span class="text-xs font-bold text-slate-400 font-mono">June 22, 2026</span>
            </div>
            
            <div class="changelog-card rounded-3xl p-6 shadow-sm">
                <p class="text-sm font-semibold text-slate-600 mb-4 uppercase tracking-widest">Base Infrastructure</p>
                <div class="space-y-4 text-sm text-slate-600">
                    <div class="flex items-start gap-3">
                        <span class="w-2 h-2 rounded-full bg-slate-400 mt-2 flex-shrink-0"></span>
                        <div>
                            <span class="font-bold text-slate-800">PZEM Real-time Monitoring:</span>
                            Dasbor pemantauan konsumsi daya (V, A, W, kWh) yang dikelompokkan berdasarkan area operasional secara real-time.
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="w-2 h-2 rounded-full bg-slate-400 mt-2 flex-shrink-0"></span>
                        <div>
                            <span class="font-bold text-slate-800">WebSocket Integration:</span>
                            Implementasi Laravel Echo & Laravel Reverb untuk transmisi paket data tanpa reload halaman.
                        </div>
                    </div>
                    <div class="flex items-start gap-3">
                        <span class="w-2 h-2 rounded-full bg-slate-400 mt-2 flex-shrink-0"></span>
                        <div>
                            <span class="font-bold text-slate-800">Role-Based Access Control (RBAC):</span>
                            Pemisahan peran Admin (akses edit, delete, setup parameter tarif, tambah perangkat) dan User biasa (akses baca/read-only).
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
