<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Jamkrida Energy</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        html, body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            overflow: hidden;
            height: 100dvh;
            width: 100vw;
            margin: 0;
            padding: 0;
            position: fixed;
            inset: 0;
        }

        /* Preloader Styles */
        #preloader {
            position: fixed;
            inset: 0;
            background: #ffffff;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            opacity: 1;
            transition: opacity 0.8s cubic-bezier(0.16, 1, 0.3, 1), transform 0.8s cubic-bezier(0.16, 1, 0.3, 1);
        }

        #preloader.fade-out {
            opacity: 0;
            transform: scale(1.08);
            pointer-events: none;
        }

        .preloader-logo-container {
            position: relative;
            animation: pulse-logo 2s infinite ease-in-out;
        }

        @keyframes pulse-logo {
            0%, 100% {
                transform: scale(1);
                filter: drop-shadow(0 0 15px rgba(59, 130, 246, 0.2));
            }
            50% {
                transform: scale(1.05);
                filter: drop-shadow(0 0 25px rgba(16, 185, 129, 0.4));
            }
        }

        /* Floating background elements */
        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            opacity: 0.45;
            z-index: 0;
            animation: float 25s infinite ease-in-out;
        }
        .orb-1 {
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, #3b82f6 0%, #60a5fa 100%);
            top: -10%;
            left: -10%;
            animation-delay: 0s;
        }
        .orb-2 {
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, #10b981 0%, #34d399 100%);
            bottom: -20%;
            right: -10%;
            animation-delay: -5s;
        }
        .orb-3 {
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, #06b6d4 0%, #22d3ee 100%);
            bottom: 30%;
            left: 20%;
            animation-delay: -10s;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0) scale(1) rotate(0deg);
            }
            50% {
                transform: translateY(-40px) scale(1.15) rotate(180deg);
            }
        }

        /* Login Card Animation */
        .login-card {
            animation: fadeInScale 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            opacity: 0;
        }

        @keyframes fadeInScale {
            0% {
                opacity: 0;
                transform: scale(0.92) translateY(20px);
            }
            100% {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        /* Form elements sequence animations */
        .animate-item {
            opacity: 0;
            transform: translateY(15px);
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        .animate-delay-1 { animation-delay: 0.1s; }
        .animate-delay-2 { animation-delay: 0.2s; }
        .animate-delay-3 { animation-delay: 0.3s; }
        .animate-delay-4 { animation-delay: 0.4s; }
        .animate-delay-5 { animation-delay: 0.5s; }

        @keyframes slideUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Fullscreen exit transition screen */
        #transition-overlay {
            position: fixed;
            inset: 0;
            background: #ffffff;
            z-index: 100;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        }
        #transition-overlay.active {
            opacity: 1;
            pointer-events: auto;
        }

        .spinner-ring {
            width: 64px;
            height: 64px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3b82f6;
            border-bottom: 4px solid #10b981;
            border-radius: 50%;
            animation: spin 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="relative flex items-center justify-center h-full p-4 select-none">

    <!-- Preloader -->
    <div id="preloader">
        <div class="preloader-logo-container flex flex-col items-center">
            <img src="{{ asset('logo.png') }}" alt="Jamkrida Energy Logo" class="h-28 w-auto object-contain mb-6">
            <h1 class="text-xl font-bold text-gray-800 tracking-wider">JAMKRIDA ENERGY</h1>
            <p class="text-xs text-gray-400 font-semibold tracking-widest mt-1 uppercase">Smart Grid Management Portal</p>
        </div>
        
        <!-- Progress Indicator -->
        <div class="mt-10 flex flex-col items-center">
            <div class="w-56 h-1 bg-gray-100 rounded-full overflow-hidden relative shadow-inner">
                <div id="preloader-progress" class="h-full bg-gradient-to-r from-blue-600 via-cyan-500 to-teal-500 w-0 transition-all duration-100 ease-out"></div>
            </div>
            <div class="flex items-center gap-1.5 mt-3 text-xs font-semibold text-gray-500">
                <span id="preloader-status">Initializing security protocols</span>
                <span class="text-blue-600 font-bold" id="preloader-percentage">0%</span>
            </div>
        </div>
    </div>

    <!-- Ambient Orbs -->
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="orb orb-3"></div>

    <!-- Main Container -->
    <div class="relative z-10 w-full max-w-md login-card">
        <div class="bg-white/80 backdrop-blur-md rounded-3xl border border-white/20 shadow-2xl p-8 sm:p-10 flex flex-col items-center">
            
            <!-- Branding Header -->
            <div class="flex flex-col items-center mb-8 animate-item">
                <img src="{{ asset('logo.png') }}" alt="Jamkrida Energy Logo" class="h-20 w-auto object-contain drop-shadow-sm mb-3">
                <p class="text-sm font-semibold tracking-wide text-gray-500 uppercase">IoT Smart Grid Portal</p>
            </div>

            <!-- Validation/Session Alerts -->
            @if(session('status'))
                <div class="w-full mb-4 bg-blue-50 border-l-4 border-blue-500 p-3 rounded text-xs text-blue-700 font-medium animate-item">
                    {{ session('status') }}
                </div>
            @endif

            <!-- Form -->
            <form id="login-form" method="POST" action="{{ route('login') }}" class="w-full space-y-5">
                @csrf

                <!-- Email Input Group -->
                <div class="animate-item animate-delay-1">
                    <label for="email" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5 pl-1">Email Address</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" /></svg>
                        </span>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="name@jamkrida.com"
                            class="w-full rounded-xl bg-gray-50 border border-gray-200 py-3 pl-10 pr-4 text-sm text-gray-900 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:bg-white transition-all shadow-sm">
                    </div>
                    @error('email')
                        <p class="mt-1.5 text-xs text-red-600 pl-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password Input Group -->
                <div class="animate-item animate-delay-2">
                    <label for="password" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5 pl-1">Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                        </span>
                        <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="••••••••"
                            class="w-full rounded-xl bg-gray-50 border border-gray-200 py-3 pl-10 pr-4 text-sm text-gray-900 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:bg-white transition-all shadow-sm">
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs text-red-600 pl-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Options Row -->
                <div class="flex items-center justify-between text-xs animate-item animate-delay-3 pl-1">
                    <label for="remember_me" class="inline-flex items-center text-gray-500 hover:text-gray-900 cursor-pointer">
                        <input id="remember_me" type="checkbox" name="remember" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 w-4 h-4">
                        <span class="ms-2 font-medium">Keep me signed in</span>
                    </label>

                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="font-semibold text-blue-600 hover:text-blue-700 transition-colors">
                            Forgot Password?
                        </a>
                    @endif
                </div>

                <!-- Submit Button -->
                <div class="animate-item animate-delay-4 pt-2">
                    <button type="submit" id="submit-btn" class="w-full bg-gradient-to-r from-blue-600 to-teal-600 hover:from-blue-500 hover:to-teal-500 text-white py-3 rounded-xl font-bold shadow-md hover:shadow-lg transition-all focus:outline-none flex items-center justify-center gap-2">
                        <span>Sign In</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" /></svg>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Transition Loader Overlay -->
    <div id="transition-overlay">
        <div class="spinner-ring mb-6"></div>
        <div id="transition-status" class="text-lg font-bold text-gray-800 tracking-tight transition-all duration-300">Authenticating...</div>
        <div class="text-xs text-gray-400 mt-2 font-medium">Jamkrida Energy Gateway</div>
    </div>

    <script>
        // Preloader script
        window.addEventListener('DOMContentLoaded', () => {
            const progress = document.getElementById('preloader-progress');
            const percent = document.getElementById('preloader-percentage');
            const status = document.getElementById('preloader-status');
            const preloader = document.getElementById('preloader');

            const statusStages = [
                { limit: 25, text: 'Connecting to main grid...' },
                { limit: 55, text: 'Synchronizing MQTT broker...' },
                { limit: 80, text: 'Securing administrative shell...' },
                { limit: 100, text: 'Ready!' }
            ];

            let count = 0;
            const interval = setInterval(() => {
                count += Math.floor(Math.random() * 8) + 4; // increment random step
                if (count >= 100) {
                    count = 100;
                    clearInterval(interval);
                    
                    status.innerText = 'Ready!';
                    percent.innerText = '100%';
                    progress.style.width = '100%';

                    setTimeout(() => {
                        preloader.classList.add('fade-out');
                        setTimeout(() => {
                            preloader.remove();
                        }, 800);
                    }, 400);
                } else {
                    progress.style.width = count + '%';
                    percent.innerText = count + '%';
                    
                    const stage = statusStages.find(s => count <= s.limit);
                    if (stage) {
                        status.innerText = stage.text;
                    }
                }
            }, 35);
        });

        // Form submit transition script
        document.getElementById('login-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const overlay = document.getElementById('transition-overlay');
            const statusText = document.getElementById('transition-status');
            const form = this;

            overlay.classList.add('active');

            setTimeout(() => {
                statusText.innerText = 'Verifying credentials...';
            }, 600);

            setTimeout(() => {
                statusText.innerText = 'Loading operational nodes...';
            }, 1200);

            setTimeout(() => {
                statusText.innerText = 'Welcome to Jamkrida Energy!';
            }, 1800);

            setTimeout(() => {
                form.submit();
            }, 2300);
        });
    </script>
</body>
</html>
