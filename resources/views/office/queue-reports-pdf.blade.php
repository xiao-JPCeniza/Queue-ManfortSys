<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Queue Reports</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: DejaVu Sans, Arial, sans-serif;
            color: #0f172a;
            font-size: 12px;
        }
        .page {
            padding: 20px;
        }
        .header {
            margin-bottom: 16px;
        }
        .title {
            margin: 0;
            font-size: 22px;
            font-weight: 700;
        }
        .meta {
            margin-top: 4px;
            color: #475569;
            font-size: 11px;
        }
        .block {
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            margin-bottom: 14px;
            overflow: hidden;
        }
        .block-title {
            background: #f1f5f9;
            color: #334155;
            font-size: 12px;
            font-weight: 700;
            padding: 10px 12px;
            border-bottom: 1px solid #cbd5e1;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border-bottom: 1px solid #e2e8f0;
            padding: 8px 10px;
            text-align: center;
        }
        th {
            color: #475569;
            font-weight: 700;
            background: #ffffff;
        }
        tr:last-child td {
            border-bottom: none;
        }
        .empty {
            color: #64748b;
            font-style: italic;
        }
        .row {
            width: 100%;
            border-collapse: separate;
            border-spacing: 12px 0;
            table-layout: fixed;
        }
        .row-cell {
            vertical-align: top;
            width: 50%;
        }
        .metric-box {
            min-height: 92px;
            display: table;
            width: 100%;
        }
        .metric-value {
            display: table-cell;
            vertical-align: middle;
            text-align: center;
            font-size: 28px;
            font-weight: 700;
            color: #334155;
        }
    </style>
</head>
<body>
    <main class="page">
        <header class="header">
            <h1 class="title">Queue Reports - {{ $reportScopeLabel ?? $office->name }}</h1>
            <p class="meta">Generated: {{ $generatedAt->format('M d, Y h:i:s A') }} (Asia/Manila)</p>
        </header>

        <section class="block">
            <div class="block-title">Daily Queue Counts (Last 7 Days)</div>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Total Tickets</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dailyCounts as $row)
                        <tr>
                            <td>{{ $row['date'] }}</td>
                            <td>{{ $row['total_tickets'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="empty">No queue activity in the last 7 days.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <section class="block">
            <div class="block-title">Weekly Queue Counts (Last 5 Weeks)</div>
            <table>
                <thead>
                    <tr>
                        <th>Week #</th>
                        <th>Total Tickets</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($weeklyCounts as $row)
                        <tr>
                            <td>{{ $row['week'] }}</td>
                            <td>{{ $row['total_tickets'] }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="empty">No queue activity in the last 5 weeks.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </section>

        <table class="row">
            <tr>
                <td class="row-cell">
                    <section class="block">
                        <div class="block-title">Status Summary</div>
                        <table>
                            <thead>
                                <tr>
                                    <th>Served</th>
                                    <th>Skipped</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>{{ $servedCount }}</td>
                                    <td>{{ $skippedCount }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </section>
                </td>
                <td class="row-cell">
                    <section class="block">
                        <div class="block-title">Average Processing Time</div>
                        <div class="metric-box">
                            <div class="metric-value">{{ $averageProcessingTime }}</div>
                        </div>
                    </section>
                </td>
            </tr>
        </table>
    </main>
</body>
</html>
