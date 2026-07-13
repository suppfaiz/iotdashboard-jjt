<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Jamkrida IoT</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Orbitron:wght@400;600;800;900&display=swap" rel="stylesheet">
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

        /* Floating background elements - Optimized to prevent GPU switching on dual-GPU Macbooks */
        .orb {
            position: absolute;
            border-radius: 50%;
            opacity: 0.45;
            z-index: 0;
            animation: float 25s infinite ease-in-out;
        }
        .orb-1 {
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.45) 0%, rgba(59, 130, 246, 0) 70%);
            top: -10%;
            left: -10%;
            animation-delay: 0s;
        }
        .orb-2 {
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.45) 0%, rgba(16, 185, 129, 0) 70%);
            bottom: -20%;
            right: -10%;
            animation-delay: -5s;
        }
        .orb-3 {
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(6, 182, 212, 0.4) 0%, rgba(6, 182, 212, 0) 70%);
            bottom: 30%;
            left: 20%;
            animation-delay: -10s;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-30px);
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

        /* --- 3D Light Door Pre-loader Styling --- */
        #door-preloader {
            position: fixed;
            inset: 0;
            z-index: 10000;
            display: flex;
            perspective: 1600px;
            overflow: hidden;
            background: #f1f5f9; /* slate-100 background behind the doors */
            transition: opacity 0.8s ease;
        }

        .door {
            position: relative;
            width: 50%;
            height: 100%;
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            box-shadow: inset 0 0 80px rgba(148, 163, 184, 0.3);
            display: flex;
            align-items: center;
            transition: transform 1.8s cubic-bezier(0.7, 0, 0.3, 1);
            z-index: 10001;
            overflow: hidden;
        }

        .door-left {
            transform-origin: left center;
            border-right: 4px solid #3b82f6; /* glowing blue seam */
            justify-content: flex-end;
            padding-right: 40px;
        }

        .door-right {
            transform-origin: right center;
            border-left: 4px solid #3b82f6;
            justify-content: flex-start;
            padding-left: 40px;
        }

        /* Door decorative panels to look like a house door */
        .door-panel-container {
            display: flex;
            flex-direction: column;
            gap: 20px;
            width: 55%;
            height: 75%;
            justify-content: space-around;
        }

        .door-panel {
            flex: 1;
            border: 3px solid rgba(59, 130, 246, 0.1);
            background: rgba(255, 255, 255, 0.6);
            border-radius: 8px;
            box-shadow: inset 0 0 20px rgba(148, 163, 184, 0.2);
        }

        /* Silver/Chrome Door handles */
        .door-handle-left {
            width: 10px;
            height: 90px;
            background: linear-gradient(to right, #e2e8f0, #94a3b8);
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(148, 163, 184, 0.3), inset 0 0 3px rgba(0,0,0,0.15);
            margin-right: -45px;
            z-index: 10002;
        }

        .door-handle-right {
            width: 10px;
            height: 90px;
            background: linear-gradient(to left, #e2e8f0, #94a3b8);
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(148, 163, 184, 0.3), inset 0 0 3px rgba(0,0,0,0.15);
            margin-left: -45px;
            z-index: 10002;
        }

        /* Glowing center badge */
        .loader-center {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 10003;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transition: all 0.8s cubic-bezier(0.7, 0, 0.3, 1);
        }

        .loader-circle {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: #ffffff;
            border: 4px solid #3b82f6;
            box-shadow: 0 0 25px rgba(59, 130, 246, 0.25);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            animation: pulse-glow 2s infinite ease-in-out;
        }

        @keyframes pulse-glow {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 0 15px rgba(59, 130, 246, 0.2);
            }
            50% {
                transform: scale(1.06);
                box-shadow: 0 0 30px rgba(59, 130, 246, 0.5);
            }
        }
    </style>
</head>
<body class="relative flex items-center justify-center h-full p-4 select-none">

    <!-- 3D Light Door Pre-loader & Transition Overlay -->
    <div id="door-preloader">
        <!-- Left Door -->
        <div class="door door-left">
            <div class="door-panel-container">
                <div class="door-panel"></div>
                <div class="door-panel"></div>
            </div>
            <div class="door-handle-left"></div>
        </div>

        <!-- Center Glowing Icon -->
        <div id="loader-center" class="loader-center">
            <div class="loader-circle">⚡</div>
            <div id="loader-text" class="text-center mt-6 transition-all duration-700">
                <h2 id="transition-status" class="text-lg font-black tracking-widest text-slate-800 uppercase" style="font-family: 'Orbitron', sans-serif;">Loading System</h2>
                <p id="transition-sub" class="text-[9px] text-slate-400 font-bold tracking-widest uppercase mt-2">Opening Jamkrida IoT...</p>
            </div>
        </div>

        <!-- Right Door -->
        <div class="door door-right">
            <div class="door-handle-right"></div>
            <div class="door-panel-container">
                <div class="door-panel"></div>
                <div class="door-panel"></div>
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
                <img src="{{ asset('logo.png') }}" alt="Jamkrida IoT Logo" class="h-20 w-auto object-contain drop-shadow-sm mb-3">
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
                            class="w-full rounded-xl bg-gray-50 border border-gray-200 py-3 pl-10 pr-10 text-sm text-gray-900 focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 focus:bg-white transition-all shadow-sm">
                        <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-gray-400 hover:text-gray-600 transition-colors focus:outline-none">
                            <svg id="eye-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path class="eye-open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path class="eye-open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                <path class="eye-closed hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                            </svg>
                        </button>
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

    <script>
        // Preloader script: Swing doors open at startup
        window.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                const preloader = document.getElementById('door-preloader');
                const leftDoor = document.querySelector('#door-preloader .door-left');
                const rightDoor = document.querySelector('#door-preloader .door-right');
                const loaderCenter = document.getElementById('loader-center');

                if (loaderCenter) {
                    loaderCenter.style.opacity = '0';
                    loaderCenter.style.transform = 'translate(-50%, -50%) scale(0.6)';
                }
                
                if (leftDoor) leftDoor.style.transform = 'rotateY(-90deg)';
                if (rightDoor) rightDoor.style.transform = 'rotateY(90deg)';

                setTimeout(() => {
                    if (preloader) {
                        preloader.style.opacity = '0';
                        preloader.style.pointerEvents = 'none';
                    }
                }, 2200);
            }, 600);
        });

        // Form submit transition script: Swing doors closed on submit
        document.getElementById('login-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const preloader = document.getElementById('door-preloader');
            const leftDoor = document.querySelector('#door-preloader .door-left');
            const rightDoor = document.querySelector('#door-preloader .door-right');
            const loaderCenter = document.getElementById('loader-center');
            const statusText = document.getElementById('transition-status');
            const statusSub = document.getElementById('transition-sub');
            const form = this;

            // Display preloader overlay
            if (preloader) {
                preloader.style.display = 'flex';
                preloader.style.opacity = '1';
                preloader.style.pointerEvents = 'auto';
            }

            // Swing doors closed
            setTimeout(() => {
                if (leftDoor) leftDoor.style.transform = 'rotateY(0deg)';
                if (rightDoor) rightDoor.style.transform = 'rotateY(0deg)';
                
                if (loaderCenter) {
                    loaderCenter.style.opacity = '1';
                    loaderCenter.style.transform = 'translate(-50%, -50%) scale(1)';
                }

                if (statusText) statusText.innerText = 'AUTHENTICATING...';
                if (statusSub) statusSub.innerText = 'Verifying credentials...';
            }, 50);

            setTimeout(() => {
                if (statusSub) statusSub.innerText = 'Loading operational nodes...';
            }, 1000);

            setTimeout(() => {
                if (statusSub) statusSub.innerText = 'Welcome to Jamkrida IoT!';
            }, 1800);

            setTimeout(() => {
                // Clear layout session storage since we are logging in fresh!
                sessionStorage.removeItem('has_seen_preloader');
                form.submit();
            }, 2400);
        });

        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const eyeOpenPaths = document.querySelectorAll('#eye-icon .eye-open');
            const eyeClosedPaths = document.querySelectorAll('#eye-icon .eye-closed');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                eyeOpenPaths.forEach(p => p.classList.add('hidden'));
                eyeClosedPaths.forEach(p => p.classList.remove('hidden'));
            } else {
                passwordInput.type = 'password';
                eyeOpenPaths.forEach(p => p.classList.remove('hidden'));
                eyeClosedPaths.forEach(p => p.classList.add('hidden'));
            }
        }
    </script>
</body>
</html>
