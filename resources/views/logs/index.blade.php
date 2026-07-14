@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Historical Logs</h1>
            <p class="text-gray-500 font-medium">View logged device metrics and power outage timeline.</p>
        </div>
        
        <!-- Log Type Selector Toggle -->
        <div class="flex bg-gray-150 p-1.5 rounded-xl border border-gray-200 shadow-sm bg-gray-100">
            <a href="{{ route('logs.index', ['type' => 'daily']) }}" 
                class="px-4 py-2 text-xs font-bold rounded-lg transition-all duration-300 {{ $type === 'daily' ? 'bg-white text-blue-600 border border-gray-200 shadow-sm font-extrabold' : 'text-gray-500 hover:text-gray-800' }}">
                Daily Energy
            </a>
            <a href="{{ route('logs.index', ['type' => 'hourly']) }}" 
                class="px-4 py-2 text-xs font-bold rounded-lg transition-all duration-300 {{ $type === 'hourly' ? 'bg-white text-blue-600 border border-gray-200 shadow-sm font-extrabold' : 'text-gray-500 hover:text-gray-800' }}">
                Hourly Telemetry
            </a>
            <a href="{{ route('logs.index', ['type' => 'outages']) }}" 
                class="px-4 py-2 text-xs font-bold rounded-lg transition-all duration-300 {{ $type === 'outages' ? 'bg-white text-blue-600 border border-gray-200 shadow-sm font-extrabold' : 'text-gray-500 hover:text-gray-800' }}">
                ⚠️ Outage Logs (Mati Lampu)
            </a>
        </div>
    </div>

    @if($type === 'outages')
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Card 1: Server Uptime -->
        <div class="bg-gradient-to-br from-indigo-500/10 to-blue-600/5 backdrop-blur-xl border border-indigo-100 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-indigo-600">Server Uptime</span>
                <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600">
                    <svg class="w-5 h-5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" /></svg>
                </div>
            </div>
            <div class="text-base font-extrabold text-gray-900 tracking-tight">{{ $serverUptime }}</div>
            <div class="text-xs text-indigo-500 mt-1 font-medium">Uptime sistem VPS aktual</div>
        </div>

        <!-- Card 2: Total Outages -->
        <div class="bg-gradient-to-br from-rose-500/10 to-red-600/5 backdrop-blur-xl border border-red-100 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-red-600">Total Mati Lampu</span>
                <div class="p-2 bg-red-50 rounded-lg text-red-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                </div>
            </div>
            <div class="text-2xl font-extrabold text-gray-900 tracking-tight">{{ $totalOutagesThisMonth }} kali</div>
            <div class="text-xs text-red-500 mt-1 font-medium">Bulan ini (sejak tanggal 1)</div>
        </div>

        <!-- Card 3: Rata-Rata Durasi -->
        <div class="bg-gradient-to-br from-amber-500/10 to-orange-600/5 backdrop-blur-xl border border-amber-100 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-amber-600">Rata-Rata Durasi</span>
                <div class="p-2 bg-amber-50 rounded-lg text-amber-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                </div>
            </div>
            <div class="text-base font-extrabold text-gray-900 tracking-tight">{{ $avgDurationStr }}</div>
            <div class="text-xs text-amber-500 mt-1 font-medium">Rata-rata pemulihan daya</div>
        </div>

        <!-- Card 4: Durasi Terlama -->
        <div class="bg-gradient-to-br from-slate-500/10 to-slate-600/5 backdrop-blur-xl border border-slate-200 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold uppercase tracking-wider text-slate-600">Padam Terlama</span>
                <div class="p-2 bg-slate-100 rounded-lg text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                </div>
            </div>
            <div class="text-base font-extrabold text-gray-900 tracking-tight">{{ $maxDurationStr }}</div>
            <div class="text-xs text-slate-500 mt-1 font-medium">Mati lampu terlama bulan ini</div>
        </div>
    </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm overflow-hidden border border-gray-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        @if($type === 'hourly')
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Date & Time</th>
                            <th scope="col" class="hidden md:table-cell px-6 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Device ID</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Device Name</th>
                            <th scope="col" class="hidden sm:table-cell px-6 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Group Area</th>
                            <th scope="col" class="hidden lg:table-cell px-6 py-3.5 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Voltage (V)</th>
                            <th scope="col" class="hidden lg:table-cell px-6 py-3.5 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Current (A)</th>
                            <th scope="col" class="hidden sm:table-cell px-6 py-3.5 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Power (W)</th>
                            <th scope="col" class="px-6 py-3.5 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Daily Energy (kWh)</th>
                        @elseif($type === 'outages')
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Mulai Padam (Outage Start)</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Listrik Menyala (Outage End)</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3.5 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Durasi Padam</th>
                        @else
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Date</th>
                            <th scope="col" class="hidden md:table-cell px-6 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Device ID</th>
                            <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Device Name</th>
                            <th scope="col" class="hidden sm:table-cell px-6 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Operational Area</th>
                            <th scope="col" class="px-6 py-3.5 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Daily Energy (kWh)</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-gray-50 transition-colors">
                            @if($type === 'hourly')
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                    {{ \Carbon\Carbon::parse($log->logged_at)->format('M d, Y | H:i') }}
                                </td>
                                <td class="hidden md:table-cell px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-600">
                                    {{ $log->device->device_id }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <a href="{{ route('devices.show', $log->device->id) }}" class="hover:text-blue-600 transition-colors">
                                        {{ $log->device->name }}
                                    </a>
                                </td>
                                <td class="hidden sm:table-cell px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $log->device->group->name ?? '-' }}
                                </td>
                                <td class="hidden lg:table-cell px-6 py-4 whitespace-nowrap text-sm text-right font-mono text-gray-700">
                                    {{ number_format($log->voltage, 1) }}
                                </td>
                                <td class="hidden lg:table-cell px-6 py-4 whitespace-nowrap text-sm text-right font-mono text-gray-700">
                                    {{ number_format($log->current, 2) }}
                                </td>
                                <td class="hidden sm:table-cell px-6 py-4 whitespace-nowrap text-sm text-right font-mono text-gray-700">
                                    {{ number_format($log->power, 1) }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-semibold text-blue-600 font-mono">
                                    {{ number_format($log->energy, 3) }}
                                </td>
                            @elseif($type === 'outages')
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-semibold">
                                    {{ \Carbon\Carbon::parse($log->outage_start)->timezone('Asia/Jakarta')->format('d M Y - H:i:s') }} WIB
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                    @if($log->outage_end)
                                        {{ \Carbon\Carbon::parse($log->outage_end)->timezone('Asia/Jakarta')->format('d M Y - H:i:s') }} WIB
                                    @else
                                        <span class="text-gray-400 italic">Belum menyala</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    @if($log->outage_end)
                                        <span class="inline-flex items-center rounded-md bg-emerald-50 px-2.5 py-0.5 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                                            ✅ Selesai
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-md bg-rose-50 px-2.5 py-0.5 text-xs font-semibold text-rose-700 ring-1 ring-inset ring-rose-600/20 animate-pulse">
                                            🚨 Sedang Padam
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-blue-600 font-mono">
                                    @if($log->duration_seconds !== null)
                                        @php
                                            $s = $log->duration_seconds;
                                            $h = floor($s / 3600);
                                            $m = floor(($s % 3600) / 60);
                                            $sec = $s % 60;
                                            $durStr = "";
                                            if ($h > 0) $durStr .= "{$h}j ";
                                            if ($m > 0) $durStr .= "{$m}m ";
                                            if ($sec > 0 || empty($durStr)) $durStr .= "{$sec}d";
                                        @endphp
                                        {{ trim($durStr) }}
                                    @else
                                        -
                                    @endif
                                </td>
                            @else
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ \Carbon\Carbon::parse($log->date)->format('M d, Y') }}
                                </td>
                                <td class="hidden md:table-cell px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-600">
                                    {{ $log->device->device_id }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <a href="{{ route('devices.show', $log->device->id) }}" class="hover:text-blue-600 transition-colors">
                                        {{ $log->device->name }}
                                    </a>
                                </td>
                                <td class="hidden sm:table-cell px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $log->device->group->name ?? '-' }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-bold text-blue-600 font-mono">
                                    {{ number_format($log->total_kwh_harian, 2) }}
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $type === 'hourly' ? 8 : ($type === 'outages' ? 4 : 5) }}" class="px-6 py-8 text-center text-gray-500 italic">
                                No historical {{ $type }} data logs available.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($logs->hasPages())
        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
            {{ $logs->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
