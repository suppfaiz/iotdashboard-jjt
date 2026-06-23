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
    <div class="border-b border-gray-200 mb-8">
        <nav class="-mb-px flex space-x-8" aria-label="Tabs">
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
                
                <div class="px-6 py-5 border-b border-gray-100 bg-gray-50/50">
                    <h2 class="text-lg font-bold text-gray-900">Financial Estimation Settings</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Parameters used to calculate electricity expenditure estimates.</p>
                </div>
                
                <div class="p-6">
                    <div class="mb-6">
                        <label for="pln_tariff" class="block text-sm font-semibold text-gray-700 mb-2">PLN Base Tariff (Rp/kWh)</label>
                        <div class="relative rounded-xl shadow-sm max-w-md">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                <span class="text-gray-400 font-bold text-sm">Rp</span>
                            </div>
                            <input type="number" step="0.01" name="pln_tariff" id="pln_tariff" value="{{ old('pln_tariff', $plnTariff->value ?? '1444.70') }}" required class="block w-full rounded-xl bg-white border-gray-300 py-3 pl-11 pr-16 text-gray-900 focus:border-blue-500 focus:ring-blue-500 text-sm font-semibold">
                            <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-4">
                                <span class="text-gray-400 font-bold text-sm">/ kWh</span>
                            </div>
                        </div>
                        <p class="mt-2.5 text-xs text-gray-500 leading-normal">This rate will be used dynamically to multiply all logged active energy data on the main dashboard analytics.</p>
                    </div>
                </div>

                <div class="px-6 py-5 border-t border-b border-gray-100 bg-gray-50/50">
                    <h2 class="text-lg font-bold text-gray-900">MQTT Broker Integration</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Settings for receiving telemetry logs from IoT devices.</p>
                </div>
                
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="mqtt_host" class="block text-sm font-semibold text-gray-700 mb-2">MQTT Host</label>
                            <input type="text" name="mqtt_host" id="mqtt_host" value="{{ old('mqtt_host', $mqttHost->value ?? 'broker.emqx.io') }}" required class="block w-full rounded-xl bg-white border-gray-300 py-3 px-4 text-gray-900 focus:border-blue-500 focus:ring-blue-500 text-sm font-semibold">
                        </div>
                        
                        <div>
                            <label for="mqtt_port" class="block text-sm font-semibold text-gray-700 mb-2">MQTT Port</label>
                            <input type="number" name="mqtt_port" id="mqtt_port" value="{{ old('mqtt_port', $mqttPort->value ?? '1883') }}" required class="block w-full rounded-xl bg-white border-gray-300 py-3 px-4 text-gray-900 focus:border-blue-500 focus:ring-blue-500 text-sm font-semibold">
                        </div>
                    </div>
                    <p class="mt-4 text-xs text-gray-500 leading-normal">Warning: Changing the broker host/port will require updating the client configurations on the microcontroller assets and restarting the MQTT daemon worker.</p>
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
                                    <th scope="col" class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Email Address</th>
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
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
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
