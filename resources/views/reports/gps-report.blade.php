<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>GPS Historical Report</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo {
            width: 80px;
        }

        .title {
            font-size: 16px;
            font-weight: bold;
        }

        .subtitle {
            font-size: 13px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
        }

        th {
            background: #f2f2f2;
        }

        .summary-box {
            margin-top: 15px;
        }

        .chart {
            margin-top: 20px;
        }

        .bar {
            display: inline-block;
            background: #4CAF50;
            height: 10px;
        }

        footer {
            position: fixed;
            bottom: -20px;
            left: 0;
            right: 0;
            height: 30px;
            text-align: center;
            font-size: 10px;
        }
    </style>
</head>

<body>

    <!-- 🔷 HEADER -->
    <div class="header">
        <img src="{{ public_path('logo.png') }}" class="logo">
        <div class="title">X-STREAM River Monitoring System</div>
        <div class="subtitle">
            GPS Historical Report <br>
            From: {{ $from->format('Y-m-d H:i') }} |
            To: {{ $to->format('Y-m-d H:i') }}
        </div>
    </div>

    <!-- 🔷 SUMMARY -->
    <div class="summary-box">
        <strong>Summary Statistics:</strong><br>
        Total Readings: {{ $totalReadings }} <br>
        Unique Buoys: {{ $uniqueBuoys }} <br>
        First Record: {{ $firstRecord ?? 'N/A' }} <br>
        Last Record: {{ $lastRecord ?? 'N/A' }}
    </div>

    <!-- 🔷 TABLE -->
    @if($readings->isEmpty())
    <p>No data available for selected date range.</p>
    @else
    <table>
        <thead>
            <tr>
                <th>Buoy</th>
                <th>Latitude</th>
                <th>Longitude</th>
                <th>Recorded At</th>
            </tr>
        </thead>
        <tbody>
            @foreach($readings as $reading)
            <tr>
                <td>{{ $reading->buoy->name ?? 'N/A' }}</td>
                <td>{{ $reading->latitude }}</td>
                <td>{{ $reading->longitude }}</td>
                <td>{{ $reading->recorded_at }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    <!-- 🔷 BAR CHART -->
    <div class="chart">
        <h4>Readings Per Day</h4>

        @if($chartData->isEmpty())
        <p>No chart data available.</p>
        @else

        @php
        $max = $chartData->max();
        @endphp

        @foreach($chartData as $date => $count)
        @php
        $width = ($count / $max) * 300;
        @endphp

        <div>
            {{ $date }} ({{ $count }})
            <div class="bar" style="width: {{ $width }}px;"></div>
        </div>
        @endforeach

        @endif
    </div>

    <!-- 🔷 PAGE NUMBER -->
    <footer></footer>

    <script type="text/php">
        if (isset($pdf)) {
    $pdf->page_script('
        $font = $fontMetrics->get_font("DejaVu Sans", "normal");
        $pdf->text(520, 820, "Page " . $PAGE_NUM . " of " . $PAGE_COUNT, $font, 8);
    ');
}
</script>

</body>

</html>
