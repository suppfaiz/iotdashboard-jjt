<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>IoT Energy Dashboard</title>
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #f8fafc radial-gradient(circle, rgba(148, 163, 184, 0.28) 1.5px, transparent 1.5px) !important;
            background-size: 24px 24px !important;
            color: #0f172a;
            min-height: 100vh;
        }

        /* Floating background orbs */
        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(120px);
            opacity: 0.35;
            z-index: 1;
            /* Render in front of body background but behind z-10 content wrapper */
            animation: float 25s infinite ease-in-out;
            pointer-events: none;
            will-change: transform;
            transform: translate3d(0, 0, 0);
            backface-visibility: hidden;
        }

        .orb-1 {
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, #60a5fa 0%, rgba(96, 165, 250, 0.1) 70%);
            top: -10%;
            left: -10%;
            animation-delay: 0s;
        }

        .orb-2 {
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, #34d399 0%, rgba(52, 211, 153, 0.1) 70%);
            bottom: -15%;
            right: -10%;
            animation-delay: -5s;
        }

        .orb-3 {
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, #22d3ee 0%, rgba(34, 211, 238, 0.1) 70%);
            bottom: 30%;
            left: 15%;
            animation-delay: -10s;
        }

        @keyframes float {

            0%,
            100% {
                transform: translate3d(0, 0, 0) scale(1) rotate(0deg);
            }

            50% {
                transform: translate3d(0, -40px, 0) scale(1.15) rotate(180deg);
            }
        }

        /* Scrollbar styling */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(241, 245, 249, 0.5);
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.3);
            border-radius: 9999px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(148, 163, 184, 0.5);
        }
    </style>
</head>

<body class="antialiased relative pb-20 md:pb-0 overflow-x-hidden">

    <!-- Ambient Background Orbs -->
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>

    <!-- Main Content Stacking Wrapper -->
    <div class="relative z-10 min-h-screen flex flex-col">

        <nav class="bg-white/85 backdrop-blur-xl border-b border-slate-200/80 shadow-sm sticky top-0 z-50">
            @php
                $offlineNotifications = collect();
                if (auth()->check() && auth()->user()->role === 'admin') {
                    $allDevices = \App\Models\Device::all();
                    foreach ($allDevices as $dev) {
                        $lastSeen = \Illuminate\Support\Facades\Cache::get("last_seen:{$dev->device_id}", 0);
                        if ($lastSeen > 0 && (now()->timestamp - $lastSeen) >= 15) {
                            $offlineNotifications->push((object) [
                                'title' => 'Device Offline',
                                'message' => "{$dev->name} is disconnected.",
                                'time' => \Carbon\Carbon::createFromTimestamp($lastSeen)->diffForHumans()
                            ]);
                        }
                    }
                }
            @endphp
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-20">
                    <div class="flex items-center">
                        <a href="{{ route('dashboard') }}" class="flex items-center mr-6">
                            <img src="{{ asset('logo.png') }}" alt="Jamkrida Energy"
                                class="h-12 w-auto object-contain filter drop-shadow-sm">
                        </a>

                        @auth
                            <div class="hidden md:ml-6 md:flex md:items-center md:space-x-1.5">
                                <a href="{{ route('dashboard') }}"
                                    class="{{ request()->routeIs('dashboard') ? 'bg-blue-600/10 text-blue-600 border border-blue-500/20' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 border border-transparent' }} rounded-xl px-3 py-2 text-sm font-semibold transition-all duration-300 whitespace-nowrap">Dashboard</a>
                                <a href="{{ route('devices.index') }}"
                                    class="{{ request()->routeIs('devices.*') ? 'bg-blue-600/10 text-blue-600 border border-blue-500/20' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 border border-transparent' }} rounded-xl px-3 py-2 text-sm font-semibold transition-all duration-300 whitespace-nowrap">Devices</a>
                                <a href="{{ route('changelog') }}"
                                    class="{{ request()->routeIs('changelog') ? 'bg-blue-600/10 text-blue-600 border border-blue-500/20' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 border border-transparent' }} rounded-xl px-3 py-2 text-sm font-semibold transition-all duration-300 whitespace-nowrap">Changelog</a>
                                @if(auth()->user()->role === 'admin')
                                    <a href="{{ route('logs.index') }}"
                                        class="{{ request()->routeIs('logs.*') ? 'bg-blue-600/10 text-blue-600 border border-blue-500/20' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 border border-transparent' }} rounded-xl px-3 py-2 text-sm font-semibold transition-all duration-300 whitespace-nowrap">Historical Logs</a>
                                    <a href="{{ route('reports.index') }}"
                                        class="{{ request()->routeIs('reports.*') ? 'bg-blue-600/10 text-blue-600 border border-blue-500/20' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 border border-transparent' }} rounded-xl px-3 py-2 text-sm font-semibold transition-all duration-300 whitespace-nowrap">Reports</a>
                                    <a href="{{ route('settings.edit') }}"
                                        class="{{ request()->routeIs('settings.*') ? 'bg-blue-600/10 text-blue-600 border border-blue-500/20' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 border border-transparent' }} rounded-xl px-3 py-2 text-sm font-semibold transition-all duration-300 whitespace-nowrap">Settings</a>
                                    <a href="{{ route('docs.index') }}"
                                        class="{{ request()->routeIs('docs.*') ? 'bg-blue-600/10 text-blue-600 border border-blue-500/20' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 border border-transparent' }} rounded-xl px-3 py-2 text-sm font-semibold transition-all duration-300 whitespace-nowrap">Docs</a>
                                    <a href="/api-docs/index.html" target="_blank"
                                        class="text-slate-600 hover:bg-slate-100 hover:text-slate-900 border border-transparent rounded-xl px-3 py-2 text-sm font-semibold transition-all duration-300 whitespace-nowrap">API Docs</a>
                                @endif
                            </div>
                        @endauth
                    </div>

                    @auth
                        <div class="flex items-center space-x-3">
                            @if(auth()->user()->role === 'admin' && request()->routeIs('dashboard'))
                                <button type="button"
                                    onclick="document.getElementById('add-device-modal').classList.remove('hidden')"
                                    class="relative inline-flex items-center gap-x-2 rounded-xl bg-blue-600 px-4 py-2 text-sm font-bold text-white shadow-sm hover:bg-blue-500 transition-all duration-300">
                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path
                                            d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                                    </svg>
                                    <span class="hidden sm:inline">Add Device</span><span class="sm:hidden">Add</span>
                                </button>
                            @endif

                            <!-- Notifications Dropdown -->
                            @if(auth()->user()->role === 'admin')
                                <div class="relative">
                                    <button type="button"
                                        onclick="document.getElementById('notification-dropdown').classList.toggle('hidden')"
                                        class="relative p-2.5 text-slate-500 hover:text-slate-800 focus:outline-none transition-all duration-300 rounded-xl hover:bg-slate-100"
                                        id="notification-button">
                                        <span class="sr-only">Notifications</span>
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                        </svg>
                                        @if($offlineNotifications->count() > 0)
                                            <span
                                                class="absolute top-2 right-2.5 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-white animate-pulse"></span>
                                        @endif
                                    </button>

                                    <div id="notification-dropdown"
                                        class="hidden absolute right-0 mt-3 w-80 bg-white/95 backdrop-blur-2xl rounded-2xl shadow-xl border border-slate-200/80 overflow-hidden z-50">
                                        <div
                                            class="px-4 py-3.5 border-b border-slate-100 bg-slate-50/60 flex justify-between items-center">
                                            <h3 class="text-sm font-semibold text-slate-800">Alerts & Status</h3>
                                            @if($offlineNotifications->count() > 0)
                                                <span
                                                    class="bg-red-100 text-red-700 text-xs font-bold px-2 py-0.5 rounded-full border border-red-200">{{ $offlineNotifications->count() }}
                                                    Alert</span>
                                            @endif
                                        </div>
                                        <div class="max-h-80 overflow-y-auto">
                                            <!-- Changelog Update Alert -->
                                            <button type="button" onclick="openChangelogModal()"
                                                class="w-full text-left block px-4 py-3.5 hover:bg-blue-50/40 transition-colors border-b border-slate-150 bg-blue-50/10">
                                                <div class="flex items-start">
                                                    <div class="flex-shrink-0 mt-0.5">
                                                        <div
                                                            class="w-8 h-8 rounded-lg bg-blue-100/80 border border-blue-200/60 flex items-center justify-center text-blue-600">
                                                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                                            </svg>
                                                        </div>
                                                    </div>
                                                    <div class="ml-3 w-0 flex-1">
                                                        <p class="text-xs font-bold text-slate-800">🚀 Dashboard V2.0 Updates!
                                                        </p>
                                                        <p class="text-[10px] text-slate-500 mt-0.5">Click to view 6 new secure
                                                            features.</p>
                                                        <span
                                                            class="inline-block mt-1 text-[9px] font-bold text-blue-600 bg-blue-100/50 px-2 py-0.5 rounded-full uppercase tracking-wider">New
                                                            Release</span>
                                                    </div>
                                                </div>
                                            </button>
                                            @forelse($offlineNotifications as $notif)
                                                <a href="{{ route('devices.index') }}"
                                                    class="block px-4 py-3.5 hover:bg-slate-50/50 transition-colors border-b border-slate-100 last:border-0">
                                                    <div class="flex items-start">
                                                        <div class="flex-shrink-0 mt-0.5">
                                                            <div
                                                                class="w-8 h-8 rounded-lg bg-red-50 border border-red-100 flex items-center justify-center text-red-500">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                                    viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                                </svg>
                                                            </div>
                                                        </div>
                                                        <div class="ml-3 w-0 flex-1">
                                                            <p class="text-xs font-bold text-slate-800">{{ $notif->title }}</p>
                                                            <p class="text-xs text-slate-500 mt-0.5">{{ $notif->message }}</p>
                                                            <p class="mt-1 text-[10px] text-slate-400 font-mono">{{ $notif->time }}
                                                            </p>
                                                        </div>
                                                    </div>
                                                </a>
                                            @empty
                                                <div class="px-4 py-8 text-center">
                                                    <svg class="mx-auto h-8 w-8 text-slate-350 mb-2" fill="none"
                                                        stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    <p class="text-xs text-slate-500">All systems functioning normally.</p>
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <!-- Changelog Link (Mobile Only) -->
                            <a href="{{ route('changelog') }}"
                                class="md:hidden p-2.5 text-slate-500 hover:text-slate-800 focus:outline-none transition-all duration-300 rounded-xl hover:bg-slate-100"
                                title="Changelog">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                </svg>
                            </a>

                            <!-- User Dropdown Menu -->
                            <div class="relative hidden md:block">
                                <button type="button" onclick="toggleDropdown()"
                                    class="flex items-center gap-2.5 text-sm font-semibold text-slate-700 hover:text-slate-900 focus:outline-none py-2 px-3.5 rounded-xl hover:bg-slate-100/60 border border-transparent hover:border-slate-200/60 transition-all duration-300"
                                    id="user-menu-button">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 filter drop-shadow-sm"></span>
                                    <span>{{ auth()->user()->name }}</span>
                                    <span
                                        class="text-[10px] uppercase tracking-wider font-extrabold px-2 py-0.5 rounded-full {{ auth()->user()->role === 'admin' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-600' }}">
                                        {{ auth()->user()->role }}
                                    </span>
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                            d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>

                                <!-- Dropdown panel -->
                                <div id="user-dropdown"
                                    class="hidden absolute right-0 mt-3 w-48 rounded-2xl shadow-xl py-2 bg-white/95 backdrop-blur-2xl border border-slate-200/80 z-50">
                                    <a href="{{ route('profile.edit') }}"
                                        class="block px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-slate-905 transition-colors">Your
                                        Profile</a>
                                    <a href="{{ route('changelog') }}"
                                        class="block px-4 py-2.5 text-sm font-medium text-slate-700 hover:bg-slate-50 hover:text-slate-905 transition-colors">Changelog</a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit"
                                            class="w-full text-left block px-4 py-2.5 text-sm font-medium text-red-500 hover:bg-red-50/40 transition-colors">
                                            Log Out
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="flex items-center space-x-4">
                            <a href="{{ route('login') }}" class="text-sm font-bold text-slate-500 hover:text-slate-800">Log
                                in</a>
                        </div>
                    @endauth
                </div>
            </div>
        </nav>

        <main class="flex-grow container mx-auto px-4 py-8 max-w-7xl relative z-10">
            @yield('content')
            @isset($slot)
                {{ $slot }}
            @endisset
        </main>

        <!-- Footer -->
        <footer
            class="mt-auto py-8 border-t border-slate-200/85 bg-white/40 backdrop-blur-md relative z-10 hidden md:block text-slate-500">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <p class="text-center text-xs font-semibold tracking-wide text-slate-500">
                    &copy; {{ date('Y') }} PT JAMKRIDA JATENG | Telemetry Monitoring Dashboard. All rights reserved.
                </p>
            </div>
        </footer>

        <!-- Mobile Footer Padding -->
        <div class="h-24 md:hidden block relative z-10">
            <p class="text-center text-[10px] text-slate-450 font-bold tracking-widest uppercase mt-6">
                &copy; {{ date('Y') }} DEPT IT JAMKRIDA JATENG
            </p>
        </div>
    </div> <!-- End Main Content Stacking Wrapper -->

    <script>
        function toggleDropdown() {
            const dropdown = document.getElementById('user-dropdown');
            if (dropdown) dropdown.classList.toggle('hidden');
        }
        window.addEventListener('click', function (e) {
            const userButton = document.getElementById('user-menu-button');
            const userDropdown = document.getElementById('user-dropdown');
            if (userButton && userDropdown && !userButton.contains(e.target) && !userDropdown.contains(e.target)) {
                userDropdown.classList.add('hidden');
            }

            const notifButton = document.getElementById('notification-button');
            const notifDropdown = document.getElementById('notification-dropdown');
            if (notifButton && notifDropdown && !notifButton.contains(e.target) && !notifDropdown.contains(e.target)) {
                notifDropdown.classList.add('hidden');
            }
        });

        function updateRealtimeClock() {
            const clockEl = document.getElementById('realtime-clock');
            if (!clockEl) return;

            const now = new Date();
            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

            const dayName = days[now.getDay()];
            const day = String(now.getDate()).padStart(2, '0');
            const monthName = months[now.getMonth()];
            const year = now.getFullYear();

            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const seconds = String(now.getSeconds()).padStart(2, '0');

            clockEl.textContent = `${dayName}, ${day} ${monthName} ${year} | ${hours}:${minutes}:${seconds}`;
        }

        function openChangelogModal() {
            const dropdown = document.getElementById('notification-dropdown');
            if (dropdown) dropdown.classList.add('hidden');
            const modal = document.getElementById('changelog-modal');
            if (modal) modal.classList.remove('hidden');
        }

        function closeChangelogModal() {
            const modal = document.getElementById('changelog-modal');
            if (modal) modal.classList.add('hidden');
        }

        document.addEventListener('DOMContentLoaded', () => {
            updateRealtimeClock();
            setInterval(updateRealtimeClock, 1000);

            // Auto open changelog for new v2 update
            const hasSeen = localStorage.getItem('changelog_seen_v2');
            if (!hasSeen) {
                setTimeout(openChangelogModal, 1200);
                localStorage.setItem('changelog_seen_v2', 'true');
            }
        });
    </script>

    <!-- Mobile Bottom Navigation Bar -->
    @auth
        <div class="md:hidden fixed bottom-0 left-0 w-full bg-white/90 backdrop-blur-xl border-t border-slate-200/80 shadow-[0_-4px_30px_rgba(0,0,0,0.05)] z-50 px-2 py-2"
            style="padding-bottom: env(safe-area-inset-bottom, 0.5rem);">
            <div class="flex items-center justify-around">
                <a href="{{ route('dashboard') }}"
                    class="flex flex-col items-center justify-center py-2 flex-1 rounded-xl {{ request()->routeIs('dashboard') ? 'text-blue-600 font-bold bg-blue-600/5' : 'text-slate-500 hover:text-slate-800' }}">
                    <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span class="text-[9px] tracking-wide whitespace-nowrap truncate w-full text-center">Home</span>
                </a>

                <a href="{{ route('devices.index') }}"
                    class="flex flex-col items-center justify-center py-2 flex-1 rounded-xl {{ request()->routeIs('devices.*') ? 'text-blue-600 font-bold bg-blue-600/5' : 'text-slate-500 hover:text-slate-800' }}">
                    <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
                    </svg>
                    <span class="text-[9px] tracking-wide whitespace-nowrap truncate w-full text-center">Devices</span>
                </a>

                @if(auth()->user()->role === 'admin')
                    <a href="{{ route('logs.index') }}"
                        class="flex flex-col items-center justify-center py-2 flex-1 rounded-xl {{ request()->routeIs('logs.*') ? 'text-blue-600 font-bold bg-blue-600/5' : 'text-slate-500 hover:text-slate-800' }}">
                        <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                        <span class="text-[9px] tracking-wide whitespace-nowrap truncate w-full text-center">Logs</span>
                    </a>
                    <a href="{{ route('reports.index') }}"
                        class="flex flex-col items-center justify-center py-2 flex-1 rounded-xl {{ request()->routeIs('reports.*') ? 'text-blue-600 font-bold bg-blue-600/5' : 'text-slate-500 hover:text-slate-800' }}">
                        <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span class="text-[9px] tracking-wide whitespace-nowrap truncate w-full text-center">Reports</span>
                    </a>
                    <a href="{{ route('settings.edit') }}"
                        class="flex flex-col items-center justify-center py-2 flex-1 rounded-xl {{ request()->routeIs('settings.*') ? 'text-blue-600 font-bold bg-blue-600/5' : 'text-slate-500 hover:text-slate-800' }}">
                        <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span class="text-[9px] tracking-wide whitespace-nowrap truncate w-full text-center">Settings</span>
                    </a>
                @endif

                <a href="{{ route('profile.edit') }}"
                    class="flex flex-col items-center justify-center py-2 flex-1 rounded-xl {{ request()->routeIs('profile.*') ? 'text-blue-600 font-bold bg-blue-600/5' : 'text-slate-500 hover:text-slate-800' }}">
                    <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    <span class="text-[9px] tracking-wide whitespace-nowrap truncate w-full text-center">Profile</span>
                </a>
            </div>
        </div>
    @endauth

    <!-- Changelog Modal -->
    <div id="changelog-modal" class="hidden fixed inset-0 z-[100] overflow-y-auto" aria-labelledby="modal-title"
        role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true"
                onclick="closeChangelogModal()"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:min-h-screen" aria-hidden="true">&#8203;</span>
            <div
                class="inline-block align-middle bg-white/90 backdrop-blur-2xl rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl sm:w-full border border-slate-200/80">
                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div class="flex items-center gap-2">
                        <span class="p-2 bg-blue-50 text-blue-600 rounded-xl border border-blue-100">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                        </span>
                        <div>
                            <h3 class="text-base font-bold text-slate-900 leading-none">What's New in Version 2.0</h3>
                            <p class="text-[10px] text-slate-500 font-medium mt-1">Released June 24, 2026</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeChangelogModal()"
                        class="text-slate-400 hover:text-slate-600 transition-colors p-1.5 hover:bg-slate-100 rounded-xl">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="p-6 space-y-5 max-h-[60vh] overflow-y-auto">
                    <!-- Feature 1 -->
                    <div class="flex gap-4">
                        <div
                            class="w-6 h-6 rounded-lg bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 text-xs font-bold flex-shrink-0">
                            1</div>
                        <div>
                            <h4 class="text-sm font-bold text-slate-800">Calibration Multipliers</h4>
                            <p class="text-xs text-slate-500 mt-0.5">Admin-only multiplier calibration configuration for
                                voltage and current scaling (e.g. adjust scaling drift to calibrate with physical
                                meters).</p>
                        </div>
                    </div>
                    <!-- Feature 2 -->
                    <div class="flex gap-4">
                        <div
                            class="w-6 h-6 rounded-lg bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 text-xs font-bold flex-shrink-0">
                            2</div>
                        <div>
                            <h4 class="text-sm font-bold text-slate-800">Telegram Bot Notifications</h4>
                            <p class="text-xs text-slate-500 mt-0.5">Instant alerts dispatched to Telegram for device
                                offline states, recovery back online, and monthly budget limit crossings.</p>
                        </div>
                    </div>
                    <!-- Feature 3 -->
                    <div class="flex gap-4">
                        <div
                            class="w-6 h-6 rounded-lg bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 text-xs font-bold flex-shrink-0">
                            3</div>
                        <div>
                            <h4 class="text-sm font-bold text-slate-800">Interactive Terminal Console</h4>
                            <p class="text-xs text-slate-500 mt-0.5">Interactive Remote Console on the device page
                                allowing administrators to execute custom JSON commands directly on nodes.</p>
                        </div>
                    </div>
                    <!-- Feature 4 -->
                    <div class="flex gap-4">
                        <div
                            class="w-6 h-6 rounded-lg bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 text-xs font-bold flex-shrink-0">
                            4</div>
                        <div>
                            <h4 class="text-sm font-bold text-slate-800">Monthly Energy & Cost Budgets</h4>
                            <p class="text-xs text-slate-500 mt-0.5">Configure target monthly cost (Rp) and consumption
                                (kWh) budgets per device with alert markers at 80% and 100% usage.</p>
                        </div>
                    </div>
                    <!-- Feature 5 -->
                    <div class="flex gap-4">
                        <div
                            class="w-6 h-6 rounded-lg bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 text-xs font-bold flex-shrink-0">
                            5</div>
                        <div>
                            <h4 class="text-sm font-bold text-slate-800">Weekly Energy Comparison Analytics</h4>
                            <p class="text-xs text-slate-500 mt-0.5">A new "Weekly" dashboard chart tab that renders a
                                side-by-side analysis of This Week vs. Last Week consumption trends.</p>
                        </div>
                    </div>
                    <!-- Feature 6 -->
                    <div class="flex gap-4">
                        <div
                            class="w-6 h-6 rounded-lg bg-blue-50 border border-blue-100 flex items-center justify-center text-blue-600 text-xs font-bold flex-shrink-0">
                            6</div>
                        <div>
                            <h4 class="text-sm font-bold text-slate-800">Authentication-Locked CSV Export</h4>
                            <p class="text-xs text-slate-500 mt-0.5">Secure, authenticated CSV report exports for daily
                                log analytics, allowing quick spreadsheet downloads.</p>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-50/50 px-6 py-4 border-t border-slate-100 flex justify-between items-center">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Jamkrida IoT
                        Dashboard</span>
                    <button type="button" onclick="closeChangelogModal()"
                        class="rounded-xl px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white font-bold text-xs shadow-sm transition-colors">
                        Got it!
                    </button>
                </div>
            </div>
        </div>
    </div>    <!-- Chatbot YukAnalisaListrikmu -->
    <div class="chatbot-container">
        <!-- Floating Chat Button -->
        <button id="chatbot-toggle-btn" class="fixed bottom-6 right-6 z-50 flex items-center gap-2.5 px-4 py-3 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white rounded-full shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-1 group">
            <span class="relative flex h-3 w-3">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
            </span>
            <svg class="w-5 h-5 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z" />
            </svg>
            <span class="text-xs font-bold tracking-wide">YukAnalisaListrikmu</span>
        </button>

        <!-- Chat Window -->
        <div id="chatbot-window" class="fixed bottom-24 right-6 w-96 h-[520px] z-50 bg-white/95 backdrop-blur-md rounded-2xl shadow-2xl border border-slate-100 flex flex-col hidden transition-all duration-300 opacity-0 translate-y-4">
            <!-- Header -->
            <div class="p-4 bg-gradient-to-r from-blue-600 to-indigo-600 text-white rounded-t-2xl flex justify-between items-center shadow-md">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-white/10 flex items-center justify-center font-bold text-lg text-white">⚡</div>
                    <div>
                        <h4 class="text-sm font-extrabold tracking-wide">YukAnalisaListrikmu</h4>
                        <p class="text-[10px] text-blue-100 font-medium">Asisten Energi Pintar Jamkrida</p>
                    </div>
                </div>
                <button id="chatbot-close-btn" class="text-white/80 hover:text-white transition-colors p-1 hover:bg-white/10 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Messages Area -->
            <div id="chatbot-messages" class="flex-1 p-4 space-y-3.5 overflow-y-auto bg-slate-50/50">
                <!-- Bot Welcome Message -->
                <div class="flex gap-2.5 max-w-[85%]">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0 text-sm">🤖</div>
                    <div class="bg-white px-3.5 py-2.5 rounded-2xl rounded-tl-none shadow-sm border border-slate-100">
                        <p class="text-xs text-slate-700 leading-relaxed">Halo! Saya <b>YukAnalisaListrikmu</b>, asisten pintar untuk menganalisis penggunaan listrik Anda. Ada yang bisa saya bantu hari ini?</p>
                        <!-- Quick Reply suggestions -->
                        <div class="mt-3 flex flex-wrap gap-1.5">
                            <button onclick="sendQuickReply('💡 Tips Hemat Listrik')" class="px-2.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-600 text-[10px] font-bold rounded-lg border border-blue-100 transition-colors">💡 Tips Hemat</button>
                            <button onclick="sendQuickReply('📊 Cara Baca Grafik')" class="px-2.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-600 text-[10px] font-bold rounded-lg border border-blue-100 transition-colors">📊 Baca Grafik</button>
                            <button onclick="sendQuickReply('🔋 Estimasi Tarif PLN')" class="px-2.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-600 text-[10px] font-bold rounded-lg border border-blue-100 transition-colors">🔋 Tarif PLN</button>
                            <button onclick="sendQuickReply('⚠️ Notifikasi Telegram')" class="px-2.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-600 text-[10px] font-bold rounded-lg border border-blue-100 transition-colors">⚠️ Telegram Alert</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Input Area -->
            <div class="p-3 bg-white border-t border-slate-100 flex gap-2 rounded-b-2xl">
                <input type="text" id="chatbot-input" placeholder="Tanya seputar listrik..." class="flex-1 px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:border-blue-500 focus:bg-white transition-all" />
                <button id="chatbot-send-btn" class="px-3 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-bold transition-colors shadow-sm">Kirim</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleBtn = document.getElementById('chatbot-toggle-btn');
            const closeBtn = document.getElementById('chatbot-close-btn');
            const chatWindow = document.getElementById('chatbot-window');
            const chatInput = document.getElementById('chatbot-input');
            const sendBtn = document.getElementById('chatbot-send-btn');
            const messagesArea = document.getElementById('chatbot-messages');

            // Open/Close toggle
            toggleBtn.addEventListener('click', function () {
                if (chatWindow.classList.contains('hidden')) {
                    chatWindow.classList.remove('hidden');
                    setTimeout(() => {
                        chatWindow.classList.remove('opacity-0', 'translate-y-4');
                    }, 50);
                } else {
                    closeChat();
                }
            });

            closeBtn.addEventListener('click', closeChat);

            function closeChat() {
                chatWindow.classList.add('opacity-0', 'translate-y-4');
                setTimeout(() => {
                    chatWindow.classList.add('hidden');
                }, 300);
            }

            // Send message
            sendBtn.addEventListener('click', handleSend);
            chatInput.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') handleSend();
            });

            function handleSend() {
                const text = chatInput.value.trim();
                if (!text) return;

                // Append user message
                appendMessage('user', text);
                chatInput.value = '';

                // Show typing indicator
                const typingId = showTypingIndicator();

                // Get bot response
                setTimeout(() => {
                    removeTypingIndicator(typingId);
                    const response = getBotResponse(text);
                    appendMessage('bot', response);
                }, 1000);
            }

            window.sendQuickReply = function (text) {
                appendMessage('user', text);
                const typingId = showTypingIndicator();
                setTimeout(() => {
                    removeTypingIndicator(typingId);
                    const response = getBotResponse(text);
                    appendMessage('bot', response);
                }, 1000);
            };

            function appendMessage(sender, text) {
                const msgDiv = document.createElement('div');
                if (sender === 'user') {
                    msgDiv.className = 'flex justify-end max-w-[85%] ml-auto';
                    msgDiv.innerHTML = `
                        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 text-white px-3.5 py-2.5 rounded-2xl rounded-tr-none shadow-sm text-xs leading-relaxed">
                            ${text}
                        </div>
                    `;
                } else {
                    msgDiv.className = 'flex gap-2.5 max-w-[85%]';
                    msgDiv.innerHTML = `
                        <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0 text-sm">🤖</div>
                        <div class="bg-white px-3.5 py-2.5 rounded-2xl rounded-tl-none shadow-sm border border-slate-100 text-xs text-slate-700 leading-relaxed">
                            ${text}
                        </div>
                    `;
                }
                messagesArea.appendChild(msgDiv);
                messagesArea.scrollTop = messagesArea.scrollHeight;
            }

            function showTypingIndicator() {
                const typingDiv = document.createElement('div');
                typingDiv.id = 'typing-indicator';
                typingDiv.className = 'flex gap-2.5 max-w-[85%]';
                typingDiv.innerHTML = `
                    <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0 text-sm">🤖</div>
                    <div class="bg-white px-3.5 py-2.5 rounded-2xl rounded-tl-none shadow-sm border border-slate-100 text-xs text-slate-400 flex items-center gap-1">
                        <span class="animate-bounce" style="animation-delay: 0ms">.</span>
                        <span class="animate-bounce" style="animation-delay: 150ms">.</span>
                        <span class="animate-bounce" style="animation-delay: 300ms">.</span>
                        <span>mengetik...</span>
                    </div>
                `;
                messagesArea.appendChild(typingDiv);
                messagesArea.scrollTop = messagesArea.scrollHeight;
                return typingDiv;
            }

            function removeTypingIndicator(el) {
                if (el && el.parentNode) {
                    el.parentNode.removeChild(el);
                }
            }

            function getBotResponse(input) {
                const text = input.toLowerCase();

                if (text.includes('tips') || text.includes('hemat')) {
                    return `💡 <b>Berikut Tips Praktis Menghemat Listrik Anda:</b><br><br>
                        1. <b>Matikan Beban Standby</b>: Cabut colokan TV, komputer, atau charger HP yang tidak dipakai. Beban standby berkontribusi hingga 10% tagihan bulanan.<br>
                        2. <b>Gunakan LED Berkualitas</b>: Ganti lampu pijar Anda dengan LED. Lampu LED menggunakan daya 80% lebih sedikit untuk tingkat kecerahan yang sama.<br>
                        3. <b>Atur Limit Alarm Anggaran</b>: Gunakan fitur <b>Monthly Cost/Kwh Budget</b> di menu Settings pada dashboard ini untuk memantau konsumsi agar tidak melebihi anggaran bulanan Anda.`;
                }

                if (text.includes('grafik') || text.includes('baca')) {
                    return `📊 <b>Cara Membaca Grafik Sensor Dashboard:</b><br><br>
                        * <b>Grafik Voltase (V)</b>: Memantau kestabilan tegangan listrik. Normalnya berkisar di <b>220V</b>. Jika turun di bawah 200V atau di atas 240V, instalasi Anda berisiko merusak peralatan elektronik.<br>
                        * <b>Grafik Arus (A)</b>: Menampilkan besarnya arus listrik yang mengalir ke beban Anda.<br>
                        * <b>Grafik Daya (W)</b>: Menunjukkan daya aktif nyata yang sedang disedot alat listrik Anda saat ini (V x A).<br>
                        * <b>Grafik Energi (kWh)</b>: Menampilkan akumulasi total pemakaian listrik harian Anda.`;
                }

                if (text.includes('tarif') || text.includes('pln') || text.includes('biaya') || text.includes('wbp')) {
                    return `🔋 <b>Estimasi Tarif PLN (Time of Use - ToU):</b><br><br>
                        Sistem di dashboard ini menghitung estimasi biaya harian Anda berdasarkan dua tarif:<br>
                        * <b>WBP (Waktu Beban Puncak)</b>: Berlaku pukul <b>17:00 - 22:00</b> dengan tarif lebih tinggi (misal Rp2.000/kWh) karena beban puncak jaringan listrik.<br>
                        * <b>LWBP (Luar Waktu Beban Puncak)</b>: Berlaku pukul <b>22:00 - 17:00</b> dengan tarif standar (misal Rp1.444,70/kWh).<br><br>
                        Anda bisa mengubah nilai tarif ini kapan saja di menu <b>Settings</b>.`;
                }

                if (text.includes('telegram') || text.includes('notif') || text.includes('mati') || text.includes('offline')) {
                    return `⚠️ <b>Fitur Notifikasi Telegram Alert:</b><br><br>
                        * Bot akan otomatis mengirimkan chat ke Telegram Anda jika voltase berada di luar batas aman (di bawah 200V / di atas 240V).<br>
                        * Jika alat sensor ESP32 terputus (mati listrik atau Wi-Fi mati) selama <b>5 menit</b>, Anda akan mendapat chat peringatan <b>DEVICE OFFLINE</b>.<br>
                        * Ketika alat menyala lagi, bot mengirimkan chat pemulihan <b>DEVICE ONLINE RECOVERY</b>.`;
                }

                return `🤖 <b>Halo! Saya YukAnalisaListrikmu.</b><br><br>
                    Ada yang bisa saya bantu tentang pemantauan energi Anda?<br>
                    Silakan tanyakan hal berikut:<br>
                    * 💡 <i>"Tips hemat listrik"</i><br>
                    * 📊 <i>"Cara membaca grafik"</i><br>
                    * 🔋 <i>"Bagaimana tarif PLN WBP/LWBP dihitung?"</i><br>
                    * ⚠️ <i>"Bagaimana cara kerja notifikasi Telegram?"</i>`;
            }
        });
    </script>
</body>

</html>