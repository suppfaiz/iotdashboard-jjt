@extends('layouts.app')

@section('content')
<div class="mb-8">
    <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Devices Management</h1>
    <p class="mt-2 text-sm text-gray-500 font-medium">Manage and monitor all IoT devices and their real-time telemetry indicators across your operational areas.</p>
</div>

<!-- Devices Table -->
<div class="bg-white/80 backdrop-blur-xl shadow-xl rounded-2xl border border-gray-100 overflow-hidden mb-8">
    <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-gray-50 to-white">
        <h3 class="text-base font-bold leading-6 text-gray-900">Active Devices List</h3>
        @if(auth()->user()->role === 'admin')
            <div class="flex items-center gap-2">
                <button type="button" onclick="document.getElementById('add-group-modal').classList.remove('hidden')" class="relative inline-flex items-center gap-x-1.5 rounded-lg bg-indigo-650 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-600 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-650 transition-colors">
                    <svg class="-ml-0.5 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Add New Group
                </button>
                <button type="button" onclick="document.getElementById('add-device-modal').classList.remove('hidden')" class="relative inline-flex items-center gap-x-1.5 rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 transition-colors">
                    <svg class="-ml-0.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                    </svg>
                    Add New Device
                </button>
            </div>
        @endif
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    @if(auth()->user()->role === 'admin')
                        <th scope="col" class="w-10 px-6 py-3 text-left">
                            <input type="checkbox" id="select-all-devices" onclick="toggleSelectAll(this)" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                        </th>
                    @endif
                    <th scope="col" class="px-6 py-3 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Device & ID</th>
                    <th scope="col" class="hidden sm:table-cell px-6 py-3 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Group Area</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="hidden sm:table-cell px-6 py-3 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">IP Address</th>
                    <th scope="col" class="hidden md:table-cell px-6 py-3 text-right text-xs font-extrabold text-gray-500 uppercase tracking-wider">Indicators (V/A/W)</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-extrabold text-gray-500 uppercase tracking-wider">Total Energy</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-extrabold text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($devices as $device)
                <tr class="hover:bg-gray-50 transition-colors">
                    @if(auth()->user()->role === 'admin')
                        <td class="px-6 py-4 whitespace-nowrap">
                            <input type="checkbox" name="selected_devices[]" value="{{ $device->id }}" 
                                data-name="{{ $device->name }}" 
                                data-id="{{ $device->device_id }}" 
                                data-group="{{ $device->group ? $device->group->name : 'General Area' }}" 
                                data-url="{{ route('devices.show', $device->id) }}" 
                                onchange="updateSelectionBar()" 
                                class="device-checkbox rounded border-gray-300 text-blue-600 focus:ring-blue-500 cursor-pointer">
                        </td>
                    @endif
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-10 w-10 flex items-center justify-center rounded-xl bg-blue-50 text-blue-600 border border-blue-100">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" /></svg>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-bold text-gray-900">{{ $device->name }}</div>
                                <div class="text-xs font-medium text-gray-500">{{ $device->device_id }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="hidden sm:table-cell px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-semibold text-gray-900">{{ $device->group->name }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($metrics[$device->id]['status'] === 'Online')
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                Online
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-rose-100 text-rose-800 border border-rose-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                Offline
                            </span>
                        @endif
                    </td>
                    <td class="hidden sm:table-cell px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center rounded-lg bg-slate-50 px-2.5 py-1 text-xs font-bold text-slate-700 ring-1 ring-inset ring-slate-600/20 font-mono">
                            {{ $metrics[$device->id]['ip'] }}
                        </span>
                    </td>
                    <td class="hidden md:table-cell px-6 py-4 whitespace-nowrap text-right">
                        <div class="text-sm text-gray-900 space-x-2 font-mono">
                            <span class="font-semibold text-blue-600" title="Voltage">{{ number_format($metrics[$device->id]['voltage'], 1) }}V</span>
                            <span class="text-gray-300">|</span>
                            <span class="font-semibold text-amber-500" title="Current">{{ number_format($metrics[$device->id]['current'], 2) }}A</span>
                            <span class="text-gray-300">|</span>
                            <span class="font-semibold text-indigo-600" title="Power">{{ number_format($metrics[$device->id]['power'], 1) }}W</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right">
                        <span class="inline-flex items-center rounded-lg bg-teal-50 px-2.5 py-1 text-xs font-bold text-teal-700 ring-1 ring-inset ring-teal-600/20">
                            {{ number_format($metrics[$device->id]['energy'], 3) }} kWh
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="{{ route('devices.show', $device->id) }}" class="text-blue-600 hover:text-blue-900 font-bold hover:underline decoration-2 underline-offset-4">View Detail</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-sm font-medium text-gray-500">
                        <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        No devices registered yet.<br>
                        @if(auth()->user()->role === 'admin')
                            Click "Add New Device" to provision your first IoT meter.
                        @else
                            Please contact an administrator to register and provision new devices.
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Add Device Modal -->
@if(auth()->user()->role === 'admin')
<div id="add-device-modal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background backdrop -->
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="document.getElementById('add-device-modal').classList.add('hidden')"></div>

        <!-- Spacer to center modal -->
        <span class="hidden sm:inline-block sm:align-middle sm:min-h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal Panel -->
        <div class="inline-block align-middle bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-200/60">
            <form action="{{ route('devices.store') }}" method="POST">
                @csrf
                
                <!-- Modal Header -->
                <div class="px-6 py-5 border-b border-slate-100 flex items-start gap-3.5 bg-slate-50/50">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center border border-blue-100 flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900" id="modal-title">Register New Device</h3>
                        <p class="text-xs text-slate-500 mt-0.5 font-medium">Enter details below to provision a new monitoring node.</p>
                    </div>
                </div>

                <!-- Form Fields -->
                <div class="px-6 py-5 space-y-4">
                    <!-- Device Type -->
                    <div class="relative group">
                        <label for="idx_device_type" class="block text-xs font-semibold text-slate-600 mb-1.5 group-focus-within:text-blue-600 transition-colors">Device Type</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 group-focus-within:text-blue-500 transition-colors">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                </svg>
                            </div>
                            <select name="device_type" id="idx_device_type" required 
                                onchange="toggleIdxGroupField(this.value)"
                                class="w-full pl-9 pr-10 py-2.5 bg-white border border-slate-250 rounded-xl text-slate-900 text-sm font-semibold focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 hover:border-slate-300 transition-all duration-200 appearance-none">
                                <option value="pzem">⚡ Energy Monitor (PZEM-004T)</option>
                                <option value="env_sensor">🌡️ Environment Sensor (DHT22)</option>
                                <option value="relay_controller">🔌 Relay Controller (4CH)</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Device Name -->
                    <div class="relative group">
                        <label for="name" class="block text-xs font-semibold text-slate-600 mb-1.5 group-focus-within:text-blue-600 transition-colors">Device Name</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 group-focus-within:text-blue-500 transition-colors">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z" />
                                </svg>
                            </div>
                            <input type="text" name="name" id="name" required placeholder="e.g. Compressor Pump A" 
                                class="w-full pl-9 pr-4 py-2.5 bg-white border border-slate-250 rounded-xl text-slate-900 text-sm font-medium placeholder-slate-405 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 hover:border-slate-300 transition-all duration-200">
                        </div>
                    </div>

                    <!-- Group Area (only for pzem) -->
                    <div class="relative group" id="idx_group_field">
                        <label for="group_id" class="block text-xs font-semibold text-slate-600 mb-1.5 group-focus-within:text-blue-600 transition-colors">Group Area</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 group-focus-within:text-blue-500 transition-colors">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                </svg>
                            </div>
                            <select name="group_id" id="group_id"
                                class="w-full pl-9 pr-10 py-2.5 bg-white border border-slate-250 rounded-xl text-slate-900 text-sm font-semibold focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 hover:border-slate-300 transition-all duration-200 appearance-none">
                                @foreach($groups as $g)
                                    <option value="{{ $g->id }}">{{ $g->name }}</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4.5 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Wifi SSID -->
                    <div class="relative group">
                        <label for="wifi_ssid" class="block text-xs font-semibold text-slate-600 mb-1.5 group-focus-within:text-blue-600 transition-colors">Wifi SSID</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 group-focus-within:text-blue-500 transition-colors">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071a9.9 9.9 0 0114.14 0M2.006 8.502a15 15 0 0121.988 0" />
                                </svg>
                            </div>
                            <input type="text" name="wifi_ssid" id="wifi_ssid" required placeholder="SSID Wifi Name" 
                                class="w-full pl-9 pr-4 py-2.5 bg-white border border-slate-250 rounded-xl text-slate-900 text-sm font-medium placeholder-slate-405 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 hover:border-slate-300 transition-all duration-200">
                        </div>
                    </div>

                    <!-- Wifi Password -->
                    <div class="relative group">
                        <label for="wifi_password" class="block text-xs font-semibold text-slate-600 mb-1.5 group-focus-within:text-blue-600 transition-colors">Wifi Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 group-focus-within:text-blue-500 transition-colors">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input type="password" name="wifi_password" id="wifi_password" required placeholder="••••••••" 
                                class="w-full pl-9 pr-4 py-2.5 bg-white border border-slate-250 rounded-xl text-slate-900 text-sm font-medium placeholder-slate-405 focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 hover:border-slate-300 transition-all duration-200">
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="bg-slate-50/60 px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                    <button type="button" onclick="document.getElementById('add-device-modal').classList.add('hidden')" 
                        class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-600 hover:bg-slate-50 hover:text-slate-800 transition-colors duration-200">
                        Cancel
                    </button>
                    <button type="submit" 
                        class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 active:bg-blue-800 text-xs font-bold text-white shadow-md shadow-blue-500/10 transition-colors duration-200">
                        Register & Get Code
                    </button>
                </div>
            </form>
        </div>
</div>
<script>
function toggleIdxGroupField(type) {
    const groupField = document.getElementById('idx_group_field');
    const groupSelect = document.getElementById('group_id');
    if (type === 'pzem') {
        groupField.style.display = '';
        if (groupSelect) groupSelect.required = true;
    } else {
        groupField.style.display = 'none';
        if (groupSelect) groupSelect.required = false;
    }
}
</script>
@endif

<!-- Add Group Modal -->
@if(auth()->user()->role === 'admin')
<div id="add-group-modal" class="hidden fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title-group" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <!-- Background backdrop -->
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity" aria-hidden="true" onclick="document.getElementById('add-group-modal').classList.add('hidden')"></div>

        <!-- Spacer to center modal -->
        <span class="hidden sm:inline-block sm:align-middle sm:min-h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal Panel -->
        <div class="inline-block align-middle bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-md sm:w-full border border-slate-200/60">
            <form action="{{ route('groups.store') }}" method="POST">
                @csrf
                
                <!-- Modal Header -->
                <div class="px-6 py-5 border-b border-slate-100 flex items-start gap-3.5 bg-slate-50/50">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100 flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900" id="modal-title-group">Add New Operational Group</h3>
                        <p class="text-xs text-slate-500 mt-0.5 font-medium">Create a new organizational area for IoT monitoring nodes.</p>
                    </div>
                </div>

                <!-- Form Fields -->
                <div class="px-6 py-5 space-y-4">
                    <!-- Group Name -->
                    <div class="relative group">
                        <label for="group_name" class="block text-xs font-semibold text-slate-600 mb-1.5 group-focus-within:text-indigo-600 transition-colors">Group Name</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 group-focus-within:text-indigo-500 transition-colors">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                            </div>
                            <input type="text" name="name" id="group_name" required placeholder="e.g. Server Room B" 
                                class="w-full pl-9 pr-4 py-2.5 bg-white border border-slate-250 rounded-xl text-slate-900 text-sm font-medium placeholder-slate-405 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 hover:border-slate-300 transition-all duration-200">
                        </div>
                    </div>

                    <!-- Floor Number -->
                    <div class="relative group">
                        <label for="group_floor" class="block text-xs font-semibold text-slate-600 mb-1.5 group-focus-within:text-indigo-600 transition-colors">Floor / Lantai</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 group-focus-within:text-indigo-500 transition-colors">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5" />
                                </svg>
                            </div>
                            <select name="floor" id="group_floor" required 
                                class="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-250 rounded-xl text-slate-900 text-sm font-medium focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 hover:border-slate-300 transition-all duration-200">
                                @for($i = 1; $i <= 10; $i++)
                                    <option value="{{ $i }}">Lantai {{ $i }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="relative group">
                        <label for="group_description" class="block text-xs font-semibold text-slate-600 mb-1.5 group-focus-within:text-indigo-600 transition-colors">Description (Optional)</label>
                        <div class="relative">
                            <textarea name="description" id="group_description" rows="3" placeholder="Describe the operational area..." 
                                class="w-full px-4 py-2.5 bg-white border border-slate-250 rounded-xl text-slate-900 text-sm font-medium placeholder-slate-405 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 hover:border-slate-300 transition-all duration-200"></textarea>
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="bg-slate-50/60 px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                    <button type="button" onclick="document.getElementById('add-group-modal').classList.add('hidden')" 
                        class="px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-xs font-bold text-slate-600 hover:bg-slate-50 hover:text-slate-800 transition-colors duration-200">
                        Cancel
                    </button>
                    <button type="submit" 
                        class="px-5 py-2.5 rounded-xl bg-indigo-650 hover:bg-indigo-600 active:bg-indigo-750 text-xs font-bold text-white shadow-md shadow-indigo-500/10 transition-colors duration-200">
                        Create Group
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@if(auth()->user()->role === 'admin')
<!-- Floating Print Option Bar -->
<div id="print-floating-bar" class="fixed bottom-6 left-1/2 transform -translate-x-1/2 z-40 bg-slate-900 text-white rounded-2xl px-6 py-4 shadow-2xl border border-slate-800 flex items-center gap-4 transition-all duration-300 translate-y-24 opacity-0 pointer-events-none">
    <div class="flex items-center gap-2 flex-shrink-0">
        <span class="flex h-3 w-3 relative">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-3 w-3 bg-blue-500"></span>
        </span>
        <span class="text-xs font-bold text-slate-300"><span id="selected-count" class="text-white font-extrabold">0</span> selected</span>
    </div>
    
    <div class="h-6 w-px bg-slate-800"></div>
    
    <!-- Options -->
    <div class="flex items-center gap-4 text-xs font-semibold">
        <div class="flex items-center gap-1.5">
            <label for="print-size" class="text-slate-400">Size:</label>
            <select id="print-size" class="bg-slate-800 border border-slate-700 text-white rounded-lg px-2.5 py-1 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <option value="small">Small (50x30mm)</option>
                <option value="medium" selected>Medium (80x50mm)</option>
                <option value="large">Large (100x60mm)</option>
            </select>
        </div>
        
        <label class="flex items-center gap-1.5 cursor-pointer text-slate-300 hover:text-white transition-colors">
            <input type="checkbox" id="print-opt-logo" checked class="rounded border-slate-700 bg-slate-800 text-blue-600 focus:ring-blue-500">
            Logo
        </label>
        
        <label class="flex items-center gap-1.5 cursor-pointer text-slate-300 hover:text-white transition-colors">
            <input type="checkbox" id="print-opt-group" checked class="rounded border-slate-700 bg-slate-800 text-blue-600 focus:ring-blue-500">
            Location
        </label>
        
        <label class="flex items-center gap-1.5 cursor-pointer text-slate-300 hover:text-white transition-colors">
            <input type="checkbox" id="print-opt-id" checked class="rounded border-slate-700 bg-slate-800 text-blue-600 focus:ring-blue-500">
            ID
        </label>
    </div>
    
    <div class="h-6 w-px bg-slate-800"></div>
    
    <button onclick="startBatchPrint()" class="bg-blue-600 hover:bg-blue-500 text-white text-xs font-bold px-4 py-2 rounded-xl transition-colors shadow-md shadow-blue-900/40 cursor-pointer flex items-center gap-1.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 00-2 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
        Print
    </button>
</div>

<!-- Hidden Batch Print Container -->
<div id="batch-print-container" style="display: none;"></div>

<style>
/* Base print settings */
@media print {
    body * {
        visibility: hidden !important;
        margin: 0 !important;
        padding: 0 !important;
    }
    #batch-print-container, #batch-print-container * {
        visibility: visible !important;
    }
    #batch-print-container {
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        background: white !important;
    }
    .print-label-item {
        page-break-after: always !important;
        page-break-inside: avoid !important;
        display: flex !important;
        flex-direction: column !important;
        align-items: center !important;
        justify-content: center !important;
        box-sizing: border-box !important;
        background: white !important;
        color: black !important;
        border: 1px dashed #ddd !important;
        margin: 10px auto !important;
    }
    @page {
        margin: 0;
        size: auto;
    }
}

/* Custom Sizes for Label Templates */
.label-size-small {
    width: 50mm;
    height: 30mm;
    padding: 2mm;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    font-size: 8px;
    text-align: center;
}
.label-size-small .lbl-logo { height: 6mm; margin-bottom: 1px; }
.label-size-small .lbl-title { font-size: 8px; font-weight: bold; line-height: 1.1; margin-bottom: 1px; }
.label-size-small .lbl-sub { font-size: 6px; color: #555; margin-bottom: 1px; text-transform: uppercase; }
.label-size-small .lbl-qrcode { width: 14mm; height: 14mm; margin: 0 auto; }
.label-size-small .lbl-qrcode canvas, .label-size-small .lbl-qrcode img { width: 14mm !important; height: 14mm !important; }
.label-size-small .lbl-id { font-size: 5px; color: #666; font-family: monospace; margin-top: 1px; }

.label-size-medium {
    width: 80mm;
    height: 50mm;
    padding: 4mm;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    font-size: 11px;
    text-align: center;
}
.label-size-medium .lbl-logo { height: 9mm; margin-bottom: 2mm; }
.label-size-medium .lbl-title { font-size: 12px; font-weight: bold; line-height: 1.2; margin-bottom: 2px; }
.label-size-medium .lbl-sub { font-size: 8px; color: #555; margin-bottom: 2mm; text-transform: uppercase; }
.label-size-medium .lbl-qrcode { width: 22mm; height: 22mm; margin: 0 auto; }
.label-size-medium .lbl-qrcode canvas, .label-size-medium .lbl-qrcode img { width: 22mm !important; height: 22mm !important; }
.label-size-medium .lbl-id { font-size: 8px; color: #666; font-family: monospace; margin-top: 3px; }

.label-size-large {
    width: 100mm;
    height: 60mm;
    padding: 5mm;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    font-size: 13px;
    text-align: center;
}
.label-size-large .lbl-logo { height: 12mm; margin-bottom: 3mm; }
.label-size-large .lbl-title { font-size: 14px; font-weight: bold; line-height: 1.2; margin-bottom: 3px; }
.label-size-large .lbl-sub { font-size: 10px; color: #555; margin-bottom: 3mm; text-transform: uppercase; }
.label-size-large .lbl-qrcode { width: 26mm; height: 26mm; margin: 0 auto; }
.label-size-large .lbl-qrcode canvas, .label-size-large .lbl-qrcode img { width: 26mm !important; height: 26mm !important; }
.label-size-large .lbl-id { font-size: 9px; color: #666; font-family: monospace; margin-top: 4px; }
</style>

<script src="{{ asset('js/qrcode.min.js') }}"></script>
<script>
function toggleSelectAll(masterCb) {
    const checkboxes = document.querySelectorAll('.device-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = masterCb.checked;
    });
    updateSelectionBar();
}

function updateSelectionBar() {
    const checkboxes = document.querySelectorAll('.device-checkbox:checked');
    const floatingBar = document.getElementById('print-floating-bar');
    const selectedCount = document.getElementById('selected-count');
    
    if (checkboxes.length > 0) {
        selectedCount.innerText = checkboxes.length;
        floatingBar.classList.remove('translate-y-24', 'opacity-0', 'pointer-events-none');
    } else {
        floatingBar.classList.add('translate-y-24', 'opacity-0', 'pointer-events-none');
        document.getElementById('select-all-devices').checked = false;
    }
}

function startBatchPrint() {
    const checkedBoxes = document.querySelectorAll('.device-checkbox:checked');
    if (checkedBoxes.length === 0) return;

    const size = document.getElementById('print-size').value;
    const incLogo = document.getElementById('print-opt-logo').checked;
    const incGroup = document.getElementById('print-opt-group').checked;
    const incId = document.getElementById('print-opt-id').checked;

    const printContainer = document.getElementById('batch-print-container');
    printContainer.innerHTML = ''; // Clear previous

    const logoUrl = "{{ asset('logo.png') }}";

    checkedBoxes.forEach((cb, idx) => {
        const name = cb.getAttribute('data-name');
        const devId = cb.getAttribute('data-id');
        const group = cb.getAttribute('data-group');
        const url = cb.getAttribute('data-url');

        // Create label item container
        const labelItem = document.createElement('div');
        labelItem.className = `print-label-item label-size-${size}`;

        // Assemble content
        let htmlContent = '';
        if (incLogo) {
            htmlContent += `<img src="${logoUrl}" class="lbl-logo mx-auto" alt="Logo">`;
        }
        htmlContent += `<div class="lbl-title">${name}</div>`;
        if (incGroup) {
            htmlContent += `<div class="lbl-sub">${group}</div>`;
        }
        
        // QR Code Placeholder
        const qrId = `qrcode-batch-${idx}`;
        htmlContent += `<div id="${qrId}" class="lbl-qrcode"></div>`;
        
        if (incId) {
            htmlContent += `<div class="lbl-id">${devId}</div>`;
        }

        labelItem.innerHTML = htmlContent;
        printContainer.appendChild(labelItem);

        // Generate QR code
        let qrDimension = 80;
        if (size === 'small') qrDimension = 50;
        if (size === 'large') qrDimension = 100;

        new QRCode(document.getElementById(qrId), {
            text: url,
            width: qrDimension,
            height: qrDimension,
            colorDark : "#000000",
            colorLight : "#ffffff",
            correctLevel : QRCode.CorrectLevel.H
        });
    });

    // Short timeout to allow QRCode canvas/images to render before opening print dialog
    setTimeout(() => {
        window.print();
    }, 350);
}
</script>
@endif
@endsection
