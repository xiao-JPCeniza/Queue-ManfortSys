@section('full_width', '1')

<div>
    @php
        $isSuperAdmin = auth()->user()?->isSuperAdmin() ?? false;
        $superAdminOfficeNames = [
            'accounting' => 'Accounting Office',
            'hrmo' => 'Human Resource',
            'mho' => 'Health Office',
            'treasury' => 'Treasury Office',
        ];
        $officeCount = $offices->count();
        $servingCount = (int) $offices->sum(fn ($office) => (int) ($office->active_window_count ?? 0));
        $waitingTotal = (int) $offices->sum('waiting_count');
        $idleCount = (int) $offices->sum(fn ($office) => (int) ($office->available_window_count ?? max(0, ($office->window_count ?? 1) - ($office->active_window_count ?? 0))));
        $activityStatusCounts = $recentEntries->countBy('status');
    @endphp

    <div class="gov-admin-shell">
        <section class="gov-admin-briefing" aria-labelledby="dashboard-heading">
            <div class="gov-admin-briefing-copy">
                <div class="gov-admin-seal-wrap" aria-hidden="true">
                    <img src="{{ asset('images/lgu-logo.png') }}" alt="" class="gov-admin-seal">
                </div>

                <div>
                    <p class="gov-admin-kicker">Municipal Queue Oversight</p>
                    <h1 id="dashboard-heading" class="gov-font-heading gov-admin-title">
                        {{ $isSuperAdmin ? 'Admin Dashboard' : 'Dashboard' }}
                    </h1>
                    <p class="gov-admin-subtitle">
                        Monitor queue activity across all offices.
                    </p>

                    <div class="gov-admin-chip-row" aria-label="Dashboard context">
                        <span class="gov-admin-chip gov-admin-chip-strong">
                            {{ $isSuperAdmin ? 'System-wide oversight' : 'Queue operations view' }}
                        </span>
                        <span class="gov-admin-chip">Live office monitoring</span>
                        <span class="gov-admin-chip">Internal government use</span>
                    </div>
                </div>
            </div>

            <div class="gov-admin-stat-grid" aria-label="Operations summary">
                <article class="gov-admin-stat-card">
                    <p class="gov-admin-stat-label">Offices in View</p>
                    <p class="gov-font-heading gov-admin-stat-value">{{ number_format($officeCount) }}</p>
                    <p class="gov-admin-stat-note">Public-facing offices currently tracked on this dashboard.</p>
                </article>

                <article class="gov-admin-stat-card gov-admin-stat-card-strong">
                    <p class="gov-admin-stat-label">Active Windows</p>
                    <p class="gov-font-heading gov-admin-stat-value">{{ number_format($servingCount) }}</p>
                    <p class="gov-admin-stat-note">Service windows with an active queue transaction in progress.</p>
                </article>

                <article class="gov-admin-stat-card gov-admin-stat-card-accent">
                    <p class="gov-admin-stat-label">Waiting Tickets</p>
                    <p class="gov-font-heading gov-admin-stat-value">{{ number_format($waitingTotal) }}</p>
                    <p class="gov-admin-stat-note">Queue numbers still pending across all monitored offices.</p>
                </article>

                <article class="gov-admin-stat-card">
                    <p class="gov-admin-stat-label">Available Windows</p>
                    <p class="gov-font-heading gov-admin-stat-value">{{ number_format($idleCount) }}</p>
                    <p class="gov-admin-stat-note">Service windows without an active ticket at the moment.</p>
                </article>
            </div>
        </section>

        <section class="gov-admin-section" aria-labelledby="offices-heading">
            <div class="gov-admin-section-head">
                <div>
                    <p class="gov-admin-section-kicker">Office Directory</p>
                    <h2 id="offices-heading" class="gov-font-heading gov-admin-section-title">Office Queue Status</h2>
                </div>

                <p class="gov-admin-section-copy">
                    Review active counters, inspect the next queue number, and access queue controls for each office.
                </p>
            </div>

            <div class="gov-office-grid">
                @foreach($offices as $office)
                    @php($officeDisplayName = $superAdminOfficeNames[$office->slug] ?? $office->name)

                    <article class="gov-office-card">
                        <div class="gov-office-card-head">
                            <div class="min-w-0">
                                <p class="gov-office-kicker">Service Operations Desk</p>
                                <h3 class="gov-font-heading gov-office-title">{{ $officeDisplayName }}</h3>

                                <div class="gov-office-meta" aria-label="Office queue numbering">
                                    <span class="gov-office-meta-chip">{{ $office->prefix }}</span>
                                    <span class="gov-office-meta-text">Next ticket release: #{{ $office->next_number }}</span>
                                    <span class="gov-office-meta-text">{{ number_format($office->window_count ?? 1) }} window{{ ($office->window_count ?? 1) === 1 ? '' : 's' }}</span>
                                </div>
                            </div>

                            @php($manageRoute = $isSuperAdmin ? route('office.dashboard', $office->slug) : route('queue-master.office', $office->slug))
                            <div class="gov-office-actions">
                                <a href="{{ $manageRoute }}" wire:navigate
                                   class="lgu-btn gov-office-action gov-office-action-primary">
                                    Manage
                                </a>
                                <button wire:click="resetNumbering({{ $office->id }})"
                                        wire:confirm="Reset queue numbering for {{ $officeDisplayName }} to 1? This will clear this office's generated tickets for today."
                                        class="lgu-btn gov-office-action gov-office-action-secondary">
                                    Reset #
                                </button>
                            </div>
                        </div>

                        <div class="gov-office-panel-grid">
                            <section class="gov-queue-panel gov-queue-panel-serving" aria-label="Now serving">
                                <p class="gov-queue-panel-label">Serving Now</p>
                                <p class="gov-queue-panel-value">
                                    {{ $office->serving_ticket ?: 'No active ticket' }}
                                </p>
                                <p class="gov-queue-panel-note">
                                    {{ number_format($office->active_window_count ?? 0) }} of {{ number_format($office->window_count ?? 1) }} window{{ ($office->window_count ?? 1) === 1 ? '' : 's' }} currently active.
                                </p>
                            </section>

                            <section class="gov-queue-panel gov-queue-panel-waiting" aria-label="Next in line">
                                <div class="gov-queue-panel-topline">
                                    <p class="gov-queue-panel-label">Next in Line</p>
                                    <span class="gov-queue-waiting-chip">{{ number_format($office->waiting_count) }} waiting</span>
                                </div>
                                <p class="gov-queue-panel-value">
                                    {{ $office->next_waiting_ticket ?: 'No one waiting' }}
                                </p>
                                <p class="gov-queue-panel-note">Next recorded queue number available for call.</p>
                            </section>
                        </div>
                    </article>
                @endforeach
            </div>
        </section>

        <section class="gov-admin-section" aria-labelledby="activity-heading">
            <div class="gov-admin-section-head gov-admin-section-head-records">
                <div>
                    <p class="gov-admin-section-kicker">Official Queue Record</p>
                    <h2 id="activity-heading" class="gov-font-heading gov-admin-section-title">Recent Queue Activity</h2>
                </div>

                <div class="gov-activity-summary" aria-label="Recent activity summary">
                    <span class="gov-activity-summary-chip">{{ number_format($recentEntries->count()) }} logged</span>
                    <span class="gov-activity-summary-chip">{{ number_format((int) ($activityStatusCounts['waiting'] ?? 0)) }} waiting</span>
                    <span class="gov-activity-summary-chip">{{ number_format((int) ($activityStatusCounts['serving'] ?? 0)) }} serving</span>
                    <span class="gov-activity-summary-chip">{{ number_format((int) ($activityStatusCounts['completed'] ?? 0)) }} completed</span>
                </div>
            </div>

            <div class="gov-admin-records-card">
                <div class="gov-admin-table-wrap">
                    <table class="gov-admin-table" role="table" aria-label="Recent queue entries">
                        <thead>
                            <tr>
                                <th scope="col">Office</th>
                                <th scope="col">Queue #</th>
                                <th scope="col">Status</th>
                                <th scope="col">Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentEntries as $entry)
                                @php($entryOfficeDisplayName = $superAdminOfficeNames[$entry->office->slug] ?? $entry->office->name)
                                <tr>
                                    <td>
                                        <div class="gov-admin-table-office">
                                            <span class="gov-admin-table-office-name">{{ $entryOfficeDisplayName }}</span>
                                            <span class="gov-admin-table-office-code">{{ $entry->office->prefix }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="gov-admin-table-ticket-stack">
                                            <span class="gov-admin-table-ticket">{{ $entry->queue_number }}</span>
                                            @if($entry->service_window_label)
                                                <span class="gov-admin-table-office-code">{{ $entry->service_window_label }}</span>
                                            @endif
                                            <span class="gov-admin-client-type-chip {{ $entry->isPriorityClient() ? 'gov-admin-client-type-chip-priority' : 'gov-admin-client-type-chip-regular' }}">
                                                {{ $entry->client_type_label }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold tracking-[0.08em] uppercase
                                            @if($entry->status === 'waiting') bg-amber-100 text-amber-800
                                            @elseif($entry->status === 'serving') bg-blue-100 text-blue-800
                                            @elseif($entry->status === 'completed') bg-emerald-100 text-emerald-800
                                            @elseif($entry->status === 'not_served') bg-rose-100 text-rose-800
                                            @else bg-slate-100 text-slate-600 @endif">
                                            {{ \Illuminate\Support\Str::headline($entry->status) }}
                                        </span>
                                    </td>
                                    <td class="gov-admin-table-time">{{ ($entry->activityAt ?? $entry->created_at)->diffForHumans() }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="gov-admin-table-empty">
                                        {{ $isSuperAdmin ? 'No queue activity yet today.' : 'No active queue entries.' }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</div>

@once
    <style>
        .gov-admin-shell {
            --gov-blue-950: #0d2d54;
            --gov-blue-900: #174574;
            --gov-blue-800: #2b639a;
            --gov-blue-100: #dbe8f5;
            --gov-gold-500: #ba8a2f;
            --gov-gold-100: #f7efdd;
            --gov-emerald-700: #0c6a52;
            --gov-emerald-100: #d7efe6;
            --gov-amber-700: #9a5d06;
            --gov-amber-100: #fff0d4;
            --gov-ink-950: #16263a;
            --gov-ink-700: #425974;
            --gov-ink-500: #64748b;
            --gov-border: #d5e0ec;
            --gov-surface: #ffffff;
            position: relative;
            display: grid;
            gap: 1.5rem;
        }

        .gov-admin-shell::before,
        .gov-admin-shell::after {
            content: '';
            position: absolute;
            border-radius: 999px;
            pointer-events: none;
            z-index: 0;
            opacity: 0.45;
        }

        .gov-admin-shell::before {
            width: 18rem;
            height: 18rem;
            top: -2.5rem;
            right: -2rem;
            background: radial-gradient(circle at 35% 35%, rgb(219 234 254 / 0.88), transparent 70%);
        }

        .gov-admin-shell::after {
            width: 14rem;
            height: 14rem;
            left: -2rem;
            bottom: 1rem;
            background: radial-gradient(circle at 50% 50%, rgb(247 239 221 / 0.9), transparent 68%);
        }

        .gov-admin-briefing,
        .gov-admin-section {
            position: relative;
            z-index: 1;
        }

        .gov-admin-briefing {
            overflow: hidden;
            border: 1px solid #c8d8ea;
            border-radius: 1.45rem;
            padding: 1.45rem;
            background:
                radial-gradient(circle at top right, rgb(255 255 255 / 0.18), transparent 44%),
                linear-gradient(135deg, var(--gov-blue-950), var(--gov-blue-900));
            color: #fff;
            display: grid;
            gap: 1.25rem;
            box-shadow: 0 28px 42px -36px rgb(10 45 85 / 0.55);
            animation: gov-admin-rise 420ms ease-out both;
        }

        .gov-admin-briefing::after {
            content: '';
            position: absolute;
            inset: auto 0 0;
            height: 0.36rem;
            background: linear-gradient(90deg, #1d4ed8 0%, #1d4ed8 58%, #ba8a2f 58%, #ba8a2f 82%, #be123c 82%, #be123c 100%);
        }

        .gov-admin-briefing-copy {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }

        .gov-admin-seal-wrap {
            width: 5rem;
            height: 5rem;
            flex-shrink: 0;
            padding: 0.35rem;
            border-radius: 999px;
            background: rgb(255 255 255 / 0.14);
            border: 1px solid rgb(255 255 255 / 0.26);
            box-shadow: inset 0 0 0 1px rgb(255 255 255 / 0.08);
        }

        .gov-admin-seal {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 999px;
            background: rgb(255 255 255 / 0.9);
        }

        .gov-admin-kicker,
        .gov-admin-section-kicker,
        .gov-office-kicker,
        .gov-queue-panel-label,
        .gov-admin-stat-label {
            margin: 0;
            font-size: 0.72rem;
            line-height: 1.2;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            font-weight: 700;
        }

        .gov-admin-kicker {
            color: rgb(219 234 254 / 0.96);
        }

        .gov-admin-title {
            margin: 0.35rem 0 0;
            color: #fff;
            font-size: clamp(1.75rem, 3vw, 2.45rem);
            line-height: 1.05;
        }

        .gov-admin-subtitle {
            margin: 0.55rem 0 0;
            max-width: 44rem;
            color: rgb(226 232 240 / 0.94);
            font-size: 0.97rem;
            line-height: 1.65;
        }

        .gov-admin-chip-row {
            margin-top: 1rem;
            display: flex;
            flex-wrap: wrap;
            gap: 0.55rem;
        }

        .gov-admin-chip,
        .gov-activity-summary-chip,
        .gov-office-meta-chip {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            font-size: 0.74rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .gov-admin-chip {
            padding: 0.45rem 0.8rem;
            color: #fff;
            background: rgb(255 255 255 / 0.12);
            border: 1px solid rgb(255 255 255 / 0.16);
        }

        .gov-admin-chip-strong {
            background: rgb(186 138 47 / 0.26);
            border-color: rgb(247 239 221 / 0.36);
        }

        .gov-admin-stat-grid {
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 0.85rem;
            align-content: start;
        }

        .gov-admin-stat-card {
            border-radius: 1.1rem;
            padding: 1rem;
            background: linear-gradient(180deg, rgb(255 255 255 / 0.14), rgb(255 255 255 / 0.08));
            border: 1px solid rgb(255 255 255 / 0.14);
            backdrop-filter: blur(2px);
        }

        .gov-admin-stat-card-strong {
            background: linear-gradient(180deg, rgb(15 138 98 / 0.24), rgb(15 138 98 / 0.16));
        }

        .gov-admin-stat-card-accent {
            background: linear-gradient(180deg, rgb(186 138 47 / 0.24), rgb(186 138 47 / 0.16));
        }

        .gov-admin-stat-label {
            color: rgb(219 234 254 / 0.92);
        }

        .gov-admin-stat-value {
            margin: 0.55rem 0 0;
            font-size: clamp(1.45rem, 2.5vw, 2rem);
            line-height: 1;
            color: #fff;
        }

        .gov-admin-stat-note {
            margin: 0.45rem 0 0;
            color: rgb(226 232 240 / 0.9);
            font-size: 0.81rem;
            line-height: 1.5;
        }

        .gov-admin-section {
            display: grid;
            gap: 1rem;
        }

        .gov-admin-section-head {
            display: flex;
            flex-wrap: wrap;
            align-items: end;
            justify-content: space-between;
            gap: 0.9rem;
        }

        .gov-admin-section-head-records {
            align-items: center;
        }

        .gov-admin-section-kicker {
            color: var(--gov-blue-800);
        }

        .gov-admin-section-title {
            margin: 0.28rem 0 0;
            color: var(--gov-ink-950);
            font-size: clamp(1.35rem, 2vw, 1.7rem);
            line-height: 1.1;
        }

        .gov-admin-section-copy {
            max-width: 34rem;
            margin: 0;
            color: var(--gov-ink-700);
            font-size: 0.92rem;
            line-height: 1.6;
        }

        .gov-office-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 1rem;
        }

        .gov-office-card,
        .gov-admin-records-card {
            overflow: hidden;
            border: 1px solid var(--gov-border);
            border-radius: 1.3rem;
            background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
            box-shadow: 0 22px 36px -34px rgb(15 63 115 / 0.32);
            animation: gov-admin-rise 480ms ease-out both;
        }

        .gov-office-card {
            position: relative;
            padding: 1.2rem;
        }

        .gov-office-card::before {
            content: '';
            position: absolute;
            inset: 0 auto 0 0;
            width: 0.42rem;
            background: linear-gradient(180deg, var(--gov-blue-800) 0%, var(--gov-gold-500) 100%);
        }

        .gov-office-card-head {
            display: flex;
            flex-wrap: wrap;
            align-items: start;
            justify-content: space-between;
            gap: 1rem;
        }

        .gov-office-kicker {
            color: var(--gov-blue-800);
        }

        .gov-office-title {
            margin: 0.3rem 0 0;
            color: var(--gov-ink-950);
            font-size: 1.45rem;
            line-height: 1.15;
        }

        .gov-office-meta {
            margin-top: 0.75rem;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 0.55rem;
        }

        .gov-office-meta-chip {
            padding: 0.36rem 0.65rem;
            color: var(--gov-blue-900);
            background: var(--gov-blue-100);
            border: 1px solid #bfd1e6;
        }

        .gov-office-meta-text {
            color: var(--gov-ink-500);
            font-size: 0.88rem;
            font-weight: 600;
            line-height: 1.4;
        }

        .gov-office-actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 0.6rem;
        }

        .gov-office-action {
            min-width: 6.5rem;
            border-radius: 0.9rem;
            padding: 0.72rem 1rem;
            font-size: 0.84rem;
            font-weight: 700;
            letter-spacing: 0.03em;
            transition: transform 180ms ease, box-shadow 180ms ease, filter 180ms ease;
        }

        .gov-office-action:hover {
            transform: translateY(-1px);
        }

        .gov-office-action:focus {
            outline: none;
        }

        .gov-office-action-primary {
            color: #fff;
            background: linear-gradient(180deg, var(--gov-blue-800), var(--gov-blue-900));
            box-shadow: 0 16px 22px -20px rgb(21 71 119 / 0.85);
        }

        .gov-office-action-secondary {
            color: var(--gov-amber-700);
            background: linear-gradient(180deg, #fff8e8 0%, #fef1d2 100%);
            border: 1px solid #f2d59a;
            box-shadow: 0 14px 20px -22px rgb(185 138 43 / 0.7);
        }

        .gov-office-panel-grid {
            margin-top: 1rem;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.85rem;
        }

        .gov-queue-panel {
            border-radius: 1.1rem;
            padding: 1rem;
            border: 1px solid #dce5ef;
            background: linear-gradient(180deg, #ffffff 0%, #f9fbfe 100%);
        }

        .gov-queue-panel-serving {
            background:
                radial-gradient(circle at top right, rgb(215 239 230 / 0.75), transparent 44%),
                linear-gradient(180deg, #ffffff 0%, #f4fcf8 100%);
            border-color: #cfe7dc;
        }

        .gov-queue-panel-waiting {
            background:
                radial-gradient(circle at top right, rgb(255 240 212 / 0.78), transparent 44%),
                linear-gradient(180deg, #ffffff 0%, #fffaf0 100%);
            border-color: #f1ddb3;
        }

        .gov-queue-panel-topline {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 0.55rem;
        }

        .gov-queue-panel-label {
            color: var(--gov-ink-500);
        }

        .gov-queue-panel-value {
            margin: 0.55rem 0 0;
            color: var(--gov-ink-950);
            font-size: clamp(1.3rem, 2vw, 1.7rem);
            line-height: 1.1;
            font-weight: 800;
            word-break: break-word;
        }

        .gov-queue-panel-serving .gov-queue-panel-value {
            color: var(--gov-emerald-700);
        }

        .gov-queue-panel-waiting .gov-queue-panel-value {
            color: var(--gov-amber-700);
        }

        .gov-queue-panel-note {
            margin: 0.45rem 0 0;
            color: var(--gov-ink-500);
            font-size: 0.81rem;
            line-height: 1.5;
        }

        .gov-queue-waiting-chip,
        .gov-activity-summary-chip {
            padding: 0.35rem 0.65rem;
        }

        .gov-queue-waiting-chip {
            color: var(--gov-amber-700);
            background: var(--gov-amber-100);
            border: 1px solid #f2d59a;
        }

        .gov-activity-summary {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 0.55rem;
        }

        .gov-activity-summary-chip {
            color: var(--gov-blue-900);
            background: linear-gradient(180deg, #eef4fb 0%, #e1ebf8 100%);
            border: 1px solid #cbd9ea;
        }

        .gov-admin-records-card {
            position: relative;
        }

        .gov-admin-records-card::before {
            content: '';
            position: absolute;
            inset: 0 auto 0 0;
            width: 0.42rem;
            background: linear-gradient(180deg, var(--gov-gold-500) 0%, var(--gov-blue-800) 100%);
        }

        .gov-admin-table-wrap {
            overflow-x: auto;
        }

        .gov-admin-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.94rem;
        }

        .gov-admin-table thead {
            background:
                linear-gradient(180deg, #f8fbff 0%, #eef4fb 100%);
        }

        .gov-admin-table th {
            padding: 1rem 1.15rem;
            text-align: left;
            color: var(--gov-ink-700);
            font-size: 0.8rem;
            font-weight: 800;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            border-bottom: 1px solid #dbe6f1;
            white-space: nowrap;
        }

        .gov-admin-table td {
            padding: 1rem 1.15rem;
            border-top: 1px solid #eef2f7;
            color: var(--gov-ink-950);
            vertical-align: middle;
        }

        .gov-admin-table tbody tr {
            transition: background-color 160ms ease;
        }

        .gov-admin-table tbody tr:hover {
            background: #f9fbfe;
        }

        .gov-admin-table-office {
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
            min-width: 9rem;
        }

        .gov-admin-table-office-name {
            font-weight: 700;
            color: var(--gov-ink-950);
        }

        .gov-admin-table-office-code {
            color: var(--gov-ink-500);
            font-size: 0.77rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            font-weight: 700;
        }

        .gov-admin-table-ticket {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 0.42rem 0.8rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            color: var(--gov-blue-900);
            background: var(--gov-blue-100);
            border: 1px solid #bfd1e6;
        }

        .gov-admin-table-ticket-stack {
            display: inline-flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 0.42rem;
        }

        .gov-admin-client-type-chip {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            border: 1px solid;
            padding: 0.32rem 0.62rem;
            font-size: 0.66rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .gov-admin-client-type-chip-regular {
            background: #eef4ff;
            border-color: #bfd5f6;
            color: #1d4ed8;
        }

        .gov-admin-client-type-chip-priority {
            background: #fff4db;
            border-color: #f4d28f;
            color: #9a5d06;
        }

        .gov-admin-table-time {
            color: var(--gov-ink-500);
            white-space: nowrap;
        }

        .gov-admin-table-empty {
            padding: 2rem 1rem !important;
            text-align: center;
            color: var(--gov-ink-500);
        }

        @media (max-width: 1280px) {
            .gov-admin-stat-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }

        @media (max-width: 960px) {
            .gov-office-grid,
            .gov-office-panel-grid,
            .gov-admin-stat-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 720px) {
            .gov-admin-briefing,
            .gov-office-card {
                padding: 1rem;
            }

            .gov-admin-briefing-copy {
                flex-direction: column;
            }

            .gov-admin-seal-wrap {
                width: 4.35rem;
                height: 4.35rem;
            }

            .gov-admin-table th,
            .gov-admin-table td {
                padding-left: 0.95rem;
                padding-right: 0.95rem;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            .gov-admin-briefing,
            .gov-office-card,
            .gov-admin-records-card {
                animation: none;
            }

            .gov-office-action,
            .gov-admin-table tbody tr {
                transition: none;
            }
        }

        @keyframes gov-admin-rise {
            from {
                opacity: 0;
                transform: translateY(16px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
@endonce
