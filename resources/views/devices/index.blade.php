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
        <button type="button" onclick="document.getElementById('add-device-modal').classList.remove('hidden')" class="relative inline-flex items-center gap-x-1.5 rounded-lg bg-blue-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 transition-colors">
            <svg class="-ml-0.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
            </svg>
            Add New Device
        </button>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Device & ID</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Group Area</th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-extrabold text-gray-500 uppercase tracking-wider">Status</th>
                    <th scope="col" class="px-6 py-3 text-right text-xs font-extrabold text-gray-500 uppercase tracking-wider">Indicators (V/A/W)</th>
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
                    <td class="px-6 py-4 whitespace-nowrap">
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
                    <td class="px-6 py-4 whitespace-nowrap text-right">
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
                        No devices registered yet.<br>Click "Add New Device" to provision your first IoT meter.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Add Device Modal -->
@if(auth()->user()->role === 'admin')
<div id="add-device-modal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity"></div>
    <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <form method="POST" action="{{ route('devices.store') }}" class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg border border-gray-100">
                @csrf
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                            <h3 class="text-lg font-bold leading-6 text-gray-900" id="modal-title">Provision New Device</h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500 mb-4">Register a new IoT smart meter. This will generate the C++ firmware template for the ESP32/ESP8266.</p>
                                
                                <div class="space-y-4">
                                    <div>
                                        <label for="name" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Device Name</label>
                                        <input type="text" name="name" id="name" required placeholder="e.g. Main Inverter Monitor" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                    </div>

                                    <div>
                                        <label for="group_id" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Group Area</label>
                                        <select name="group_id" id="group_id" required class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                            @foreach($groups as $g)
                                                <option value="{{ $g->id }}">{{ $g->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label for="wifi_ssid" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">SSID Wifi</label>
                                        <input type="text" name="wifi_ssid" id="wifi_ssid" required placeholder="Network Name" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                    </div>

                                    <div>
                                        <label for="wifi_password" class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1">Password Wifi</label>
                                        <input type="password" name="wifi_password" id="wifi_password" required placeholder="••••••••" class="w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                    <button type="submit" class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-semibold text-white hover:bg-blue-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                        Register & Get Code
                    </button>
                    <button type="button" onclick="document.getElementById('add-device-modal').classList.add('hidden')" class="mt-3 w-full inline-flex justify-center rounded-lg border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-semibold text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-colors">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
