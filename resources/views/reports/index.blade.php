@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Energy Reports</h1>
            <p class="text-gray-500">Download and archive energy consumption reports in PDF and CSV format.</p>
        </div>
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-3 text-xs text-blue-700 font-semibold flex items-center gap-2 self-start sm:self-auto">
            <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            PLN Tariff Rate: Rp {{ number_format($plnTariff, 2, ',', '.') }} / kWh
        </div>
    </div>

    @if(session('error'))
        <div class="mb-6 bg-rose-50 border-l-4 border-rose-500 p-4 rounded-xl shadow-sm text-sm font-semibold text-rose-800 flex items-center gap-2">
            <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            {{ session('error') }}
        </div>
    @endif

    @php
        $activeTab = request()->query('tab', 'daily');
    @endphp

    <!-- Tabs Header -->
    <div class="border-b border-gray-200 mb-8 overflow-x-auto scrollbar-none">
        <nav class="-mb-px flex space-x-8 min-w-max" aria-label="Tabs">
            <button onclick="switchTab('daily')" id="tab-btn-daily" 
                class="whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm transition-all focus:outline-none 
                {{ $activeTab === 'daily' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Daily Reports
            </button>
            <button onclick="switchTab('monthly')" id="tab-btn-monthly" 
                class="whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm transition-all focus:outline-none 
                {{ $activeTab === 'monthly' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                Monthly Reports
            </button>
        </nav>
    </div>

    <!-- Tab 1: Daily Reports -->
    <div id="tab-content-daily" class="{{ $activeTab === 'daily' ? '' : 'hidden' }}">
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Report Date</th>
                            <th scope="col" class="hidden sm:table-cell px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Active Devices</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Total Energy</th>
                            <th scope="col" class="hidden md:table-cell px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Estimated Cost</th>
                            <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($reports as $report)
                            @php
                                $estCost = $report->total_kwh * $plnTariff;
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                    {{ \Carbon\Carbon::parse($report->date)->format('F d, Y') }}
                                </td>
                                <td class="hidden sm:table-cell px-6 py-4 whitespace-nowrap text-sm text-center text-gray-600">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                                        {{ $report->device_count }} Devices
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-teal-600">
                                    {{ number_format($report->total_kwh, 3, ',', '.') }} kWh
                                </td>
                                <td class="hidden md:table-cell px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-blue-600">
                                    Rp {{ number_format($estCost, 2, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                    <a href="{{ route('reports.download', $report->date) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-semibold shadow-sm transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                        Download PDF
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500 italic">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="h-10 w-10 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                        No reports found. Generate or log daily energy data first.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($reports->hasPages())
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                {{ $reports->appends(['tab' => 'daily', 'monthly_page' => request()->query('monthly_page')])->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- Tab 2: Monthly Reports -->
    <div id="tab-content-monthly" class="{{ $activeTab === 'monthly' ? '' : 'hidden' }}">
        <div class="bg-white rounded-2xl shadow-sm overflow-hidden border border-gray-200">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Report Month</th>
                            <th scope="col" class="hidden sm:table-cell px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Monitored Devices</th>
                            <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Total Energy</th>
                            <th scope="col" class="hidden md:table-cell px-6 py-4 text-right text-xs font-bold text-gray-500 uppercase tracking-wider">Estimated Cost</th>
                            <th scope="col" class="px-6 py-4 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse ($monthlyReports as $report)
                            @php
                                $estCost = $report->total_kwh * $plnTariff;
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                    {{ \Carbon\Carbon::parse($report->month . '-01')->format('F Y') }}
                                </td>
                                <td class="hidden sm:table-cell px-6 py-4 whitespace-nowrap text-sm text-center text-gray-600">
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                                        {{ $report->device_count }} Devices
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-teal-600">
                                    {{ number_format($report->total_kwh, 3, ',', '.') }} kWh
                                </td>
                                <td class="hidden md:table-cell px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-blue-600">
                                    Rp {{ number_format($estCost, 2, ',', '.') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-center flex items-center justify-center gap-2">
                                    <a href="{{ route('reports.download_monthly', $report->month) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs font-semibold shadow-sm transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" /></svg>
                                        Download PDF
                                    </a>
                                    <a href="{{ route('reports.export_monthly_csv', $report->month) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-semibold shadow-sm transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                        Export CSV
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-12 text-center text-gray-500 italic">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="h-10 w-10 text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                                        No monthly reports found. Generate or log daily energy data first.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($monthlyReports->hasPages())
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                {{ $monthlyReports->appends(['tab' => 'monthly', 'daily_page' => request()->query('daily_page')])->links() }}
            </div>
            @endif
        </div>
    </div>
</div>

<script>
    function switchTab(tabId) {
        // Toggle tab contents
        document.getElementById('tab-content-daily').classList.add('hidden');
        document.getElementById('tab-content-monthly').classList.add('hidden');
        document.getElementById('tab-content-' + tabId).classList.remove('hidden');

        // Reset and apply active button styles
        const tabs = ['daily', 'monthly'];
        tabs.forEach(t => {
            const btn = document.getElementById('tab-btn-' + t);
            if (t === tabId) {
                btn.className = "whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm transition-all focus:outline-none border-blue-600 text-blue-600";
            } else {
                btn.className = "whitespace-nowrap py-4 px-1 border-b-2 font-bold text-sm transition-all focus:outline-none border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300";
            }
        });

        // Update URL query parameter without reloading
        const url = new URL(window.location);
        url.searchParams.set('tab', tabId);
        window.history.pushState({}, '', url);
    }
</script>
@endsection
