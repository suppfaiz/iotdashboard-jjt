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
            <button type="button" onclick="document.getElementById('add-device-modal').classList.remove('hidden')" class="relative inline-flex items-center gap-x-1.5 rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 transition-colors">
                <svg class="-ml-0.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
                </svg>
                Add New Device
            </button>
        @endif
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Device & ID</th>
                    <th scope="col" class="hidden sm:table-cell px-6 py-3 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Group Area</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="hidden md:table-cell px-6 py-3 text-right text-xs font-extrabold text-gray-500 uppercase tracking-wider">Indicators (V/A/W)</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-extrabold text-gray-500 uppercase tracking-wider">Total Energy</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-extrabold text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($devices as $device)
                <tr class="hover:bg-gray-50 transition-colors">
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
                    <td colspan="6" class="px-6 py-12 text-center text-sm font-medium text-gray-500">
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

                    <!-- Group Area -->
                    <div class="relative group">
                        <label for="group_id" class="block text-xs font-semibold text-slate-600 mb-1.5 group-focus-within:text-blue-600 transition-colors">Group Area</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 group-focus-within:text-blue-500 transition-colors">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                </svg>
                            </div>
                            <select name="group_id" id="group_id" required 
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
</div>
@endif
@endsection
