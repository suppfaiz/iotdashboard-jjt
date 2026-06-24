<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Daily Energy Report - {{ $date }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: #333333;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
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
            font-size: 20px;
            font-weight: bold;
            color: #1e3a8a;
            margin: 0;
        }
        .header-subtitle {
            font-size: 11px;
            color: #6b7280;
            margin-top: 5px;
            margin-bottom: 0;
        }
        .meta-section {
            background-color: #f3f4f6;
            border: 1px solid #e5e7eb;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 25px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-label {
            font-weight: bold;
            color: #4b5563;
            width: 30%;
        }
        .meta-value {
            color: #111827;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .data-table th {
            background-color: #1e3a8a;
            color: #ffffff;
            font-weight: bold;
            text-align: left;
            padding: 8px 10px;
            font-size: 11px;
            border: 1px solid #1e3a8a;
        }
        .data-table td {
            padding: 8px 10px;
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
            margin-bottom: 40px;
        }
        .summary-table {
            width: 40%;
            margin-left: auto;
            border-collapse: collapse;
        }
        .summary-table td {
            padding: 6px 10px;
            border-bottom: 1px solid #e5e7eb;
        }
        .summary-total-label {
            font-weight: bold;
            font-size: 13px;
            color: #1e3a8a;
        }
        .summary-total-value {
            font-weight: bold;
            font-size: 13px;
            color: #1e3a8a;
        }
        .signature-section {
            width: 100%;
            margin-top: 50px;
        }
        .signature-box {
            width: 200px;
            float: right;
            text-align: center;
        }
        .signature-line {
            margin-top: 60px;
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
            font-size: 10px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 5px;
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
                    <span style="font-size: 20px; font-weight: bold; color: #1e3a8a;">JAMKRIDA ENERGY</span>
                @endif
            </td>
            <td class="header-title-container">
                <div class="header-title">DAILY ENERGY CONSUMPTION REPORT</div>
                <div class="header-subtitle">Generated automatically on {{ now()->format('M d, Y H:i:s') }}</div>
            </td>
        </tr>
    </table>

    <!-- Meta Info -->
    <div class="meta-section">
        <table class="meta-table">
            <tr>
                <td class="meta-label">Report Date:</td>
                <td class="meta-value">{{ $date }}</td>
                <td class="meta-label">PLN Base Tariff:</td>
                <td class="meta-value">Rp {{ number_format($plnTariff, 2, ',', '.') }} / kWh</td>
            </tr>
            <tr>
                <td class="meta-label">Active Nodes:</td>
                <td class="meta-value">{{ $logs->count() }} Devices</td>
                <td class="meta-label">Report Period:</td>
                <td class="meta-value">24 Hours (Daily Log)</td>
            </tr>
        </table>
    </div>

    <!-- Main Data Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">No</th>
                <th style="width: 20%;">Device ID</th>
                <th style="width: 30%;">Device Name</th>
                <th style="width: 20%;">Group Area</th>
                <th style="width: 12%;" class="text-right">Energy (kWh)</th>
                <th style="width: 13%;" class="text-right">Est. Cost (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $index => $log)
                @php
                    $cost = $log->total_kwh_harian * $plnTariff;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td style="font-family: monospace;">{{ $log->device->device_id }}</td>
                    <td class="text-bold">{{ $log->device->name }}</td>
                    <td>{{ $log->device->group->name ?? '-' }}</td>
                    <td class="text-right text-bold" style="color: #0d9488;">{{ number_format($log->total_kwh_harian, 3, ',', '.') }}</td>
                    <td class="text-right text-bold" style="color: #1d4ed8;">{{ number_format($cost, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Summary Section -->
    <div class="summary-container">
        <table class="summary-table">
            <tr>
                <td class="text-bold">Total Energy:</td>
                <td class="text-right text-bold" style="color: #0d9488;">{{ number_format($totalKwh, 3, ',', '.') }} kWh</td>
            </tr>
            <tr>
                <td class="summary-total-label">Total Cost:</td>
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
