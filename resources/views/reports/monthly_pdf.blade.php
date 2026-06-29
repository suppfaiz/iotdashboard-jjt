<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Monthly Energy Report - {{ $month }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #333333;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .header-logo {
            width: 150px;
            vertical-align: middle;
        }
        .header-title-container {
            text-align: right;
            vertical-align: middle;
        }
        .header-title {
            font-size: 18px;
            font-weight: bold;
            color: #1e3a8a;
            margin: 0;
        }
        .header-subtitle {
            font-size: 10px;
            color: #6b7280;
            margin-top: 5px;
            margin-bottom: 0;
        }
        .meta-section {
            background-color: #f3f4f6;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 20px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-label {
            font-weight: bold;
            color: #4b5563;
            width: 25%;
        }
        .meta-value {
            color: #111827;
            width: 25%;
        }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #1e3a8a;
            border-bottom: 1.5px solid #1e3a8a;
            padding-bottom: 4px;
            margin-top: 20px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        .chart-container {
            text-align: center;
            margin-bottom: 25px;
            padding: 10px;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            background-color: #ffffff;
        }
        .chart-img {
            max-width: 100%;
            height: auto;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .data-table th {
            background-color: #1e3a8a;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 6px 8px;
            font-size: 10px;
            border: 1px solid #1e3a8a;
        }
        .data-table td {
            padding: 6px 8px;
            border: 1px solid #e5e7eb;
        }
        .data-table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .text-bold {
            font-weight: bold;
        }
        .summary-container {
            width: 100%;
            margin-bottom: 30px;
        }
        .summary-table {
            width: 40%;
            margin-left: auto;
            border-collapse: collapse;
        }
        .summary-table td {
            padding: 5px 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        .summary-total-label {
            font-weight: bold;
            font-size: 12px;
            color: #1e3a8a;
        }
        .summary-total-value {
            font-weight: bold;
            font-size: 12px;
            color: #1e3a8a;
        }
        .signature-section {
            width: 100%;
            margin-top: 40px;
        }
        .signature-box {
            width: 200px;
            float: right;
            text-align: center;
        }
        .signature-line {
            margin-top: 50px;
            border-top: 1px solid #333;
            width: 180px;
            margin-left: auto;
            margin-right: auto;
        }
        .footer {
            position: fixed;
            bottom: -10px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 5px;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <table class="header-table">
        <tr>
            <td>
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" class="header-logo" alt="Logo">
                @else
                    <span style="font-size: 18px; font-weight: bold; color: #1e3a8a;">JAMKRIDA ENERGY</span>
                @endif
            </td>
            <td class="header-title-container">
                <div class="header-title">MONTHLY ENERGY CONSUMPTION REPORT</div>
                <div class="header-subtitle">Generated automatically on {{ now()->format('M d, Y H:i:s') }}</div>
            </td>
        </tr>
    </table>

    <!-- Meta Info -->
    <div class="meta-section">
        <table class="meta-table">
            <tr>
                <td class="meta-label">Report Month:</td>
                <td class="meta-value text-bold" style="color: #1e3a8a;">{{ $month }}</td>
                <td class="meta-label">PLN Tariff Rate:</td>
                <td class="meta-value">Rp {{ number_format($plnTariff, 2, ',', '.') }} / kWh</td>
            </tr>
            <tr>
                <td class="meta-label">Total Consumption:</td>
                <td class="meta-value text-bold" style="color: #0d9488;">{{ number_format($totalKwh, 3, ',', '.') }} kWh</td>
                <td class="meta-label">Total Cost:</td>
                <td class="meta-value text-bold" style="color: #1d4ed8;">Rp {{ number_format($totalCost, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="meta-label">Avg. Daily Usage:</td>
                <td class="meta-value">{{ number_format($avgDailyKwh, 3, ',', '.') }} kWh/day</td>
                <td class="meta-label">Logged Days:</td>
                <td class="meta-value">{{ $dailySummary->count() }} Days</td>
            </tr>
        </table>
    </div>

    <!-- Chart -->
    @if($chartBase64)
        <div class="section-title">Konsumsi Energi Harian</div>
        <div class="chart-container">
            <img src="{{ $chartBase64 }}" class="chart-img" alt="Daily Energy Chart">
        </div>
    @endif

    <!-- Device Contribution Table -->
    <div class="section-title">Kontribusi Konsumsi Perangkat</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">No</th>
                <th style="width: 25%;">Device ID</th>
                <th style="width: 35%;">Device Name</th>
                <th style="width: 20%;">Group Area</th>
                <th style="width: 15%;" class="text-right">Total Energy (kWh)</th>
            </tr>
        </thead>
        <tbody>
            @php $num = 1; @endphp
            @foreach($deviceSummary as $devId => $summary)
                <tr>
                    <td class="text-center">{{ $num++ }}</td>
                    <td style="font-family: monospace;">{{ $summary['device_id'] }}</td>
                    <td class="text-bold">{{ $summary['name'] }}</td>
                    <td>{{ $summary['group'] }}</td>
                    <td class="text-right text-bold" style="color: #0d9488;">{{ number_format($summary['total_kwh'], 3, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="page-break"></div>

    <!-- Daily Breakdown Table -->
    <div class="section-title" style="margin-top: 0;">Rincian Konsumsi Harian</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 10%;" class="text-center">No</th>
                <th style="width: 40%;">Tanggal</th>
                <th style="width: 25%;" class="text-right">Total Konsumsi (kWh)</th>
                <th style="width: 25%;" class="text-right">Estimasi Biaya (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @php $dayNum = 1; @endphp
            @foreach($dailySummary as $dateStr => $dayKwh)
                @php
                    $dayCost = $dayKwh * $plnTariff;
                @endphp
                <tr>
                    <td class="text-center">{{ $dayNum++ }}</td>
                    <td class="text-bold">{{ \Carbon\Carbon::parse($dateStr)->format('F d, Y') }}</td>
                    <td class="text-right text-bold" style="color: #0d9488;">{{ number_format($dayKwh, 3, ',', '.') }}</td>
                    <td class="text-right text-bold" style="color: #1d4ed8;">Rp {{ number_format($dayCost, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Summary Section -->
    <div class="summary-container">
        <table class="summary-table">
            <tr>
                <td class="text-bold">Total Bulanan (kWh):</td>
                <td class="text-right text-bold" style="color: #0d9488;">{{ number_format($totalKwh, 3, ',', '.') }} kWh</td>
            </tr>
            <tr>
                <td class="summary-total-label">Total Tagihan (Rp):</td>
                <td class="text-right summary-total-value">Rp {{ number_format($totalCost, 2, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <!-- Signature Section -->
    <div class="signature-section">
        <div class="signature-box">
            <p>Validated By,</p>
            <div class="signature-line"></div>
            <p style="font-weight: bold; margin-top: 5px;">Operations Manager</p>
        </div>
    </div>

    <!-- Footer Page -->
    <div class="footer">
        Jamkrida Energy IoT Platform &bull; Repository: https://github.com/suppfaiz/iotdashboard-jjt &bull; Confidential Internal Report
    </div>

</body>
</html>
