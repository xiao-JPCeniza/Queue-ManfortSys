@php($reportScopeLabel = $isAllOfficesReportScope ? 'All Municipal Offices' : $reportOfficeLabel)
@php($reportHeadline = $isAllOfficesReportScope ? 'Official Municipal Queue Performance Summary' : $reportOfficeLabel . ' Queue Performance Summary')
@php($reportDescription = $isAllOfficesReportScope
    ? 'Validated service-delivery analytics for all municipal offices within the LGU queue operations workflow.'
    : 'Validated service-delivery analytics for the ' . $reportOfficeLabel . ' office within the LGU queue operations workflow.')
@php($dailyQueueCounts = collect($queueReportDailyCounts ?? []))
@php($weeklyQueueCounts = collect($queueReportWeeklyCounts ?? []))
@php($dailyMax = max(1, (int) $dailyQueueCounts->max('total_tickets')))
@php($weeklyMax = max(1, (int) $weeklyQueueCounts->max('total_tickets')))
@php($dailyPeak = $dailyQueueCounts->sortByDesc('total_tickets')->first())
@php($weeklyPeak = $weeklyQueueCounts->sortByDesc('total_tickets')->first())

<div class="gov-report-shell space-y-6">
    <section class="gov-report-masthead" aria-labelledby="reports-overview-heading">
        <div class="gov-report-banner">
            <div class="gov-report-seal-wrap">
                <img src="{{ asset('images/lgu-logo.png') }}" alt="Municipality of Manolo Fortich official seal" class="gov-report-seal">
            </div>

            <div>
                <p class="gov-report-kicker">Municipal Queue Reporting</p>
                <h2 id="reports-overview-heading" class="gov-font-heading gov-report-title">{{ $reportHeadline }}</h2>
                <p class="gov-report-copy">{{ $reportDescription }}</p>
            </div>
        </div>

        <div class="gov-report-meta" aria-label="Report metadata">
            <span class="gov-report-chip gov-report-chip-strong">Official Record</span>
            <span class="gov-report-chip">Scope: {{ $reportScopeLabel }}</span>
            <span class="gov-report-chip gov-report-chip-accent">{{ now()->timezone('Asia/Manila')->format('F j, Y') }}</span>
        </div>
    </section>

    <section class="gov-report-card gov-report-summary-card p-6" aria-labelledby="summary-heading">
        <div class="gov-report-card-head gov-report-card-head-wide">
            <div>
                <p class="gov-report-card-kicker">Executive Summary</p>
                <h2 id="summary-heading" class="gov-font-heading gov-report-card-title">
                    {{ $isAllOfficesReportScope ? 'Total Tickets Accommodated Across All Offices' : 'Total Tickets Accommodated (' . $reportOfficeLabel . ')' }}
                </h2>
            </div>
            <p class="gov-report-card-meta">
                Daily counts and officially accommodated totals used for municipal office monitoring and administrative review.
            </p>
        </div>

        <div class="gov-report-summary-grid mt-5">
            <article class="gov-report-stat gov-report-stat-neutral">
                <p class="gov-report-stat-label">Total Today</p>
                <p class="gov-report-stat-value">{{ $summary['total_today'] }}</p>
                <p class="gov-report-stat-note">Tickets issued and logged for the current reporting day.</p>
            </article>

            <article class="gov-report-stat gov-report-stat-success">
                <p class="gov-report-stat-label">Completed Today</p>
                <p class="gov-report-stat-value">{{ $summary['completed_today'] }}</p>
                <p class="gov-report-stat-note">Successfully served tickets finalized within today&apos;s queue cycle.</p>
            </article>

            <article class="gov-report-stat gov-report-stat-accent">
                <p class="gov-report-stat-label">
                    {{ $isAllOfficesReportScope ? 'Total Accommodated' : 'Total Accommodated (' . $reportOfficeLabel . ')' }}
                </p>
                <p class="gov-report-stat-value">{{ $summary['overall_accommodated'] }}</p>
                <p class="gov-report-stat-note">Officially accommodated ticket volume recorded in the reporting scope.</p>
            </article>
        </div>
    </section>

    @if(auth()->user()?->isSuperAdmin())
        @php($topOffice = $officeAccommodatedChartSeries[0] ?? null)

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <section class="gov-report-card p-6" aria-labelledby="office-accommodated-pie-heading">
                <div class="gov-report-card-head">
                    <div>
                        <p class="gov-report-card-kicker">Office Distribution</p>
                        <h2 id="office-accommodated-pie-heading" class="gov-font-heading gov-report-card-title">Accommodated Tickets Share by Office</h2>
                    </div>
                    <p class="gov-report-card-meta">{{ number_format($officeAccommodatedTotal) }} total accommodated tickets</p>
                </div>

                <div class="gov-report-donut-layout mt-5">
                    <div class="gov-report-donut-wrap">
                        <div class="gov-report-donut"
                             style="background: {{ $officeAccommodatedPieStyle }};"
                             role="img"
                             aria-label="Pie chart of accommodated tickets by office">
                            <div class="gov-report-donut-core"></div>
                            <div class="gov-report-donut-center">
                                <span class="gov-report-donut-label">Total</span>
                                <span class="gov-report-donut-value">{{ number_format($officeAccommodatedTotal) }}</span>
                            </div>
                        </div>

                        @if(!$officeAccommodatedHasData)
                            <p class="gov-report-empty-note">No accommodated tickets recorded yet.</p>
                        @endif
                    </div>

                    <div class="gov-report-legend-grid">
                        @forelse($officeAccommodatedChartSeries as $officeRow)
                            <article class="gov-report-legend-item">
                                <div class="gov-report-legend-item-head">
                                    <span class="gov-report-legend-label">
                                        <span class="h-2.5 w-2.5 rounded-full {{ $officeRow['chip_class'] }}" aria-hidden="true"></span>
                                        {{ $officeRow['office_name'] }}
                                    </span>
                                    <span class="gov-report-legend-value">{{ number_format($officeRow['accommodated_total']) }}</span>
                                </div>
                                <p class="gov-report-legend-meta">{{ number_format($officeRow['percentage'], 1) }}% of all accommodated tickets</p>
                            </article>
                        @empty
                            <p class="gov-report-empty-note">No office accommodation data found.</p>
                        @endforelse
                    </div>
                </div>
            </section>

            <section class="gov-report-card p-6" aria-labelledby="office-accommodated-bar-heading">
                <div class="gov-report-card-head">
                    <div>
                        <p class="gov-report-card-kicker">Comparative View</p>
                        <h2 id="office-accommodated-bar-heading" class="gov-font-heading gov-report-card-title">Accommodated Tickets by Office</h2>
                    </div>
                    <p class="gov-report-card-meta">
                        @if($topOffice)
                            Highest volume: {{ $topOffice['office_name'] }} ({{ number_format($topOffice['accommodated_total']) }})
                        @else
                            No available chart data
                        @endif
                    </p>
                </div>

                <div class="mt-5 space-y-4">
                    @forelse($officeAccommodatedChartSeries as $officeRow)
                        @php($barWidth = $officeAccommodatedMax > 0 ? ($officeRow['accommodated_total'] > 0 ? max(2, round(($officeRow['accommodated_total'] / $officeAccommodatedMax) * 100, 1)) : 0) : 0)
                        <div class="gov-report-bar-row">
                            <div class="gov-report-bar-head">
                                <span class="gov-report-bar-label">{{ $officeRow['office_name'] }}</span>
                                <span class="gov-report-bar-value">{{ number_format($officeRow['accommodated_total']) }} ({{ number_format($officeRow['percentage'], 1) }}%)</span>
                            </div>
                            <div class="gov-report-bar-track">
                                @if($barWidth > 0)
                                    <span class="gov-report-bar-fill {{ $officeRow['bar_class'] }}" style="width: {{ $barWidth }}%;"></span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="gov-report-empty-note">No office accommodation data found.</p>
                    @endforelse
                </div>
            </section>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <section class="gov-report-card p-6" aria-labelledby="daily-queue-counts-heading">
                <div class="gov-report-card-head gov-report-card-head-wide">
                    <div>
                        <p class="gov-report-card-kicker">Seven-Day Log</p>
                        <h2 id="daily-queue-counts-heading" class="gov-font-heading gov-report-card-title">Daily Queue Counts</h2>
                    </div>
                    <p class="gov-report-card-meta">
                        @if($dailyPeak)
                            Peak day: {{ \Carbon\Carbon::createFromFormat('Y-m-d', $dailyPeak['date'], 'Asia/Manila')->format('F j, Y') }} ({{ number_format($dailyPeak['total_tickets']) }} tickets)
                        @else
                            No queue activity recorded in the last 7 days.
                        @endif
                    </p>
                </div>

                <div class="gov-report-table-wrap mt-5 overflow-x-auto">
                    <table class="gov-report-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Activity Load</th>
                                <th class="text-right">Total Tickets</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($dailyQueueCounts as $row)
                                @php($dailyDate = \Carbon\Carbon::createFromFormat('Y-m-d', $row['date'], 'Asia/Manila'))
                                @php($dailyBarWidth = $row['total_tickets'] > 0 ? max(8, round(($row['total_tickets'] / $dailyMax) * 100, 1)) : 0)
                                <tr>
                                    <td>
                                        <div class="gov-report-table-primary">{{ $dailyDate->format('F j, Y') }}</div>
                                        <div class="gov-report-table-sub">{{ $dailyDate->format('l') }}</div>
                                    </td>
                                    <td>
                                        <div class="gov-report-activity-track" aria-hidden="true">
                                            @if($dailyBarWidth > 0)
                                                <span class="gov-report-activity-fill gov-report-activity-fill-daily" style="width: {{ $dailyBarWidth }}%;"></span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="gov-report-table-value text-right">{{ number_format($row['total_tickets']) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="gov-report-table-empty">No queue activity in the last 7 days.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            <section class="gov-report-card p-6" aria-labelledby="weekly-queue-counts-heading">
                <div class="gov-report-card-head gov-report-card-head-wide">
                    <div>
                        <p class="gov-report-card-kicker">Five-Week Ledger</p>
                        <h2 id="weekly-queue-counts-heading" class="gov-font-heading gov-report-card-title">Weekly Queue Counts</h2>
                    </div>
                    <p class="gov-report-card-meta">
                        @if($weeklyPeak)
                            Peak week: {{ 'Week ' . ltrim(substr($weeklyPeak['week'], 4), '0') . ', ' . substr($weeklyPeak['week'], 0, 4) }} ({{ number_format($weeklyPeak['total_tickets']) }} tickets)
                        @else
                            No queue activity recorded in the last 5 weeks.
                        @endif
                    </p>
                </div>

                <div class="gov-report-table-wrap mt-5 overflow-x-auto">
                    <table class="gov-report-table">
                        <thead>
                            <tr>
                                <th>Week</th>
                                <th>Activity Load</th>
                                <th class="text-right">Total Tickets</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($weeklyQueueCounts as $row)
                                @php($weekLabel = 'Week ' . ltrim(substr($row['week'], 4), '0') . ', ' . substr($row['week'], 0, 4))
                                @php($weeklyBarWidth = $row['total_tickets'] > 0 ? max(8, round(($row['total_tickets'] / $weeklyMax) * 100, 1)) : 0)
                                <tr>
                                    <td>
                                        <div class="gov-report-table-primary">{{ $weekLabel }}</div>
                                    </td>
                                    <td>
                                        <div class="gov-report-activity-track" aria-hidden="true">
                                            @if($weeklyBarWidth > 0)
                                                <span class="gov-report-activity-fill gov-report-activity-fill-weekly" style="width: {{ $weeklyBarWidth }}%;"></span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="gov-report-table-value text-right">{{ number_format($row['total_tickets']) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="gov-report-table-empty">No queue activity in the last 5 weeks.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    @else
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <section class="gov-report-card p-6" aria-labelledby="status-distribution-heading">
                <div class="gov-report-card-head">
                    <div>
                        <p class="gov-report-card-kicker">Daily Status</p>
                        <h2 id="status-distribution-heading" class="gov-font-heading gov-report-card-title">Ticket Status Distribution (Today)</h2>
                    </div>
                    <p class="gov-report-card-meta">{{ $summary['total_today'] }} total tickets logged today</p>
                </div>

                <div class="gov-report-donut-layout mt-5">
                    <div class="gov-report-donut-wrap">
                        <div class="gov-report-donut"
                             style="background: {{ $statusPieStyle }};"
                             role="img"
                             aria-label="Pie chart of ticket status distribution for today">
                            <div class="gov-report-donut-core"></div>
                            <div class="gov-report-donut-center">
                                <span class="gov-report-donut-label">Total</span>
                                <span class="gov-report-donut-value">{{ $summary['total_today'] }}</span>
                            </div>
                        </div>

                        @if(!$statusPieHasData)
                            <p class="gov-report-empty-note">No tickets recorded yet for today.</p>
                        @endif
                    </div>

                    <div>
                        <div class="gov-report-progress-track">
                            @foreach($statusBreakdown as $status)
                                @php($segmentWidth = $status['count'] > 0 ? max($status['percentage'], 1) : 0)
                                @if($segmentWidth > 0)
                                    <span
                                        class="gov-report-progress-segment {{ $status['bar_class'] }}"
                                        style="width: {{ $segmentWidth }}%;"
                                        title="{{ $status['label'] }}: {{ $status['count'] }} ({{ number_format($status['percentage'], 1) }}%)"
                                    ></span>
                                @endif
                            @endforeach
                        </div>

                        <div class="gov-report-legend-grid mt-4">
                            @foreach($statusBreakdown as $status)
                                <article class="gov-report-legend-item">
                                    <div class="gov-report-legend-item-head">
                                        <span class="gov-report-legend-label">
                                            <span class="h-2.5 w-2.5 rounded-full {{ $status['chip_class'] }}" aria-hidden="true"></span>
                                            {{ $status['label'] }}
                                        </span>
                                        <span class="gov-report-legend-value">{{ $status['count'] }}</span>
                                    </div>
                                    <p class="gov-report-legend-meta">{{ number_format($status['percentage'], 1) }}% of today&apos;s issued tickets</p>
                                </article>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            <section class="gov-report-card p-6" aria-labelledby="hourly-volume-heading">
                <div class="gov-report-card-head">
                    <div>
                        <p class="gov-report-card-kicker">Daily Pattern</p>
                        <h2 id="hourly-volume-heading" class="gov-font-heading gov-report-card-title">Hourly Ticket Volume (Today)</h2>
                    </div>
                    <p class="gov-report-card-meta">Peak activity: {{ $peakHourLabel }}</p>
                </div>

                <div class="gov-report-chart-wrap mt-5 overflow-x-auto">
                    <div class="gov-report-chart-canvas min-w-[900px]">
                        <div class="gov-report-axis-stage">
                            @foreach($hourlyTicketSeries as $hourPoint)
                                @php($barHeight = $hourPoint['count'] > 0 ? max(8, (int) round(($hourPoint['count'] / $hourlyMax) * 185)) : 8)
                                <div class="gov-report-axis-column">
                                    <div
                                        class="gov-report-chart-bar {{ $hourPoint['count'] > 0 ? 'bg-blue-700' : 'bg-slate-200' }}"
                                        style="height: {{ $barHeight }}px;"
                                        title="{{ $hourPoint['label'] }}: {{ $hourPoint['count'] }} ticket(s)"
                                    >
                                        <span class="sr-only">{{ $hourPoint['label'] }}: {{ $hourPoint['count'] }} ticket(s)</span>
                                    </div>
                                    <span class="gov-report-axis-label">{{ $hourPoint['short_label'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <div class="gov-report-annual-layout grid grid-cols-1 xl:grid-cols-2 gap-6">
            <section class="gov-report-card p-6" aria-labelledby="monthly-volume-heading">
                <div class="gov-report-card-head">
                    <div>
                        <p class="gov-report-card-kicker">Annual Volume</p>
                        <h2 id="monthly-volume-heading" class="gov-font-heading gov-report-card-title">Ticket Volume Per Month (Last 12 Months)</h2>
                    </div>
                    <p class="gov-report-card-meta">Peak month: {{ $monthlyPeakMonthLabel }}</p>
                </div>

                <div class="gov-report-chart-wrap gov-report-chart-wrap-monthly mt-5">
                    <div class="gov-report-chart-canvas gov-report-chart-canvas-monthly">
                        <div class="gov-report-axis-stage gov-report-axis-stage-compact gov-report-axis-stage-monthly">
                            @foreach($monthlyVolumeSeries as $monthPoint)
                                @php($monthBarHeight = $monthPoint['total'] > 0 ? max(8, (int) round(($monthPoint['total'] / $monthlyVolumeMax) * 185)) : 8)
                                <div class="gov-report-axis-column gov-report-axis-column-compact gov-report-axis-column-monthly">
                                    <div
                                        class="gov-report-chart-bar gov-report-chart-bar-secondary {{ $monthPoint['total'] > 0 ? 'bg-amber-500' : 'bg-slate-200' }}"
                                        style="height: {{ $monthBarHeight }}px;"
                                        title="{{ $monthPoint['label'] }}: {{ $monthPoint['total'] }} ticket(s)"
                                    >
                                        <span class="sr-only">{{ $monthPoint['label'] }}: {{ $monthPoint['total'] }} ticket(s)</span>
                                    </div>
                                    <span class="gov-report-axis-label">{{ $monthPoint['short_label'] }}</span>
                                    <span class="gov-report-axis-year">{{ $monthPoint['year_short'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>

            <section class="gov-report-card gov-report-monthly-status-card p-6" aria-labelledby="monthly-status-heading">
                <div class="gov-report-card-head gov-report-card-head-wide">
                    <div>
                        <p class="gov-report-card-kicker">Monthly Performance</p>
                        <h2 id="monthly-status-heading" class="gov-font-heading gov-report-card-title">Status Per Month (Last 12 Months)</h2>
                    </div>
                    <div class="gov-report-pill-row">
                        @foreach($monthlyStatusLegend as $legend)
                            <span class="gov-report-pill">
                                <span class="h-2 w-2 rounded-full {{ $legend['chip_class'] }}" aria-hidden="true"></span>
                                {{ $legend['label'] }}
                            </span>
                        @endforeach
                    </div>
                </div>

                <div class="gov-report-month-list gov-report-month-list-landscape mt-5">
                    @foreach($monthlyStatusSeries as $monthRow)
                        <article class="gov-report-month-row">
                            <div class="gov-report-month-row-head">
                                <span class="gov-report-month-row-label">{{ $monthRow['label'] }}</span>
                                <span class="gov-report-month-row-total">{{ $monthRow['total'] }} total</span>
                            </div>
                            <div class="gov-report-progress-track mt-2.5">
                                @if($monthRow['total'] > 0)
                                    @foreach($monthRow['segments'] as $segment)
                                        @php($segmentWidth = $segment['count'] > 0 ? max($segment['percentage'], 1) : 0)
                                        @if($segmentWidth > 0)
                                            <span
                                                class="gov-report-progress-segment {{ $segment['bar_class'] }}"
                                                style="width: {{ $segmentWidth }}%;"
                                                title="{{ $segment['label'] }}: {{ $segment['count'] }} ({{ number_format($segment['percentage'], 1) }}%)"
                                            ></span>
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        </div>
    @endif
</div>

@once
    <style>
        .gov-report-shell {
            --gov-report-blue-950: #0a2d55;
            --gov-report-blue-900: #154777;
            --gov-report-blue-800: #2a5f97;
            --gov-report-blue-100: #dce8f6;
            --gov-report-gold-500: #b98a2b;
            --gov-report-emerald-600: #0f8a62;
            --gov-report-ink-900: #17283f;
            --gov-report-ink-700: #48607f;
            --gov-report-ink-500: #6f849f;
            --gov-report-border: #d6e1ee;
            position: relative;
            display: grid;
            gap: 1.5rem;
        }

        .gov-report-shell::before,
        .gov-report-shell::after {
            content: '';
            position: absolute;
            border-radius: 999px;
            pointer-events: none;
            opacity: 0.42;
            z-index: 0;
        }

        .gov-report-shell::before {
            width: 16rem;
            height: 16rem;
            top: 0.5rem;
            right: -2rem;
            background: radial-gradient(circle at 35% 35%, rgb(220 232 246 / 0.92), transparent 68%);
        }

        .gov-report-shell::after {
            width: 13rem;
            height: 13rem;
            left: -1.5rem;
            bottom: 2rem;
            background: radial-gradient(circle at 50% 50%, rgb(247 239 220 / 0.9), transparent 70%);
        }

        .gov-report-shell > * {
            position: relative;
            z-index: 1;
        }

        .gov-report-masthead {
            overflow: hidden;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1.2rem;
            border: 1px solid #c8d7e9;
            border-radius: 1.45rem;
            padding: 1.35rem 1.45rem;
            background:
                radial-gradient(circle at top right, rgb(255 255 255 / 0.16), transparent 40%),
                linear-gradient(125deg, var(--gov-report-blue-950), var(--gov-report-blue-900));
            color: #fff;
            box-shadow: 0 26px 42px -34px rgb(10 45 85 / 0.5);
        }

        .gov-report-masthead::after {
            content: '';
            position: absolute;
            inset: auto 0 0;
            height: 0.3rem;
            background: linear-gradient(90deg, #1d4ed8 0%, #1d4ed8 62%, #b98a2b 62%, #b98a2b 82%, #be123c 82%, #be123c 100%);
        }

        .gov-report-banner {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .gov-report-seal-wrap {
            width: 4.5rem;
            height: 4.5rem;
            padding: 0.34rem;
            border-radius: 999px;
            background: rgb(255 255 255 / 0.14);
            border: 1px solid rgb(255 255 255 / 0.24);
            box-shadow: inset 0 0 0 1px rgb(255 255 255 / 0.08);
            flex-shrink: 0;
        }

        .gov-report-seal {
            width: 100%;
            height: 100%;
            border-radius: 999px;
            object-fit: cover;
            background: rgb(255 255 255 / 0.9);
        }

        .gov-report-kicker {
            margin: 0;
            font-size: 0.72rem;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            font-weight: 800;
            color: rgb(219 234 254 / 0.98);
        }

        .gov-report-title {
            margin: 0.35rem 0 0;
            font-size: clamp(1.7rem, 2.5vw, 2.35rem);
            line-height: 1.06;
            color: #fff;
        }

        .gov-report-copy {
            margin: 0.5rem 0 0;
            max-width: 42rem;
            font-size: 0.95rem;
            line-height: 1.65;
            color: rgb(226 232 240 / 0.94);
        }

        .gov-report-meta {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 0.55rem;
        }

        .gov-report-chip {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            border: 1px solid rgb(255 255 255 / 0.18);
            background: rgb(255 255 255 / 0.12);
            padding: 0.44rem 0.8rem;
            color: #fff;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .gov-report-chip-strong {
            background: rgb(21 71 119 / 0.42);
        }

        .gov-report-chip-accent {
            background: rgb(185 138 43 / 0.26);
            border-color: rgb(247 239 220 / 0.36);
        }

        .gov-report-card {
            border: 1px solid var(--gov-report-border);
            border-radius: 1.35rem;
            background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
            box-shadow: 0 22px 36px -34px rgb(15 63 115 / 0.34);
        }

        .gov-report-summary-card {
            background:
                radial-gradient(circle at top right, rgb(220 232 246 / 0.62), transparent 42%),
                linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
        }

        .gov-report-card-head {
            display: grid;
            gap: 0.4rem;
        }

        .gov-report-card-head-wide {
            grid-template-columns: minmax(0, 1fr) minmax(15rem, 19rem);
            align-items: end;
            gap: 1rem;
        }

        .gov-report-card-kicker {
            margin: 0;
            font-size: 0.72rem;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            font-weight: 800;
            color: var(--gov-report-blue-800);
        }

        .gov-report-card-title {
            margin: 0.28rem 0 0;
            color: var(--gov-report-ink-900);
            font-size: 1.32rem;
            line-height: 1.2;
        }

        .gov-report-card-meta {
            margin: 0;
            color: var(--gov-report-ink-500);
            font-size: 0.9rem;
            line-height: 1.6;
            text-align: right;
        }

        .gov-report-summary-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 1rem;
        }

        .gov-report-stat {
            position: relative;
            overflow: hidden;
            border: 1px solid #dbe5f0;
            border-radius: 1.1rem;
            padding: 1.1rem 1.05rem;
            background: linear-gradient(180deg, #ffffff 0%, #f9fbfe 100%);
        }

        .gov-report-stat::after {
            content: '';
            position: absolute;
            inset: auto 0 0;
            height: 0.24rem;
            background: linear-gradient(90deg, rgb(21 71 119 / 0.2), rgb(21 71 119 / 0.65));
        }

        .gov-report-stat-success {
            background:
                radial-gradient(circle at top right, rgb(217 244 234 / 0.72), transparent 42%),
                linear-gradient(180deg, #ffffff 0%, #f8fdfb 100%);
            border-color: rgb(173 218 202 / 0.9);
        }

        .gov-report-stat-success::after {
            background: linear-gradient(90deg, rgb(15 138 98 / 0.2), rgb(15 138 98 / 0.7));
        }

        .gov-report-stat-accent {
            background:
                radial-gradient(circle at top right, rgb(220 232 246 / 0.82), transparent 42%),
                linear-gradient(180deg, #ffffff 0%, #f7fbff 100%);
            border-color: rgb(182 205 234 / 0.95);
        }

        .gov-report-stat-accent::after {
            background: linear-gradient(90deg, rgb(42 95 151 / 0.2), rgb(42 95 151 / 0.76));
        }

        .gov-report-stat-label {
            margin: 0;
            font-size: 0.74rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            font-weight: 800;
            color: var(--gov-report-ink-500);
        }

        .gov-report-stat-value {
            margin: 0.6rem 0 0;
            font-size: 2.15rem;
            line-height: 1;
            font-weight: 800;
            color: var(--gov-report-ink-900);
        }

        .gov-report-stat-note {
            margin: 0.65rem 0 0;
            font-size: 0.84rem;
            line-height: 1.55;
            color: var(--gov-report-ink-700);
        }

        .gov-report-stat-success .gov-report-stat-label,
        .gov-report-stat-success .gov-report-stat-value {
            color: #0f684e;
        }

        .gov-report-stat-accent .gov-report-stat-label,
        .gov-report-stat-accent .gov-report-stat-value {
            color: var(--gov-report-blue-900);
        }

        .gov-report-donut-layout {
            display: grid;
            grid-template-columns: minmax(0, 220px) minmax(0, 1fr);
            align-items: center;
            gap: 1.25rem;
        }

        .gov-report-donut-wrap {
            display: grid;
            justify-items: center;
            gap: 0.65rem;
        }

        .gov-report-donut {
            position: relative;
            width: 11rem;
            height: 11rem;
            border-radius: 999px;
            border: 1px solid #dbe5f0;
            box-shadow: 0 18px 28px -24px rgb(15 63 115 / 0.48);
        }

        .gov-report-donut-core {
            position: absolute;
            inset: 26%;
            border-radius: 999px;
            border: 1px solid #eef3f9;
            background: #fff;
        }

        .gov-report-donut-center {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .gov-report-donut-label {
            font-size: 0.7rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            font-weight: 800;
            color: var(--gov-report-ink-500);
        }

        .gov-report-donut-value {
            margin-top: 0.25rem;
            font-size: 1.9rem;
            line-height: 1;
            font-weight: 800;
            color: var(--gov-report-ink-900);
        }

        .gov-report-empty-note {
            margin: 0;
            color: var(--gov-report-ink-500);
            font-size: 0.8rem;
            line-height: 1.45;
            text-align: center;
        }

        .gov-report-legend-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.75rem;
        }

        .gov-report-legend-item {
            border: 1px solid #dbe5f0;
            border-radius: 1rem;
            background: linear-gradient(180deg, #ffffff 0%, #f9fbfe 100%);
            padding: 0.85rem 0.9rem;
        }

        .gov-report-legend-item-head {
            display: flex;
            align-items: flex-start;
            justify-content: flex-start;
            gap: 0.8rem;
        }

        .gov-report-legend-label {
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            color: var(--gov-report-ink-700);
            font-size: 0.9rem;
            font-weight: 700;
            min-width: 0;
            flex: 1 1 auto;
            line-height: 1.35;
        }

        .gov-report-legend-value {
            color: var(--gov-report-ink-900);
            font-size: 0.95rem;
            font-weight: 800;
            flex: 0 0 auto;
            margin-left: auto;
            line-height: 1.2;
        }

        .gov-report-legend-meta {
            margin: 0.35rem 0 0;
            color: var(--gov-report-ink-500);
            font-size: 0.78rem;
            line-height: 1.45;
        }

        .gov-report-bar-row {
            display: grid;
            gap: 0.55rem;
        }

        .gov-report-bar-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            color: var(--gov-report-ink-500);
            font-size: 0.8rem;
        }

        .gov-report-bar-label {
            color: var(--gov-report-ink-700);
            font-weight: 700;
        }

        .gov-report-bar-value {
            font-weight: 700;
        }

        .gov-report-bar-track {
            height: 0.9rem;
            overflow: hidden;
            border-radius: 999px;
            background: #e7eef7;
            box-shadow: inset 0 1px 2px rgb(15 63 115 / 0.08);
        }

        .gov-report-bar-fill {
            display: block;
            height: 100%;
            border-radius: 999px;
        }

        .gov-report-activity-track {
            display: flex;
            height: 0.95rem;
            overflow: hidden;
            border-radius: 999px;
            background: #e7eef7;
            box-shadow: inset 0 1px 2px rgb(15 63 115 / 0.08);
        }

        .gov-report-activity-fill {
            display: block;
            height: 100%;
            border-radius: 999px;
        }

        .gov-report-activity-fill-daily {
            background: linear-gradient(90deg, #285f96 0%, #1d4ed8 100%);
        }

        .gov-report-activity-fill-weekly {
            background: linear-gradient(90deg, #a9771d 0%, #d4a335 100%);
        }

        .gov-report-progress-track {
            display: flex;
            height: 0.9rem;
            overflow: hidden;
            border-radius: 999px;
            background: #e7eef7;
            box-shadow: inset 0 1px 2px rgb(15 63 115 / 0.08);
        }

        .gov-report-progress-segment {
            display: block;
            height: 100%;
        }

        .gov-report-chart-wrap {
            overflow: hidden;
            border: 1px solid #dbe5f0;
            border-radius: 1.15rem;
            background: linear-gradient(180deg, #f9fbfe 0%, #ffffff 100%);
        }

        .gov-report-chart-canvas {
            padding: 1rem 0.9rem 0.85rem;
        }

        .gov-report-chart-wrap-monthly {
            overflow: hidden;
        }

        .gov-report-chart-canvas-monthly {
            width: 100%;
            min-width: 0;
            padding-right: 0.7rem;
        }

        .gov-report-axis-stage {
            display: flex;
            align-items: flex-end;
            gap: 0.55rem;
            height: 15.25rem;
            padding: 0 0.25rem 0.3rem;
            border-left: 1px solid #d6e1ee;
            border-bottom: 1px solid #d6e1ee;
            background: repeating-linear-gradient(
                to top,
                transparent 0 31px,
                rgb(148 163 184 / 0.14) 31px 32px
            );
        }

        .gov-report-axis-stage-compact {
            gap: 0.7rem;
        }

        .gov-report-axis-stage-monthly {
            gap: 0.35rem;
        }

        .gov-report-axis-column {
            display: flex;
            min-width: 24px;
            flex: 1;
            flex-direction: column;
            align-items: center;
            justify-content: flex-end;
            gap: 0.2rem;
        }

        .gov-report-axis-column-compact {
            min-width: 34px;
        }

        .gov-report-axis-column-monthly {
            min-width: 0;
        }

        .gov-report-chart-bar {
            width: 100%;
            border-radius: 0.65rem 0.65rem 0 0;
            box-shadow: 0 12px 18px -16px rgb(15 63 115 / 0.75);
        }

        .gov-report-chart-bar-secondary {
            box-shadow: 0 12px 18px -16px rgb(185 138 43 / 0.78);
        }

        .gov-report-axis-label {
            color: var(--gov-report-ink-500);
            font-size: 0.62rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }

        .gov-report-axis-year {
            color: #9aa7b8;
            font-size: 0.6rem;
        }

        .gov-report-pill-row {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 0.45rem;
        }

        .gov-report-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            border-radius: 999px;
            border: 1px solid #dbe5f0;
            background: #fff;
            padding: 0.35rem 0.7rem;
            color: var(--gov-report-ink-700);
            font-size: 0.72rem;
            font-weight: 700;
        }

        .gov-report-month-list {
            display: grid;
            gap: 0.8rem;
        }

        .gov-report-annual-layout {
            align-items: start;
        }

        .gov-report-monthly-status-card {
            background:
                radial-gradient(circle at top right, rgb(220 232 246 / 0.5), transparent 44%),
                linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
        }

        .gov-report-month-list-landscape {
            grid-template-columns: repeat(2, minmax(0, 1fr));
            align-items: start;
        }

        .gov-report-month-row {
            border: 1px solid #dbe5f0;
            border-radius: 1rem;
            background: linear-gradient(180deg, #ffffff 0%, #f9fbfe 100%);
            padding: 0.9rem 0.95rem;
        }

        .gov-report-month-row-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            color: var(--gov-report-ink-500);
            font-size: 0.8rem;
        }

        .gov-report-month-row-label {
            color: var(--gov-report-ink-700);
            font-weight: 700;
        }

        .gov-report-month-row-total {
            font-weight: 700;
        }

        .gov-report-table-wrap {
            overflow: hidden;
            border: 1px solid #dbe5f0;
            border-radius: 1.15rem;
            background: #fff;
        }

        .gov-report-table {
            width: 100%;
            border-collapse: collapse;
        }

        .gov-report-table thead {
            background: linear-gradient(180deg, #f6f9fc 0%, #eef4fb 100%);
        }

        .gov-report-table th {
            padding: 0.88rem 1rem;
            color: var(--gov-report-ink-500);
            font-size: 0.74rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            font-weight: 800;
            text-align: left;
        }

        .gov-report-table td {
            padding: 0.9rem 1rem;
            border-top: 1px solid #edf2f7;
            color: var(--gov-report-ink-700);
            font-size: 0.92rem;
        }

        .gov-report-table-primary {
            color: var(--gov-report-ink-900);
            font-weight: 700;
        }

        .gov-report-table-sub {
            margin-top: 0.18rem;
            color: var(--gov-report-ink-500);
            font-size: 0.75rem;
        }

        .gov-report-table-value {
            color: var(--gov-report-blue-900);
            font-weight: 800;
        }

        .gov-report-table-empty {
            text-align: center;
            color: var(--gov-report-ink-500);
        }

        @media (max-width: 1100px) {
            .gov-report-summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 900px) {
            .gov-report-card-head-wide,
            .gov-report-donut-layout {
                grid-template-columns: 1fr;
            }

            .gov-report-card-meta,
            .gov-report-pill-row {
                text-align: left;
                justify-content: flex-start;
            }
        }

        @media (max-width: 1280px) {
            .gov-report-month-list-landscape {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 780px) {
            .gov-report-masthead,
            .gov-report-card {
                border-radius: 1.15rem;
            }

            .gov-report-masthead {
                padding: 1.05rem;
            }

            .gov-report-banner {
                align-items: flex-start;
            }

            .gov-report-summary-grid,
            .gov-report-legend-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .gov-report-banner {
                flex-direction: column;
            }

            .gov-report-seal-wrap {
                width: 4rem;
                height: 4rem;
            }

            .gov-report-title {
                font-size: 1.55rem;
            }

            .gov-report-card-title {
                font-size: 1.18rem;
            }

            .gov-report-stat-value {
                font-size: 1.85rem;
            }
        }
    </style>
@endonce
