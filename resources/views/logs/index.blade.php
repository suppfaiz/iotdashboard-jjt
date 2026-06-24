@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Historical Energy Logs</h1>
            <p class="text-gray-500">View logged device metrics collected periodically.</p>
        </div>
        
        <!-- Log Type Selector Toggle -->
        <div class="flex bg-gray-150 p-1.5 rounded-xl border border-gray-200 shadow-sm bg-gray-100">
            <a href="{{ route('logs.index', ['type' => 'daily']) }}" 
                class="px-4 py-2 text-xs font-bold rounded-lg transition-all duration-300 {{ $type === 'daily' ? 'bg-white text-blue-600 border border-gray-200 shadow-sm font-extrabold' : 'text-gray-500 hover:text-gray-800' }}">
                Daily Logs
            </a>
            <a href="{{ route('logs.index', ['type' => 'hourly']) }}" 
                class="px-4 py-2 text-xs font-bold rounded-lg transition-all duration-300 {{ $type === 'hourly' ? 'bg-white text-blue-600 border border-gray-200 shadow-sm font-extrabold' : 'text-gray-500 hover:text-gray-800' }}">
                Hourly Logs
            </a>
        </div>
    </div>

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
                            <td colspan="{{ $type === 'hourly' ? 8 : 5 }}" class="px-6 py-8 text-center text-gray-500 italic">
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
