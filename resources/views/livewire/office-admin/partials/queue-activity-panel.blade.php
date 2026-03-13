@php
    $activityEntries = collect($entries ?? []);
    $latestIssuedAt = $activityEntries
        ->sortByDesc(fn ($entry) => $entry->created_at?->getTimestamp() ?? 0)
        ->first()?->created_at?->timezone('Asia/Manila');
    $activityStatusStyles = [
        'serving' => ['label' => 'Serving', 'badge' => 'gov-activity-status-serving', 'dot' => '#0ea5e9'],
        'waiting' => ['label' => 'Waiting', 'badge' => 'gov-activity-status-waiting', 'dot' => '#f59e0b'],
        'completed' => ['label' => 'Completed', 'badge' => 'gov-activity-status-completed', 'dot' => '#10b981'],
        'not_served' => ['label' => 'Not Served', 'badge' => 'gov-activity-status-not-served', 'dot' => '#f43f5e'],
        'cancelled' => ['label' => 'Cancelled', 'badge' => 'gov-activity-status-cancelled', 'dot' => '#64748b'],
        'default' => ['label' => 'Unknown', 'badge' => 'gov-activity-status-cancelled', 'dot' => '#94a3b8'],
    ];
@endphp

<section class="gov-activity-panel" aria-labelledby="{{ $panelId }}">
    <div class="gov-activity-panel-head">
        <div class="gov-activity-intro">
            <div class="gov-activity-chip-row">
                <span class="gov-activity-chip gov-activity-chip-scope">{{ $kicker ?? 'Queue Activity Monitor' }}</span>
                <span class="gov-activity-chip gov-activity-chip-live">Live Auto Refresh</span>
            </div>
            <h2 id="{{ $panelId }}" class="gov-activity-title">{{ $heading }}</h2>
            <p class="gov-activity-copy">
                {{ $description ?? 'Issued, called, and completed timestamps for the current queue day.' }}
                @if($latestIssuedAt)
                    Latest issued ticket logged at {{ $latestIssuedAt->format('h:i:s A') }}.
                @endif
            </p>
        </div>
    </div>

    <div class="gov-activity-table-shell">
        <div class="gov-activity-table-wrap overflow-x-auto">
            <table class="gov-activity-table">
                <thead>
                    <tr>
                        <th>Ticket #</th>
                        <th>Status</th>
                        <th>Issued</th>
                        <th>Called</th>
                        <th>Completed</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($activityEntries as $entry)
                        @php($statusMeta = $activityStatusStyles[$entry->status] ?? array_merge($activityStatusStyles['default'], ['label' => ucwords(str_replace('_', ' ', $entry->status ?? 'unknown'))]))
                        <tr class="gov-activity-row">
                            <td>
                                <span class="gov-activity-ticket">{{ $entry->queue_number }}</span>
                            </td>
                            <td>
                                <span class="gov-activity-status {{ $statusMeta['badge'] }}">
                                    <span class="gov-activity-status-dot" style="background-color: {{ $statusMeta['dot'] }};" aria-hidden="true"></span>
                                    {{ $statusMeta['label'] }}
                                </span>
                            </td>
                            <td>
                                <span class="gov-activity-time-value">{{ $entry->created_at->timezone('Asia/Manila')->format('h:i:s A') }}</span>
                            </td>
                            <td>
                                <span class="gov-activity-time-value {{ $entry->called_at ? '' : 'gov-activity-time-empty' }}">
                                    {{ $entry->called_at?->timezone('Asia/Manila')?->format('h:i:s A') ?? '-' }}
                                </span>
                            </td>
                            <td>
                                <span class="gov-activity-time-value {{ $entry->served_at ? '' : 'gov-activity-time-empty' }}">
                                    {{ $entry->served_at?->timezone('Asia/Manila')?->format('h:i:s A') ?? '-' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="gov-activity-empty">
                                <div class="gov-activity-empty-shell">
                                    <p class="gov-activity-empty-title">No ticket movement yet</p>
                                    <p class="gov-activity-empty-copy">{{ $emptyMessage }}</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</section>

@once
    <style>
        .gov-activity-panel {
            position: relative;
            overflow: hidden;
            border: 1px solid #d6e1ee;
            border-radius: 1.6rem;
            background:
                radial-gradient(circle at top right, rgb(220 232 246 / 0.58), transparent 34%),
                linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
            box-shadow: 0 28px 45px -34px rgb(15 63 115 / 0.38);
        }

        .gov-activity-panel::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at top left, rgb(21 71 119 / 0.08), transparent 24%),
                linear-gradient(120deg, transparent 0 58%, rgb(185 138 43 / 0.06) 58% 100%);
            pointer-events: none;
        }

        .gov-activity-panel-head,
        .gov-activity-table-shell {
            position: relative;
            z-index: 1;
        }

        .gov-activity-panel-head {
            display: grid;
            gap: 1rem;
            padding: 1.15rem;
            background:
                radial-gradient(circle at top right, rgb(255 255 255 / 0.16), transparent 34%),
                linear-gradient(135deg, #15345f 0%, #8c631d 58%, #df8f05 100%);
            color: #fff;
        }

        .gov-activity-intro {
            display: grid;
            gap: 0.8rem;
        }

        .gov-activity-chip-row {
            display: flex;
            flex-wrap: wrap;
            gap: 0.55rem;
        }

        .gov-activity-chip {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            border: 1px solid rgb(255 255 255 / 0.18);
            padding: 0.38rem 0.78rem;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .gov-activity-chip-scope {
            background: rgb(255 255 255 / 0.16);
        }

        .gov-activity-chip-live {
            background: rgb(16 185 129 / 0.16);
            border-color: rgb(167 243 208 / 0.34);
            color: #d1fae5;
        }

        .gov-activity-title {
            margin: 0;
            font-size: clamp(1.4rem, 2.2vw, 1.9rem);
            line-height: 1.08;
            font-weight: 800;
        }

        .gov-activity-copy {
            margin: 0;
            max-width: 42rem;
            color: rgb(226 232 240 / 0.94);
            font-size: 0.92rem;
            line-height: 1.6;
        }

        .gov-activity-table-shell {
            padding: 1rem;
        }

        .gov-activity-table-wrap {
            overflow: hidden;
            border: 1px solid #dbe7f3;
            border-radius: 1.2rem;
            background: linear-gradient(180deg, #f9fbfe 0%, #ffffff 100%);
        }

        .gov-activity-table {
            width: 100%;
            min-width: 760px;
            border-collapse: separate;
            border-spacing: 0;
        }

        .gov-activity-table thead th {
            padding: 0.95rem 1rem;
            border-bottom: 1px solid #dbe5f0;
            background: linear-gradient(180deg, #eef4fb 0%, #f6f9fc 100%);
            color: #5b7089;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.11em;
            text-align: left;
            text-transform: uppercase;
        }

        .gov-activity-table tbody td {
            padding: 0.95rem 1rem;
            border-top: 1px solid #edf2f7;
            vertical-align: middle;
        }

        .gov-activity-table tbody tr:first-child td {
            border-top: 0;
        }

        .gov-activity-row {
            transition: background-color 160ms ease, transform 160ms ease;
        }

        .gov-activity-row:hover {
            background: #f8fbff;
        }

        .gov-activity-ticket {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            border: 1px solid #d5e3f1;
            background: linear-gradient(180deg, #ffffff 0%, #f3f8fd 100%);
            padding: 0.45rem 0.78rem;
            color: #183153;
            font-size: 0.84rem;
            font-weight: 800;
            letter-spacing: 0.04em;
        }

        .gov-activity-status {
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            border-radius: 999px;
            padding: 0.45rem 0.78rem;
            font-size: 0.76rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .gov-activity-status-dot {
            width: 0.42rem;
            height: 0.42rem;
            border-radius: 999px;
            flex-shrink: 0;
        }

        .gov-activity-status-serving {
            background: #e0f2fe;
            color: #0369a1;
        }

        .gov-activity-status-waiting {
            background: #fef3c7;
            color: #b45309;
        }

        .gov-activity-status-completed {
            background: #d1fae5;
            color: #047857;
        }

        .gov-activity-status-not-served {
            background: #ffe4e6;
            color: #be123c;
        }

        .gov-activity-status-cancelled {
            background: #e2e8f0;
            color: #475569;
        }

        .gov-activity-time-value {
            color: #2f4866;
            font-size: 0.86rem;
            font-weight: 700;
            letter-spacing: 0.02em;
            white-space: nowrap;
        }

        .gov-activity-time-empty {
            color: #94a3b8;
        }

        .gov-activity-empty {
            padding: 2.5rem 1rem;
        }

        .gov-activity-empty-shell {
            border: 1px dashed #cdd9e6;
            border-radius: 1rem;
            padding: 1.5rem;
            text-align: center;
            background: linear-gradient(180deg, #fbfdff 0%, #f8fbfe 100%);
        }

        .gov-activity-empty-title {
            margin: 0;
            color: #183153;
            font-size: 1rem;
            font-weight: 800;
        }

        .gov-activity-empty-copy {
            margin: 0.45rem 0 0;
            color: #64748b;
            font-size: 0.86rem;
            line-height: 1.55;
        }

        @media (max-width: 780px) {
            .gov-activity-panel {
                border-radius: 1.3rem;
            }

            .gov-activity-panel-head,
            .gov-activity-table-shell {
                padding: 0.9rem;
            }
        }
    </style>
@endonce
