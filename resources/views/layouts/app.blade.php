<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>IoT Energy Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            color: #0f172a;
        }
        /* Floating background orbs */
        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(100px);
            opacity: 0.22;
            z-index: 0;
            animation: float 25s infinite ease-in-out;
            pointer-events: none;
        }
        .orb-1 {
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, #3b82f6 0%, #60a5fa 100%);
            top: -10%;
            left: -10%;
            animation-delay: 0s;
        }
        .orb-2 {
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, #10b981 0%, #34d399 100%);
            bottom: -15%;
            right: -10%;
            animation-delay: -5s;
        }
        .orb-3 {
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, #06b6d4 0%, #22d3ee 100%);
            bottom: 30%;
            left: 15%;
            animation-delay: -10s;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0) scale(1) rotate(0deg);
            }
            50% {
                transform: translateY(-30px) scale(1.1) rotate(180deg);
            }
        }
    </style>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased min-h-screen flex flex-col relative pb-20 md:pb-0">
    <!-- Ambient Background Orbs -->
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>

    <nav class="bg-white/80 backdrop-blur-md border-b border-gray-200 shadow-sm sticky top-0 z-50">
        @php
            $offlineNotifications = collect();
            if(auth()->check() && auth()->user()->role === 'admin') {
                $allDevices = \App\Models\Device::all();
                foreach($allDevices as $dev) {
                    $lastSeen = \Illuminate\Support\Facades\Cache::get("last_seen:{$dev->device_id}", 0);
                    // If last seen is recorded but it's older than 15 seconds, it's offline
                    if ($lastSeen > 0 && (now()->timestamp - $lastSeen) >= 15) {
                        $offlineNotifications->push((object)[
                            'title' => 'Device Disconnected',
                            'message' => "{$dev->name} is offline.",
                            'time' => \Carbon\Carbon::createFromTimestamp($lastSeen)->diffForHumans()
                        ]);
                    }
                }
            }
        @endphp
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center">
                    <a href="{{ route('dashboard') }}" class="flex items-center">
                        <img src="{{ asset('logo.png') }}" alt="Jamkrida Energy" class="h-11 w-auto object-contain">
                    </a>
                    
                    @auth
                    <div class="hidden md:ml-10 md:flex md:items-center md:space-x-4">
                        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} rounded-md px-3 py-2 text-sm font-medium transition-colors">Dashboard</a>
                        @if(auth()->user()->role === 'admin')
                            <a href="{{ route('devices.index') }}" class="{{ request()->routeIs('devices.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} rounded-md px-3 py-2 text-sm font-medium transition-colors">Devices</a>
                            <a href="{{ route('logs.index') }}" class="{{ request()->routeIs('logs.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} rounded-md px-3 py-2 text-sm font-medium transition-colors">Historical Logs</a>
                            <a href="{{ route('reports.index') }}" class="{{ request()->routeIs('reports.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} rounded-md px-3 py-2 text-sm font-medium transition-colors">Reports</a>
                            <a href="{{ route('settings.edit') }}" class="{{ request()->routeIs('settings.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} rounded-md px-3 py-2 text-sm font-medium transition-colors">Settings</a>
                            <a href="{{ route('docs.index') }}" class="{{ request()->routeIs('docs.*') ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }} rounded-md px-3 py-2 text-sm font-medium transition-colors">Documentation</a>
                        @endif
                    </div>
                    @endauth
                </div>
                
                @auth
                <div class="flex items-center space-x-4">
                    @if(auth()->user()->role === 'admin' && request()->routeIs('dashboard'))
                        <button type="button" onclick="document.getElementById('add-device-modal').classList.remove('hidden')" class="relative inline-flex items-center gap-x-1.5 rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 transition-colors">
                            <svg class="-ml-0.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                            </svg>
                            <span class="hidden sm:inline">Add Device</span><span class="sm:hidden">Add</span>
                        </button>
                    @endif
                    
                    <!-- Notifications Dropdown -->
                    @if(auth()->user()->role === 'admin')
                    <div class="relative">
                        <button type="button" onclick="document.getElementById('notification-dropdown').classList.toggle('hidden')" class="relative p-2 text-gray-500 hover:text-gray-700 focus:outline-none transition-colors rounded-full hover:bg-gray-100" id="notification-button">
                            <span class="sr-only">View notifications</span>
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" /></svg>
                            @if($offlineNotifications->count() > 0)
                            <span class="absolute top-1.5 right-2 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-white animate-pulse"></span>
                            @endif
                        </button>

                        <div id="notification-dropdown" class="hidden absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden z-50">
                            <div class="px-4 py-3 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                                <h3 class="text-sm font-semibold text-gray-900">Notifications</h3>
                                @if($offlineNotifications->count() > 0)
                                <span class="bg-red-100 text-red-800 text-xs font-medium px-2 py-0.5 rounded-full">{{ $offlineNotifications->count() }} New</span>
                                @endif
                            </div>
                            <div class="max-h-80 overflow-y-auto">
                                @forelse($offlineNotifications as $notif)
                                <a href="{{ route('devices.index') }}" class="block px-4 py-3 hover:bg-gray-50 transition-colors border-b border-gray-50 last:border-0">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 mt-0.5">
                                            <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center text-red-600">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                                            </div>
                                        </div>
                                        <div class="ml-3 w-0 flex-1">
                                            <p class="text-sm font-medium text-gray-900">{{ $notif->title }}</p>
                                            <p class="text-sm text-gray-500">{{ $notif->message }}</p>
                                            <p class="mt-1 text-xs text-gray-400">{{ $notif->time }}</p>
                                        </div>
                                    </div>
                                </a>
                                @empty
                                <div class="px-4 py-8 text-center">
                                    <svg class="mx-auto h-8 w-8 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" /></svg>
                                    <p class="text-sm text-gray-500">All systems operational.</p>
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    <!-- User Dropdown Menu -->
                    <div class="relative hidden md:block">
                        <button type="button" onclick="toggleDropdown()" class="flex items-center gap-2 text-sm font-medium text-gray-700 hover:text-gray-900 focus:outline-none py-2 px-3 rounded-md hover:bg-gray-50 transition-colors" id="user-menu-button">
                            <span>{{ auth()->user()->name }}</span>
                            <span class="text-xs px-2 py-0.5 rounded-full {{ auth()->user()->role === 'admin' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ ucfirst(auth()->user()->role) }}
                            </span>
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        
                        <!-- Dropdown panel -->
                        <div id="user-dropdown" class="hidden absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white border border-gray-200 ring-1 ring-black ring-opacity-5 focus:outline-none z-50">
                            <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">Your Profile</a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                    Log Out
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @else
                <div class="flex items-center space-x-4">
                    <a href="{{ route('login') }}" class="text-sm font-medium text-gray-700 hover:text-gray-900">Log in</a>
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
    <footer class="mt-auto py-6 border-t border-gray-200 bg-white/50 backdrop-blur-sm relative z-10 hidden md:block">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="text-center text-sm text-gray-500 font-medium">
                &copy; {{ date('Y') }} Copyright by Departement IT PT Jamkrida Jateng. All rights reserved.
            </p>
        </div>
    </footer>
    
    <!-- Mobile Footer Padding (To ensure content isn't hidden behind the sticky nav) -->
    <div class="h-20 md:hidden block relative z-10">
        <p class="text-center text-[10px] text-gray-400 font-medium mt-4">
            &copy; {{ date('Y') }} Dept IT PT Jamkrida Jateng
        </p>
    </div>


    <script>
        function toggleDropdown() {
            const dropdown = document.getElementById('user-dropdown');
            if (dropdown) dropdown.classList.toggle('hidden');
        }
        // Close dropdowns when clicking outside
        window.addEventListener('click', function(e) {
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
        
        // Start clock immediately and update every second
        document.addEventListener('DOMContentLoaded', () => {
            updateRealtimeClock();
            setInterval(updateRealtimeClock, 1000);
        });
    </script>

    <!-- Mobile Bottom Navigation Bar -->
    @auth
    <div class="md:hidden fixed bottom-0 left-0 w-full bg-white/90 backdrop-blur-lg border-t border-gray-200 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] z-50 px-2 py-2" style="padding-bottom: env(safe-area-inset-bottom, 0.5rem);">
        <div class="flex items-center justify-around">
            <a href="{{ route('dashboard') }}" class="flex flex-col items-center justify-center py-2 flex-1 rounded-xl {{ request()->routeIs('dashboard') ? 'text-blue-600' : 'text-gray-500 hover:text-gray-900' }}">
                <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" /></svg>
                <span class="text-[10px] font-semibold whitespace-nowrap truncate w-full text-center">Home</span>
            </a>
            
            @if(auth()->user()->role === 'admin')
            <a href="{{ route('devices.index') }}" class="flex flex-col items-center justify-center py-2 flex-1 rounded-xl {{ request()->routeIs('devices.*') ? 'text-blue-600' : 'text-gray-500 hover:text-gray-900' }}">
                <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" /></svg>
                <span class="text-[10px] font-semibold whitespace-nowrap truncate w-full text-center">Devices</span>
            </a>
            <a href="{{ route('logs.index') }}" class="flex flex-col items-center justify-center py-2 flex-1 rounded-xl {{ request()->routeIs('logs.*') ? 'text-blue-600' : 'text-gray-500 hover:text-gray-900' }}">
                <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" /></svg>
                <span class="text-[10px] font-semibold whitespace-nowrap truncate w-full text-center">Logs</span>
            </a>
            
            <a href="{{ route('reports.index') }}" class="flex flex-col items-center justify-center py-2 flex-1 rounded-xl {{ request()->routeIs('reports.*') ? 'text-blue-600' : 'text-gray-500 hover:text-gray-900' }}">
                <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                <span class="text-[10px] font-semibold whitespace-nowrap truncate w-full text-center">Reports</span>
            </a>
            
            <a href="{{ route('settings.edit') }}" class="flex flex-col items-center justify-center py-2 flex-1 rounded-xl {{ request()->routeIs('settings.*') ? 'text-blue-600' : 'text-gray-500 hover:text-gray-900' }}">
                <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                <span class="text-[10px] font-semibold whitespace-nowrap truncate w-full text-center">Settings</span>
            </a>
            @endif
            
            <a href="{{ route('profile.edit') }}" class="flex flex-col items-center justify-center py-2 flex-1 rounded-xl {{ request()->routeIs('profile.*') ? 'text-blue-600' : 'text-gray-500 hover:text-gray-900' }}">
                <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                <span class="text-[10px] font-semibold whitespace-nowrap truncate w-full text-center">Profile</span>
            </a>
        </div>
    </div>
    @endauth
</body>
</html>
