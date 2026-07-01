@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Settings & Control Panel</h1>
        <p class="text-gray-500">Configure global configurations and manage platform operator access.</p>
    </div>

    <!-- Alert Notifications -->
    @if(session('success'))
        <div class="mb-6 bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-xl shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-emerald-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-semibold text-emerald-800">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 bg-rose-50 border-l-4 border-rose-500 p-4 rounded-xl shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-rose-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-11a1 1 0 112 0v4a1 1 0 11-2 0V7zm1 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-semibold text-rose-800">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 bg-rose-50 border-l-4 border-rose-500 p-4 rounded-xl shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-rose-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-11a1 1 0 112 0v4a1 1 0 11-2 0V7zm1 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-bold text-rose-800">Please correct the errors:</h3>
                    <ul class="mt-1 list-disc list-inside text-sm text-rose-700">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    @php
        $activeTab = request()->query('tab', 'config');
    @endphp

    <!-- Tabs Header -->
    <div class="border-b border-gray-200 mb-8 overflow-x-auto scrollbar-none">
        <nav class="-mb-px flex space-x-8 min-w-max" aria-label="Tabs">
            <button onclick="switchTab('config')" id="tab-btn-config" 
                class="whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm transition-all focus:outline-none 
                {{ $activeTab === 'config' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                System Configurations
            </button>
            <button onclick="switchTab('users')" id="tab-btn-users" 
                class="whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm transition-all focus:outline-none 
                {{ $activeTab === 'users' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                User Management
            </button>
        </nav>
    </div>

    <!-- Tab 1: System Configurations -->
    <div id="tab-content-config" class="{{ $activeTab === 'config' ? '' : 'hidden' }}">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <form action="{{ route('settings.update') }}" method="POST">
                @csrf
                @method('PUT')
                
                <!-- Section 1: Financial & Time of Use (ToU) Billing -->
                <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <h2 class="text-lg font-bold text-gray-900">Financial & Time of Use (ToU) Billing</h2>
                    </div>
                    <p class="text-xs text-gray-500 mt-0.5">Parameters used to calculate electricity billing estimates and peak tariff structures.</p>
                </div>
                
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="pln_tariff" class="block text-sm font-semibold text-gray-700 mb-2">PLN Base Tariff (Rp/kWh)</label>
                            <div class="relative rounded-xl shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                    <span class="text-gray-400 font-bold text-sm">Rp</span>
                                </div>
                                <input type="number" step="0.01" name="pln_tariff" id="pln_tariff" value="{{ old('pln_tariff', $configs['pln_tariff'] ?? '1444.70') }}" required class="block w-full rounded-xl bg-white border-gray-300 py-3 pl-11 pr-16 text-gray-900 focus:border-blue-500 focus:ring-blue-500 text-sm font-semibold">
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4">
                                    <span class="text-gray-400 font-bold text-sm">/ kWh</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="pln_tariff_wbp" class="block text-sm font-semibold text-gray-700 mb-2">Peak Tariff / WBP (Rp/kWh)</label>
                            <div class="relative rounded-xl shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                    <span class="text-gray-400 font-bold text-sm text-amber-600">Rp</span>
                                </div>
                                <input type="number" step="0.01" name="pln_tariff_wbp" id="pln_tariff_wbp" value="{{ old('pln_tariff_wbp', $configs['pln_tariff_wbp'] ?? '2000.00') }}" required class="block w-full rounded-xl bg-white border-gray-300 py-3 pl-11 pr-16 text-gray-900 focus:border-blue-500 focus:ring-blue-500 text-sm font-semibold">
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4">
                                    <span class="text-gray-400 font-bold text-sm">/ kWh</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label for="pln_tariff_lwbp" class="block text-sm font-semibold text-gray-700 mb-2">Off-Peak Tariff / LWBP (Rp/kWh)</label>
                            <div class="relative rounded-xl shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                    <span class="text-gray-400 font-bold text-sm text-emerald-600">Rp</span>
                                </div>
                                <input type="number" step="0.01" name="pln_tariff_lwbp" id="pln_tariff_lwbp" value="{{ old('pln_tariff_lwbp', $configs['pln_tariff_lwbp'] ?? '1444.70') }}" required class="block w-full rounded-xl bg-white border-gray-300 py-3 pl-11 pr-16 text-gray-900 focus:border-blue-500 focus:ring-blue-500 text-sm font-semibold">
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4">
                                    <span class="text-gray-400 font-bold text-sm">/ kWh</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="wbp_start" class="block text-sm font-semibold text-gray-700 mb-2">WBP Start Time (HH:MM)</label>
                            <input type="text" name="wbp_start" id="wbp_start" placeholder="17:00" value="{{ old('wbp_start', $configs['wbp_start'] ?? '17:00') }}" required class="block w-full rounded-xl bg-white border-gray-300 py-3 px-4 text-gray-900 focus:border-blue-500 focus:ring-blue-500 text-sm font-semibold">
                            <p class="mt-1.5 text-xs text-gray-500">Hour when Peak pricing begins (e.g. 17:00).</p>
                        </div>
                        
                        <div>
                            <label for="wbp_end" class="block text-sm font-semibold text-gray-700 mb-2">WBP End Time (HH:MM)</label>
                            <input type="text" name="wbp_end" id="wbp_end" placeholder="22:00" value="{{ old('wbp_end', $configs['wbp_end'] ?? '22:00') }}" required class="block w-full rounded-xl bg-white border-gray-300 py-3 px-4 text-gray-900 focus:border-blue-500 focus:ring-blue-500 text-sm font-semibold">
                            <p class="mt-1.5 text-xs text-gray-500">Hour when Peak pricing ends (e.g. 22:00).</p>
                        </div>
                    </div>
                </div>

                <!-- Section 2: MQTT Broker Integration -->
                <div class="px-6 py-5 border-t border-b border-gray-100 bg-gray-50/50">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                        </svg>
                        <h2 class="text-lg font-bold text-gray-900">MQTT Broker Integration</h2>
                    </div>
                    <p class="text-xs text-gray-500 mt-0.5">Settings for receiving telemetry logs and managing connection with IoT devices.</p>
                </div>
                
                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="mqtt_host" class="block text-sm font-semibold text-gray-700 mb-2">MQTT Host</label>
                            <input type="text" name="mqtt_host" id="mqtt_host" value="{{ old('mqtt_host', $configs['mqtt_host'] ?? 'broker.emqx.io') }}" required class="block w-full rounded-xl bg-white border-gray-300 py-3 px-4 text-gray-900 focus:border-blue-500 focus:ring-blue-500 text-sm font-semibold">
                        </div>
                        
                        <div>
                            <label for="mqtt_port" class="block text-sm font-semibold text-gray-700 mb-2">MQTT Port</label>
                            <input type="number" name="mqtt_port" id="mqtt_port" value="{{ old('mqtt_port', $configs['mqtt_port'] ?? '1883') }}" required class="block w-full rounded-xl bg-white border-gray-300 py-3 px-4 text-gray-900 focus:border-blue-500 focus:ring-blue-500 text-sm font-semibold">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="mqtt_user" class="block text-sm font-semibold text-gray-700 mb-2">MQTT Username (Optional)</label>
                            <input type="text" name="mqtt_user" id="mqtt_user" value="{{ old('mqtt_user', $configs['mqtt_user'] ?? '') }}" class="block w-full rounded-xl bg-white border-gray-300 py-3 px-4 text-gray-900 focus:border-blue-500 focus:ring-blue-500 text-sm font-semibold">
                        </div>
                        
                        <div>
                            <label for="mqtt_password" class="block text-sm font-semibold text-gray-700 mb-2">MQTT Password (Optional)</label>
                            <input type="password" name="mqtt_password" id="mqtt_password" value="{{ old('mqtt_password', $configs['mqtt_password'] ?? '') }}" class="block w-full rounded-xl bg-white border-gray-300 py-3 px-4 text-gray-900 focus:border-blue-500 focus:ring-blue-500 text-sm font-semibold">
                        </div>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="mqtt_use_tls" id="mqtt_use_tls" value="1" {{ (old('mqtt_use_tls', $configs['mqtt_use_tls'] ?? '0') === '1') ? 'checked' : '' }} class="h-4 w-4 rounded border-gray-300 text-blue-650 focus:ring-blue-500">
                        <label for="mqtt_use_tls" class="ml-2.5 text-sm font-bold text-gray-800">
                            Enable Secure SSL/TLS Connection (Required for HiveMQ Cloud)
                        </label>
                    </div>

                    <p class="text-xs text-gray-500 leading-normal">Warning: Changing the broker host/port or TLS configuration will require updating the client configurations on the microcontroller assets and restarting the MQTT daemon worker.</p>
                </div>

                <!-- Section 3: Telegram Bot Notification & Alert Thresholds -->
                <div class="px-6 py-5 border-t border-b border-gray-100 bg-gray-50/50">
                    <div class="flex items-center space-x-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <h2 class="text-lg font-bold text-gray-900">Telegram Notifications & Telemetry Alert Thresholds</h2>
                    </div>
                    <p class="text-xs text-gray-500 mt-0.5">Parameters for anomaly alerts and notification integration via Telegram.</p>
                </div>

                <div class="p-6 space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="telegram_bot_token" class="block text-sm font-semibold text-gray-700 mb-2">Telegram Bot Token</label>
                            <input type="text" name="telegram_bot_token" id="telegram_bot_token" placeholder="e.g. 123456789:ABCdefGhIJKlmNoPQRsTUVwxyZ" value="{{ old('telegram_bot_token', $configs['telegram_bot_token'] ?? '') }}" class="block w-full rounded-xl bg-white border-gray-300 py-3 px-4 text-gray-900 focus:border-blue-500 focus:ring-blue-500 text-sm font-semibold">
                            <p class="mt-1.5 text-xs text-gray-500">Provide bot token generated by @BotFather.</p>
                        </div>
                        
                        <div>
                            <label for="telegram_chat_id" class="block text-sm font-semibold text-gray-700 mb-2">Telegram Chat ID</label>
                            <input type="text" name="telegram_chat_id" id="telegram_chat_id" placeholder="e.g. -100123456789" value="{{ old('telegram_chat_id', $configs['telegram_chat_id'] ?? '') }}" class="block w-full rounded-xl bg-white border-gray-300 py-3 px-4 text-gray-900 focus:border-blue-500 focus:ring-blue-500 text-sm font-semibold">
                            <p class="mt-1.5 text-xs text-gray-500">Provide target chat ID (admin group or direct user ID).</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="alert_voltage_min" class="block text-sm font-semibold text-gray-700 mb-2">Min Voltage Limit (Volt)</label>
                            <div class="relative rounded-xl shadow-sm">
                                <input type="number" step="0.01" name="alert_voltage_min" id="alert_voltage_min" value="{{ old('alert_voltage_min', $configs['alert_voltage_min'] ?? '200.00') }}" required class="block w-full rounded-xl bg-white border-gray-300 py-3 px-4 text-gray-900 focus:border-blue-500 focus:ring-blue-500 text-sm font-semibold">
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4">
                                    <span class="text-gray-400 font-bold text-sm">V</span>
                                </div>
                            </div>
                            <p class="mt-1.5 text-xs text-gray-500">Minimum voltage threshold to trigger alert.</p>
                        </div>
                        
                        <div>
                            <label for="alert_voltage_max" class="block text-sm font-semibold text-gray-700 mb-2">Max Voltage Limit (Volt)</label>
                            <div class="relative rounded-xl shadow-sm">
                                <input type="number" step="0.01" name="alert_voltage_max" id="alert_voltage_max" value="{{ old('alert_voltage_max', $configs['alert_voltage_max'] ?? '240.00') }}" required class="block w-full rounded-xl bg-white border-gray-300 py-3 px-4 text-gray-900 focus:border-blue-500 focus:ring-blue-500 text-sm font-semibold">
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4">
                                    <span class="text-gray-400 font-bold text-sm">V</span>
                                </div>
                            </div>
                            <p class="mt-1.5 text-xs text-gray-500">Maximum voltage threshold to trigger alert.</p>
                        </div>

                        <div>
                            <label for="alert_power_max" class="block text-sm font-semibold text-gray-700 mb-2">Max Power Limit (Watt)</label>
                            <div class="relative rounded-xl shadow-sm">
                                <input type="number" step="0.01" name="alert_power_max" id="alert_power_max" value="{{ old('alert_power_max', $configs['alert_power_max'] ?? '2200.00') }}" required class="block w-full rounded-xl bg-white border-gray-300 py-3 px-4 text-gray-900 focus:border-blue-500 focus:ring-blue-500 text-sm font-semibold">
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4">
                                    <span class="text-gray-400 font-bold text-sm">W</span>
                                </div>
                            </div>
                            <p class="mt-1.5 text-xs text-gray-500">Maximum power consumption threshold to trigger alert.</p>
                        </div>
                    </div>

                    <!-- Section 4: Electrician WhatsApp (Hubungi Tukang Listrik) -->
                    <div class="border-t border-gray-100 pt-6">
                        <div class="flex items-center space-x-2 mb-4">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.94.725l.548 2.2a1 1 0 01-.321.988l-1.305.98a10.582 10.582 0 004.872 4.872l.98-1.305a1 1 0 01.988-.321l2.2.548a1 1 0 01.725.94V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                            </svg>
                            <h2 class="text-base font-bold text-gray-900">Electrician Contact Settings</h2>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="electrician_whatsapp" class="block text-sm font-semibold text-gray-700 mb-2">WhatsApp Number (Electrician)</label>
                                <input type="text" name="electrician_whatsapp" id="electrician_whatsapp" placeholder="e.g. 628123456789 (Use country code, no +)" value="{{ old('electrician_whatsapp', $configs['electrician_whatsapp'] ?? '') }}" class="block w-full rounded-xl bg-white border-gray-300 py-3 px-4 text-gray-900 focus:border-blue-500 focus:ring-blue-500 text-sm font-semibold">
                                <p class="mt-1.5 text-xs text-gray-500">Provide the electrician's WhatsApp phone number with country code (e.g. 628123456789) to allow quick messaging during alerts.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Section 5: AI Chatbot (Google Gemini API Key) -->
                    <div class="border-t border-gray-100 pt-6 mt-6">
                        <div class="flex items-center space-x-2 mb-4">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.663 17h4.673M12 3v1m6.364 .364l-.707 .707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                            </svg>
                            <h2 class="text-base font-bold text-gray-900">AI Chatbot Integration Settings</h2>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="gemini_api_key" class="block text-sm font-semibold text-gray-700 mb-2">Google Gemini API Key (Optional)</label>
                                <input type="password" name="gemini_api_key" id="gemini_api_key" placeholder="e.g. AIzaSy..." value="{{ old('gemini_api_key', $configs['gemini_api_key'] ?? '') }}" class="block w-full rounded-xl bg-white border-gray-300 py-3 px-4 text-gray-900 focus:border-blue-500 focus:ring-blue-500 text-sm font-semibold">
                                <p class="mt-1.5 text-xs text-gray-500">Provide Google Gemini API Key from Google AI Studio (Free Tier available) to power the chatbot with interactive AI. Leave empty to fallback to standard template analysis reports.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-4 border-t border-gray-200 px-6 pb-6 bg-gray-50">
                    <button type="submit" class="inline-flex justify-center rounded-xl border border-transparent bg-blue-600 py-2.5 px-5 text-sm font-bold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                        Save Configurations
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tab 2: User Management -->
    <div id="tab-content-users" class="{{ $activeTab === 'users' ? '' : 'hidden' }}">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            
            <!-- User List Table (lg:col-span-2) -->
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden flex flex-col justify-between">
                <div>
                    <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
                        <h2 class="text-lg font-bold text-gray-900">Platform Users</h2>
                        <p class="text-xs text-gray-500 mt-0.5">List of active system operators and their authority settings.</p>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50/50">
                                <tr>
                                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Operator</th>
                                    <th scope="col" class="hidden sm:table-cell px-6 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Email Address</th>
                                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Access Role</th>
                                    <th scope="col" class="relative px-6 py-3.5 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($users as $user)
                                    <tr class="hover:bg-gray-50/50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center space-x-3">
                                                <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm uppercase">
                                                    {{ substr($user->name, 0, 2) }}
                                                </div>
                                                <span class="text-sm font-semibold text-gray-900">{{ $user->name }}</span>
                                            </div>
                                        </td>
                                        <td class="hidden sm:table-cell px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $user->email }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase tracking-wider
                                                {{ $user->role === 'admin' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                                {{ $user->role }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            @if($user->id !== auth()->id())
                                                <form action="{{ route('settings.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete operator {{ $user->name }}? This action cannot be undone.')" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-rose-600 hover:text-rose-900 font-bold transition-colors">
                                                        Delete
                                                    </button>
                                                </form>
                                            @else
                                                <span class="text-xs text-gray-400 italic font-medium">Active Account</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-12 text-center text-gray-400 text-sm">
                                            No system operators found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Create New User Form (lg:col-span-1) -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
                    <h2 class="text-lg font-bold text-gray-900">Create Operator Account</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Register a new profile to grant platform access.</p>
                </div>
                
                <form action="{{ route('settings.users.store') }}" method="POST" class="p-6 space-y-5">
                    @csrf
                    
                    <div>
                        <label for="name" class="block text-sm font-semibold text-gray-700 mb-1.5">Full Name</label>
                        <input type="text" name="name" id="name" required value="{{ old('name') }}" class="block w-full rounded-xl bg-white border-gray-300 py-2.5 px-3.5 text-gray-900 focus:border-blue-500 focus:ring-blue-500 text-sm font-semibold">
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5">Email Address</label>
                        <input type="email" name="email" id="email" required value="{{ old('email') }}" class="block w-full rounded-xl bg-white border-gray-300 py-2.5 px-3.5 text-gray-900 focus:border-blue-500 focus:ring-blue-500 text-sm font-semibold">
                    </div>

                    <div>
                        <label for="role" class="block text-sm font-semibold text-gray-700 mb-1.5">Access Role</label>
                        <select name="role" id="role" required class="block w-full rounded-xl bg-white border-gray-300 py-2.5 px-3.5 text-gray-900 focus:border-blue-500 focus:ring-blue-500 text-sm font-semibold">
                            <option value="user" {{ old('role') === 'user' ? 'selected' : '' }}>Standard User (View Only)</option>
                            <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Administrator (Full Access)</option>
                        </select>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-semibold text-gray-700 mb-1.5">Password</label>
                        <input type="password" name="password" id="password" required class="block w-full rounded-xl bg-white border-gray-300 py-2.5 px-3.5 text-gray-900 focus:border-blue-500 focus:ring-blue-500 text-sm font-semibold">
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-1.5">Confirm Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" required class="block w-full rounded-xl bg-white border-gray-300 py-2.5 px-3.5 text-gray-900 focus:border-blue-500 focus:ring-blue-500 text-sm font-semibold">
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full inline-flex justify-center rounded-xl border border-transparent bg-blue-600 py-2.5 px-4 text-sm font-bold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                            Register Operator
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    window.switchTab = function(tabName) {
        // Toggle tab content visibility
        document.getElementById('tab-content-config').classList.add('hidden');
        document.getElementById('tab-content-users').classList.add('hidden');
        document.getElementById('tab-content-' + tabName).classList.remove('hidden');

        // Toggle button classes
        ['config', 'users'].forEach(t => {
            const btn = document.getElementById('tab-btn-' + t);
            if (t === tabName) {
                btn.className = 'whitespace-nowrap py-4 px-1 border-b-2 border-blue-600 font-bold text-sm text-blue-600 transition-all focus:outline-none';
            } else {
                btn.className = 'whitespace-nowrap py-4 px-1 border-b-2 border-transparent font-bold text-sm text-gray-500 hover:text-gray-700 hover:border-gray-300 transition-all focus:outline-none';
            }
        });

        // Update URL query parameters without reloading the page
        const url = new URL(window.location);
        url.searchParams.set('tab', tabName);
        window.history.pushState({}, '', url);
    }
</script>
@endsection
