@php($queueReportsPdfUrl = auth()->user()?->isSuperAdmin() ? route('super-admin.queue-reports.pdf') : route('office.queue-reports.pdf', $office->slug))
@php($manilaNow = now('Asia/Manila'))
@php($dailyTotal = (int) collect($queueReportDailyCounts)->sum('total_tickets'))
@php($weeklyTotal = (int) collect($queueReportWeeklyCounts)->sum('total_tickets'))
@php($dailyMax = max(1, (int) collect($queueReportDailyCounts)->max('total_tickets')))
@php($weeklyMax = max(1, (int) collect($queueReportWeeklyCounts)->max('total_tickets')))
@php($dailyPeak = collect($queueReportDailyCounts)->sortByDesc('total_tickets')->first())
@php($weeklyPeak = collect($queueReportWeeklyCounts)->sortByDesc('total_tickets')->first())
@php($servedTotal = (int) ($queueReportStatusSummary['served'] ?? 0))
@php($skippedTotal = (int) ($queueReportStatusSummary['skipped'] ?? 0))
@php($serviceOutcomeTotal = $servedTotal + $skippedTotal)
@php($servedShare = $serviceOutcomeTotal > 0 ? round(($servedTotal / $serviceOutcomeTotal) * 100, 1) : 0)
@php($skippedShare = $serviceOutcomeTotal > 0 ? round(($skippedTotal / $serviceOutcomeTotal) * 100, 1) : 0)
@php($queueReportDescription = auth()->user()?->isSuperAdmin()
    ? 'Official inter-office queue activity record used for municipal service oversight and executive review.'
    : 'Official office queue activity record used for staff monitoring, document preparation, and daily administrative review.')

<div class="gov-queue-report-shell space-y-6">
    <section id="queue-reports-printable" class="gov-queue-report-masthead" aria-labelledby="queue-reports-heading">
        <div class="gov-queue-report-banner">
            <div class="gov-queue-report-seal-wrap">
                <img src="{{ asset('images/lgu-logo.png') }}" alt="Municipality of Manolo Fortich official seal" class="gov-queue-report-seal">
            </div>

            <div>
                <p class="gov-queue-report-kicker">Republic of the Philippines</p>
                <h2 id="queue-reports-heading" class="gov-font-heading gov-queue-report-title">Queue Activity Record</h2>
                <p class="gov-queue-report-copy">{{ $queueReportDescription }}</p>
            </div>
        </div>

        <div class="gov-queue-report-masthead-side">
            <div class="gov-queue-report-chip-row" aria-label="Report metadata">
                <span class="gov-queue-report-chip gov-queue-report-chip-strong">Official Record</span>
                <span class="gov-queue-report-chip">Scope: {{ $queueReportScopeLabel }}</span>
                <span class="gov-queue-report-chip gov-queue-report-chip-accent">{{ $manilaNow->format('F j, Y') }}</span>
            </div>

            <a href="{{ $queueReportsPdfUrl }}" data-no-print="true" class="gov-queue-report-btn">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V3h12v6" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18h12v3H6z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 14H4a2 2 0 0 1-2-2v-1a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v1a2 2 0 0 1-2 2h-2" />
                </svg>
                Print PDF
            </a>
        </div>
    </section>

    <section class="gov-queue-report-card gov-queue-report-summary-card p-6" aria-labelledby="queue-reports-summary-heading">
        <div class="gov-queue-report-head">
            <div>
                <p class="gov-queue-report-card-kicker">Executive Snapshot</p>
                <h3 id="queue-reports-summary-heading" class="gov-font-heading gov-queue-report-card-title">Queue Performance Overview</h3>
            </div>
            <p class="gov-queue-report-card-meta">Consolidated queue counts and service outcomes for the current reporting scope.</p>
        </div>

        <div class="gov-queue-report-summary-grid mt-5">
            <article class="gov-queue-report-stat">
                <p class="gov-queue-report-stat-label">Last 7 Days</p>
                <p class="gov-queue-report-stat-value">{{ number_format($dailyTotal) }}</p>
                <p class="gov-queue-report-stat-note">Tickets issued across {{ count($queueReportDailyCounts) }} active day(s).</p>
            </article>

            <article class="gov-queue-report-stat gov-queue-report-stat-blue">
                <p class="gov-queue-report-stat-label">Last 5 Weeks</p>
                <p class="gov-queue-report-stat-value">{{ number_format($weeklyTotal) }}</p>
                <p class="gov-queue-report-stat-note">Recorded volume for the recent weekly reporting window.</p>
            </article>

            <article class="gov-queue-report-stat gov-queue-report-stat-green">
                <p class="gov-queue-report-stat-label">Served</p>
                <p class="gov-queue-report-stat-value">{{ number_format($servedTotal) }}</p>
                <p class="gov-queue-report-stat-note">{{ number_format($servedShare, 1) }}% of processed tickets were completed.</p>
            </article>

            <article class="gov-queue-report-stat gov-queue-report-stat-gold">
                <p class="gov-queue-report-stat-label">Avg. Processing Time</p>
                <p class="gov-queue-report-stat-value gov-queue-report-stat-value-tight">{{ $queueReportAverageProcessingTime }}</p>
                <p class="gov-queue-report-stat-note">Average handling time per completed queue transaction.</p>
            </article>
        </div>
    </section>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <section class="gov-queue-report-card p-6" aria-labelledby="daily-queue-counts-heading">
            <div class="gov-queue-report-head">
                <div>
                    <p class="gov-queue-report-card-kicker">Seven-Day Log</p>
                    <h3 id="daily-queue-counts-heading" class="gov-font-heading gov-queue-report-card-title">Daily Queue Counts</h3>
                </div>
                <p class="gov-queue-report-card-meta">
                    @if($dailyPeak)
                        Peak day: {{ \Carbon\Carbon::createFromFormat('Y-m-d', $dailyPeak['date'], 'Asia/Manila')->format('F j, Y') }} ({{ number_format($dailyPeak['total_tickets']) }} tickets)
                    @else
                        No queue activity recorded in the last 7 days.
                    @endif
                </p>
            </div>

            <div class="gov-queue-report-table-wrap mt-5 overflow-x-auto">
                <table class="gov-queue-report-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Activity Load</th>
                            <th class="text-right">Total Tickets</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($queueReportDailyCounts as $row)
                            @php($dailyDate = \Carbon\Carbon::createFromFormat('Y-m-d', $row['date'], 'Asia/Manila'))
                            @php($dailyBarWidth = $row['total_tickets'] > 0 ? max(8, round(($row['total_tickets'] / $dailyMax) * 100, 1)) : 0)
                            <tr>
                                <td>
                                    <div class="gov-queue-report-table-primary">{{ $dailyDate->format('F j, Y') }}</div>
                                    <div class="gov-queue-report-table-sub">{{ $dailyDate->format('l') }}</div>
                                </td>
                                <td>
                                    <div class="gov-queue-report-bar-track" aria-hidden="true">
                                        @if($dailyBarWidth > 0)
                                            <span class="gov-queue-report-bar-fill gov-queue-report-bar-fill-daily" style="width: {{ $dailyBarWidth }}%;"></span>
                                        @endif
                                    </div>
                                </td>
                                <td class="gov-queue-report-table-value text-right">{{ number_format($row['total_tickets']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="gov-queue-report-table-empty">No queue activity in the last 7 days.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="gov-queue-report-card p-6" aria-labelledby="weekly-queue-counts-heading">
            <div class="gov-queue-report-head">
                <div>
                    <p class="gov-queue-report-card-kicker">Five-Week Ledger</p>
                    <h3 id="weekly-queue-counts-heading" class="gov-font-heading gov-queue-report-card-title">Weekly Queue Counts</h3>
                </div>
                <p class="gov-queue-report-card-meta">
                    @if($weeklyPeak)
                        Peak week: {{ 'Week ' . ltrim(substr($weeklyPeak['week'], 4), '0') . ', ' . substr($weeklyPeak['week'], 0, 4) }} ({{ number_format($weeklyPeak['total_tickets']) }} tickets)
                    @else
                        No queue activity recorded in the last 5 weeks.
                    @endif
                </p>
            </div>

            <div class="gov-queue-report-table-wrap mt-5 overflow-x-auto">
                <table class="gov-queue-report-table">
                    <thead>
                        <tr>
                            <th>Week</th>
                            <th>Activity Load</th>
                            <th class="text-right">Total Tickets</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($queueReportWeeklyCounts as $row)
                            @php($weekLabel = 'Week ' . ltrim(substr($row['week'], 4), '0') . ', ' . substr($row['week'], 0, 4))
                            @php($weeklyBarWidth = $row['total_tickets'] > 0 ? max(8, round(($row['total_tickets'] / $weeklyMax) * 100, 1)) : 0)
                            <tr>
                                <td>
                                    <div class="gov-queue-report-table-primary">{{ $weekLabel }}</div>
                                    <div class="gov-queue-report-table-sub">ISO week {{ $row['week'] }}</div>
                                </td>
                                <td>
                                    <div class="gov-queue-report-bar-track" aria-hidden="true">
                                        @if($weeklyBarWidth > 0)
                                            <span class="gov-queue-report-bar-fill gov-queue-report-bar-fill-weekly" style="width: {{ $weeklyBarWidth }}%;"></span>
                                        @endif
                                    </div>
                                </td>
                                <td class="gov-queue-report-table-value text-right">{{ number_format($row['total_tickets']) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="gov-queue-report-table-empty">No queue activity in the last 5 weeks.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <section class="gov-queue-report-card p-6" aria-labelledby="status-summary-heading">
            <div class="gov-queue-report-head">
                <div>
                    <p class="gov-queue-report-card-kicker">Completion Ledger</p>
                    <h3 id="status-summary-heading" class="gov-font-heading gov-queue-report-card-title">Status Summary</h3>
                </div>
                <p class="gov-queue-report-card-meta">{{ number_format($serviceOutcomeTotal) }} processed ticket(s) recorded in this scope.</p>
            </div>

            <div class="gov-queue-report-status-grid mt-5">
                <article class="gov-queue-report-status-stat gov-queue-report-status-stat-served">
                    <p class="gov-queue-report-status-label">Served</p>
                    <p class="gov-queue-report-status-value">{{ number_format($servedTotal) }}</p>
                    <p class="gov-queue-report-status-note">{{ number_format($servedShare, 1) }}% completion share</p>
                </article>

                <article class="gov-queue-report-status-stat gov-queue-report-status-stat-skipped">
                    <p class="gov-queue-report-status-label">Skipped</p>
                    <p class="gov-queue-report-status-value">{{ number_format($skippedTotal) }}</p>
                    <p class="gov-queue-report-status-note">{{ number_format($skippedShare, 1) }}% non-service share</p>
                </article>
            </div>

            <div class="gov-queue-report-status-bar mt-5" aria-hidden="true">
                @if($serviceOutcomeTotal > 0)
                    <span class="gov-queue-report-status-bar-served" style="width: {{ $servedShare }}%;"></span>
                    @if($skippedTotal > 0)
                        <span class="gov-queue-report-status-bar-skipped" style="width: {{ $skippedShare }}%;"></span>
                    @endif
                @endif
            </div>
        </section>

        <section class="gov-queue-report-card gov-queue-report-time-card p-6" aria-labelledby="average-processing-heading">
            <div class="gov-queue-report-head">
                <div>
                    <p class="gov-queue-report-card-kicker">Service Efficiency</p>
                    <h3 id="average-processing-heading" class="gov-font-heading gov-queue-report-card-title">Average Processing Time</h3>
                </div>
                <p class="gov-queue-report-card-meta">Computed from completed tickets with valid call and completion timestamps.</p>
            </div>

            <div class="gov-queue-report-time-panel mt-5">
                <p class="gov-queue-report-time-label">Current Average</p>
                <p class="gov-queue-report-time-value">{{ $queueReportAverageProcessingTime }}</p>
                <p class="gov-queue-report-time-note">Average elapsed handling time per completed queue transaction in the selected reporting scope.</p>
            </div>
        </section>
    </div>
</div>

@once
    <style>
        .gov-queue-report-shell {
            --gov-queue-report-blue-950: #0a2d55;
            --gov-queue-report-blue-900: #154777;
            --gov-queue-report-blue-800: #2a5f97;
            --gov-queue-report-gold-500: #b98a2b;
            --gov-queue-report-emerald-600: #0f8a62;
            --gov-queue-report-rose-600: #be123c;
            --gov-queue-report-ink-900: #17283f;
            --gov-queue-report-ink-700: #48607f;
            --gov-queue-report-ink-500: #6f849f;
            --gov-queue-report-border: #d6e1ee;
            position: relative;
            display: grid;
            gap: 1.5rem;
        }

        .gov-queue-report-shell::before,
        .gov-queue-report-shell::after {
            content: '';
            position: absolute;
            border-radius: 999px;
            pointer-events: none;
            z-index: 0;
            opacity: 0.45;
        }

        .gov-queue-report-shell::before {
            width: 17rem;
            height: 17rem;
            top: 0.5rem;
            right: -2rem;
            background: radial-gradient(circle at 35% 35%, rgb(220 232 246 / 0.94), transparent 68%);
        }

        .gov-queue-report-shell::after {
            width: 14rem;
            height: 14rem;
            left: -1.5rem;
            bottom: 1rem;
            background: radial-gradient(circle at 50% 50%, rgb(246 236 209 / 0.88), transparent 70%);
        }

        .gov-queue-report-shell > * {
            position: relative;
            z-index: 1;
        }

        .gov-queue-report-masthead,
        .gov-queue-report-card {
            border: 1px solid var(--gov-queue-report-border);
            border-radius: 1.35rem;
            box-shadow: 0 22px 36px -34px rgb(15 63 115 / 0.34);
            animation: gov-queue-report-fade-up 420ms ease-out both;
        }

        .gov-queue-report-masthead {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1.25rem;
            padding: 1.35rem 1.45rem;
            overflow: hidden;
            color: #fff;
            background:
                radial-gradient(circle at top right, rgb(255 255 255 / 0.16), transparent 40%),
                linear-gradient(125deg, var(--gov-queue-report-blue-950), var(--gov-queue-report-blue-900));
            box-shadow: 0 26px 42px -34px rgb(10 45 85 / 0.5);
        }

        .gov-queue-report-masthead::after {
            content: '';
            position: absolute;
            inset: auto 0 0;
            height: 0.3rem;
            background: linear-gradient(90deg, #1d4ed8 0%, #1d4ed8 62%, #b98a2b 62%, #b98a2b 82%, #be123c 82%, #be123c 100%);
        }

        .gov-queue-report-banner {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .gov-queue-report-seal-wrap {
            width: 4.65rem;
            height: 4.65rem;
            padding: 0.34rem;
            border-radius: 999px;
            background: rgb(255 255 255 / 0.14);
            border: 1px solid rgb(255 255 255 / 0.24);
            flex-shrink: 0;
        }

        .gov-queue-report-seal {
            width: 100%;
            height: 100%;
            border-radius: 999px;
            object-fit: cover;
            background: rgb(255 255 255 / 0.92);
        }

        .gov-queue-report-kicker,
        .gov-queue-report-card-kicker,
        .gov-queue-report-stat-label,
        .gov-queue-report-status-label,
        .gov-queue-report-time-label {
            margin: 0;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.14em;
            text-transform: uppercase;
        }

        .gov-queue-report-kicker {
            color: rgb(219 234 254 / 0.98);
        }

        .gov-queue-report-title {
            margin: 0.35rem 0 0;
            font-size: clamp(1.7rem, 2.5vw, 2.3rem);
            line-height: 1.06;
            color: #fff;
        }

        .gov-queue-report-copy {
            margin: 0.5rem 0 0;
            max-width: 42rem;
            font-size: 0.95rem;
            line-height: 1.65;
            color: rgb(226 232 240 / 0.94);
        }

        .gov-queue-report-masthead-side {
            display: grid;
            justify-items: end;
            gap: 0.85rem;
        }

        .gov-queue-report-chip-row {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 0.55rem;
        }

        .gov-queue-report-chip {
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

        .gov-queue-report-chip-strong {
            background: rgb(21 71 119 / 0.42);
        }

        .gov-queue-report-chip-accent {
            background: rgb(185 138 43 / 0.26);
            border-color: rgb(247 239 220 / 0.36);
        }

        .gov-queue-report-btn {
            min-height: 2.9rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            border-radius: 0.9rem;
            border: 1px solid rgb(255 255 255 / 0.22);
            background: linear-gradient(180deg, #1d4ed8 0%, #1840b3 100%);
            padding: 0.7rem 1rem;
            color: #fff;
            font-size: 0.9rem;
            font-weight: 700;
            transition: transform 160ms ease, filter 160ms ease;
        }

        .gov-queue-report-btn:hover {
            transform: translateY(-1px);
            filter: brightness(1.02);
        }

        .gov-queue-report-btn:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgb(255 255 255 / 0.22), 0 0 0 6px rgb(30 64 175 / 0.45);
        }

        .gov-queue-report-card {
            background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
        }

        .gov-queue-report-summary-card {
            background:
                radial-gradient(circle at top right, rgb(220 232 246 / 0.62), transparent 42%),
                linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
        }

        .gov-queue-report-head {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(15rem, 19rem);
            align-items: end;
            gap: 1rem;
        }

        .gov-queue-report-card-kicker {
            color: var(--gov-queue-report-blue-800);
        }

        .gov-queue-report-card-title {
            margin: 0.28rem 0 0;
            color: var(--gov-queue-report-ink-900);
            font-size: 1.32rem;
            line-height: 1.2;
        }

        .gov-queue-report-card-meta,
        .gov-queue-report-stat-note,
        .gov-queue-report-status-note,
        .gov-queue-report-time-note,
        .gov-queue-report-table-sub {
            color: var(--gov-queue-report-ink-500);
        }

        .gov-queue-report-card-meta {
            margin: 0;
            font-size: 0.9rem;
            line-height: 1.6;
            text-align: right;
        }

        .gov-queue-report-summary-grid,
        .gov-queue-report-status-grid {
            display: grid;
            gap: 1rem;
        }

        .gov-queue-report-summary-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr));
        }

        .gov-queue-report-status-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .gov-queue-report-stat,
        .gov-queue-report-status-stat,
        .gov-queue-report-time-panel,
        .gov-queue-report-table-wrap {
            border: 1px solid #dbe5f0;
            border-radius: 1.1rem;
            background: linear-gradient(180deg, #ffffff 0%, #f9fbfe 100%);
        }

        .gov-queue-report-stat,
        .gov-queue-report-status-stat {
            position: relative;
            overflow: hidden;
            padding: 1.1rem 1.05rem;
        }

        .gov-queue-report-stat::after {
            content: '';
            position: absolute;
            inset: auto 0 0;
            height: 0.24rem;
            background: linear-gradient(90deg, rgb(21 71 119 / 0.2), rgb(21 71 119 / 0.65));
        }

        .gov-queue-report-stat-blue {
            background:
                radial-gradient(circle at top right, rgb(220 232 246 / 0.82), transparent 42%),
                linear-gradient(180deg, #ffffff 0%, #f7fbff 100%);
            border-color: rgb(182 205 234 / 0.95);
        }

        .gov-queue-report-stat-green {
            background:
                radial-gradient(circle at top right, rgb(217 244 234 / 0.72), transparent 42%),
                linear-gradient(180deg, #ffffff 0%, #f8fdfb 100%);
            border-color: rgb(173 218 202 / 0.9);
        }

        .gov-queue-report-stat-gold,
        .gov-queue-report-time-card {
            background:
                radial-gradient(circle at top right, rgb(246 236 209 / 0.8), transparent 42%),
                linear-gradient(180deg, #ffffff 0%, #fffcf4 100%);
            border-color: rgb(234 211 163 / 0.85);
        }

        .gov-queue-report-stat-label,
        .gov-queue-report-status-label,
        .gov-queue-report-time-label {
            color: var(--gov-queue-report-ink-500);
        }

        .gov-queue-report-stat-value,
        .gov-queue-report-status-value,
        .gov-queue-report-time-value {
            margin: 0.55rem 0 0;
            font-weight: 800;
            line-height: 1;
            color: var(--gov-queue-report-ink-900);
        }

        .gov-queue-report-stat-value {
            font-size: 2.15rem;
        }

        .gov-queue-report-stat-value-tight {
            font-size: clamp(1.5rem, 2vw, 2rem);
            line-height: 1.12;
        }

        .gov-queue-report-stat-note,
        .gov-queue-report-status-note {
            margin: 0.65rem 0 0;
            font-size: 0.84rem;
            line-height: 1.55;
        }

        .gov-queue-report-status-stat-served {
            border-color: #bde6d7;
            background:
                radial-gradient(circle at top right, rgb(221 247 238 / 0.84), transparent 42%),
                linear-gradient(180deg, #ffffff 0%, #f8fdfa 100%);
        }

        .gov-queue-report-status-stat-skipped {
            border-color: #fecdd3;
            background:
                radial-gradient(circle at top right, rgb(255 228 230 / 0.9), transparent 42%),
                linear-gradient(180deg, #ffffff 0%, #fff9fa 100%);
        }

        .gov-queue-report-status-value {
            font-size: 2rem;
        }

        .gov-queue-report-status-bar,
        .gov-queue-report-bar-track {
            display: flex;
            height: 0.95rem;
            overflow: hidden;
            border-radius: 999px;
            background: #e7eef7;
            box-shadow: inset 0 1px 2px rgb(15 63 115 / 0.08);
        }

        .gov-queue-report-status-bar-served,
        .gov-queue-report-status-bar-skipped,
        .gov-queue-report-bar-fill {
            display: block;
            height: 100%;
        }

        .gov-queue-report-status-bar-served {
            background: linear-gradient(90deg, #1e8a66 0%, var(--gov-queue-report-emerald-600) 100%);
        }

        .gov-queue-report-status-bar-skipped {
            background: linear-gradient(90deg, #d04a67 0%, var(--gov-queue-report-rose-600) 100%);
        }

        .gov-queue-report-bar-fill-daily {
            border-radius: 999px;
            background: linear-gradient(90deg, #285f96 0%, #1d4ed8 100%);
        }

        .gov-queue-report-bar-fill-weekly {
            border-radius: 999px;
            background: linear-gradient(90deg, #a9771d 0%, #d4a335 100%);
        }

        .gov-queue-report-table-wrap {
            overflow: hidden;
        }

        .gov-queue-report-table {
            width: 100%;
            border-collapse: collapse;
        }

        .gov-queue-report-table thead {
            background: linear-gradient(180deg, #f6f9fc 0%, #eef4fb 100%);
        }

        .gov-queue-report-table th {
            padding: 0.88rem 1rem;
            color: var(--gov-queue-report-ink-500);
            font-size: 0.74rem;
            font-weight: 800;
            letter-spacing: 0.1em;
            text-align: left;
            text-transform: uppercase;
        }

        .gov-queue-report-table td {
            padding: 0.95rem 1rem;
            border-top: 1px solid #edf2f7;
            vertical-align: middle;
            color: var(--gov-queue-report-ink-700);
            font-size: 0.92rem;
        }

        .gov-queue-report-table-primary {
            color: var(--gov-queue-report-ink-900);
            font-weight: 700;
        }

        .gov-queue-report-table-sub {
            margin-top: 0.18rem;
            font-size: 0.75rem;
        }

        .gov-queue-report-table-value {
            color: var(--gov-queue-report-blue-900);
            font-weight: 800;
        }

        .gov-queue-report-table-empty {
            text-align: center;
            color: var(--gov-queue-report-ink-500);
        }

        .gov-queue-report-time-panel {
            min-height: 15rem;
            display: grid;
            align-content: center;
            gap: 0.6rem;
            padding: 1.4rem;
            text-align: center;
            background:
                radial-gradient(circle at center, rgb(255 255 255 / 0.8), transparent 62%),
                linear-gradient(180deg, #fffef9 0%, #fcf6e8 100%);
        }

        .gov-queue-report-time-label {
            color: #936a1a;
        }

        .gov-queue-report-time-value {
            font-size: clamp(2rem, 4vw, 3rem);
            line-height: 1.08;
        }

        .gov-queue-report-time-note {
            margin: 0 auto;
            max-width: 25rem;
            font-size: 0.92rem;
            line-height: 1.7;
            color: var(--gov-queue-report-ink-700);
        }

        @media (max-width: 1180px) {
            .gov-queue-report-summary-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 900px) {
            .gov-queue-report-head {
                grid-template-columns: 1fr;
            }

            .gov-queue-report-card-meta,
            .gov-queue-report-masthead-side,
            .gov-queue-report-chip-row {
                text-align: left;
                justify-items: start;
                justify-content: flex-start;
            }
        }

        @media (max-width: 780px) {
            .gov-queue-report-masthead,
            .gov-queue-report-card {
                border-radius: 1.15rem;
            }

            .gov-queue-report-masthead {
                padding: 1.05rem;
            }
        }

        @media (max-width: 640px) {
            .gov-queue-report-banner {
                flex-direction: column;
                align-items: flex-start;
            }

            .gov-queue-report-seal-wrap {
                width: 4rem;
                height: 4rem;
            }

            .gov-queue-report-title {
                font-size: 1.55rem;
            }

            .gov-queue-report-card-title {
                font-size: 1.18rem;
            }

            .gov-queue-report-summary-grid,
            .gov-queue-report-status-grid {
                grid-template-columns: 1fr;
            }

            .gov-queue-report-stat-value {
                font-size: 1.85rem;
            }
        }

        @keyframes gov-queue-report-fade-up {
            from {
                opacity: 0;
                transform: translateY(8px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .gov-queue-report-masthead,
            .gov-queue-report-card {
                animation: none;
            }

            .gov-queue-report-btn {
                transition: none;
            }
        }
    </style>
@endonce
