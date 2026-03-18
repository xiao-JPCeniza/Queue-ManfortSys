<div wire:poll.keep-alive.2s="tick" data-session-keepalive="always" class="gov-monitor-root">
    <section class="gov-monitor-shell" aria-label="All offices live queue monitor">
        <header class="gov-monitor-header">
            <div class="gov-monitor-brand">
                <img src="{{ asset('images/lgu-logo.png') }}" alt="Municipality of Manolo Fortich official seal" class="gov-monitor-seal">
                <div class="gov-monitor-brand-copy">
                    <h1 class="gov-font-heading gov-monitor-title">Live Queue Monitor</h1>
                    <p class="gov-monitor-subtitle">All Offices | Municipality of Manolo Fortich</p>
                </div>
            </div>

            <div class="gov-monitor-clock" data-manila-clock data-manila-now="{{ $manilaNow->toIso8601String() }}" aria-live="polite" aria-label="Current Philippine time">
                <p class="gov-monitor-location">Manolo Fortich, Bukidnon</p>
                <p class="gov-monitor-time" data-manila-clock-time>{{ $manilaNow->format('h:i:s A') }}</p>
                <p class="gov-monitor-date" data-manila-clock-date>{{ $manilaNow->format('l, M d, Y') }}</p>
            </div>
        </header>

        <main class="gov-monitor-main">
            @if($featuredOfficeRow)
                <article class="gov-office-monitor-card gov-office-monitor-card-featured" aria-label="Featured office queue">
                    <div class="gov-office-monitor-head">
                        <div>
                            <p class="gov-office-monitor-kicker">Office Queue</p>
                            <h2 class="gov-font-heading gov-office-monitor-title">{{ $featuredOfficeRow['office']->name }}</h2>
                        </div>

                        <span class="gov-office-monitor-chip">
                            {{ $featuredOfficeRow['waiting_count'] }} waiting | {{ $featuredOfficeRow['active_window_count'] }} active
                        </span>
                    </div>

                    <div class="gov-monitor-grid">
                        <section class="gov-monitor-panel gov-panel-serving" aria-labelledby="serving-{{ $featuredOfficeRow['office']->slug }}">
                            <div class="gov-panel-head">
                                <h3 id="serving-{{ $featuredOfficeRow['office']->slug }}" class="gov-font-heading gov-panel-title">Serving Now</h3>
                                <span class="gov-status-badge {{ $featuredOfficeRow['serving'] ? 'gov-status-badge-active' : 'gov-status-badge-idle' }}">
                                    {{ $featuredOfficeRow['serving'] ? '1 Active' : 'Idle' }}
                                </span>
                            </div>

                            <div class="gov-panel-body">
                                @if($featuredOfficeRow['serving'])
                                    <article class="gov-ticket-card gov-ticket-card-serving">
                                        <p class="gov-ticket-label">{{ $featuredOfficeRow['serving']->service_window_label ?? 'Window 1' }}</p>
                                        <p class="gov-ticket-number gov-ticket-number-serving" aria-live="polite">{{ $featuredOfficeRow['serving']->queue_number }}</p>
                                        <div class="gov-ticket-meta-block">
                                            <p class="gov-ticket-meta-label">Called at</p>
                                            <p class="gov-ticket-meta-value">{{ $featuredOfficeRow['serving']->displayCalledAt()?->format('h:i:s A') ?? 'Just now' }}</p>
                                        </div>
                                    </article>
                                @else
                                    <div class="gov-ticket-empty">
                                        <p class="gov-ticket-empty-title">No active ticket right now</p>
                                        <p class="gov-ticket-empty-text">The next waiting ticket will be called automatically.</p>
                                    </div>
                                @endif
                            </div>
                        </section>

                        <section class="gov-monitor-panel gov-panel-next" aria-labelledby="next-{{ $featuredOfficeRow['office']->slug }}">
                            <div class="gov-panel-head">
                                <h3 id="next-{{ $featuredOfficeRow['office']->slug }}" class="gov-font-heading gov-panel-title">Next in Line</h3>
                                <span class="gov-status-badge {{ $featuredOfficeRow['nextInline'] ? 'gov-status-badge-queue' : 'gov-status-badge-idle' }}">
                                    {{ $featuredOfficeRow['nextInline'] ? 'Queued' : 'Empty' }}
                                </span>
                            </div>

                            <div class="gov-panel-body">
                                @if($featuredOfficeRow['nextInline'])
                                    <div class="gov-ticket-card gov-ticket-card-next">
                                        <p class="gov-ticket-label">Upcoming Ticket</p>
                                        <p class="gov-ticket-number gov-ticket-number-next" aria-live="polite">{{ $featuredOfficeRow['nextInline']->queue_number }}</p>
                                        <div class="gov-ticket-meta-block">
                                            <p class="gov-ticket-meta-label">Queued at</p>
                                            <p class="gov-ticket-meta-value">{{ $featuredOfficeRow['nextInline']->displayCreatedAt()?->format('h:i:s A') }}</p>
                                        </div>
                                    </div>
                                @else
                                    <div class="gov-ticket-empty">
                                        <p class="gov-ticket-empty-title">No waiting ticket in line</p>
                                        <p class="gov-ticket-empty-text">Newly issued tickets will appear here.</p>
                                    </div>
                                @endif
                            </div>
                        </section>
                    </div>

                    <section class="gov-monitor-panel gov-panel-recent" aria-labelledby="recent-{{ $featuredOfficeRow['office']->slug }}">
                        <div class="gov-panel-head">
                            <h3 id="recent-{{ $featuredOfficeRow['office']->slug }}" class="gov-font-heading gov-panel-title">Recent Transactions Today</h3>
                            @if($featuredOfficeRow['recentTransactions']->isNotEmpty())
                                <span class="gov-recent-count">{{ $featuredOfficeRow['recentTransactions']->count() }} records</span>
                            @endif
                        </div>

                        <div class="gov-panel-body">
                            @if($featuredOfficeRow['recentTransactions']->isNotEmpty())
                                <marquee behavior="scroll" direction="left" scrollamount="7" class="gov-ticker gov-marquee" aria-label="Recent transaction queue numbers for {{ $featuredOfficeRow['office']->name }}">
                                    @foreach($featuredOfficeRow['recentTransactions'] as $entry)
                                        <span class="gov-ticker-pill">{{ $entry->queue_number }}</span>
                                    @endforeach
                                </marquee>
                            @else
                                <div class="gov-ticket-empty gov-ticket-empty-soft">
                                    <p class="gov-ticket-empty-title">No recent transaction yet</p>
                                    <p class="gov-ticket-empty-text">Completed and not served transactions will show here.</p>
                                </div>
                            @endif
                        </div>
                    </section>
                </article>
            @else
                <section class="gov-monitor-panel gov-panel-empty-state" aria-label="No active queues">
                    <div class="gov-panel-head">
                        <h2 class="gov-font-heading gov-panel-title">No Active Queues</h2>
                        <span class="gov-status-badge gov-status-badge-idle">Idle</span>
                    </div>

                    <div class="gov-panel-body">
                        <div class="gov-ticket-empty">
                            <p class="gov-ticket-empty-title">No active queues across all offices</p>
                            <p class="gov-ticket-empty-text">Newly issued tickets will appear here once offices begin serving clients.</p>
                        </div>
                    </div>
                </section>
            @endif

        </main>
    </section>

    @foreach(collect([$featuredOfficeRow])->filter() as $officeRow)
        @include('livewire.office-admin.partials.live-monitor-announcer', [
            'office' => $officeRow['office'],
            'announcementPayload' => $officeRow['announcementPayload'],
        ])
    @endforeach
</div>

@include('livewire.office-admin.partials.live-monitor-clock-script')

@once
    <style>
        .gov-monitor-root {
            --gov-blue-950: #0b2f57;
            --gov-blue-900: #785416;
            --gov-blue-800: #2c5f97;
            --gov-blue-100: #d9e7f7;
            --gov-gold-500: #b88a2c;
            --gov-emerald-700: #0c7a58;
            --gov-emerald-100: #ddf8ec;
            --gov-sky-700: #ad5f12;
            --gov-sky-100: #e1f1ff;
            --gov-surface: #f4f7fb;
            --gov-ink-900: #152742;
            --gov-ink-700: #374d6a;
            --gov-ink-500: #64748b;
            --gov-border: #d4dfec;
            width: 100%;
            height: 100%;
            min-height: 100dvh;
            overflow: auto;
            background:
                radial-gradient(circle at 10% 5%, rgb(255 255 255 / 0.66), transparent 34%),
                linear-gradient(180deg, #e6edf5 0%, #dce5f1 100%);
        }

        .gov-monitor-shell {
            width: 100%;
            min-height: 100%;
            display: flex;
            flex-direction: column;
            color: var(--gov-ink-900);
        }

        .gov-monitor-header {
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: clamp(0.9rem, 2vw, 1.25rem) clamp(1rem, 2vw, 1.45rem);
            background:
                radial-gradient(circle at right top, rgb(255 255 255 / 0.12), transparent 50%),
                linear-gradient(125deg, var(--gov-blue-950), var(--gov-blue-900));
            border-bottom: 1px solid rgb(255 255 255 / 0.2);
            animation: gov-monitor-rise 360ms ease-out both;
        }

        .gov-monitor-header::after {
            content: '';
            position: absolute;
            inset: auto 0 0;
            height: 0.28rem;
            background: linear-gradient(90deg, #1d4ed8 0%, #1d4ed8 66%, #b88a2c 66%, #b88a2c 84%, #be123c 84%, #be123c 100%);
        }

        .gov-monitor-brand {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            min-width: 0;
        }

        .gov-monitor-seal {
            width: clamp(3rem, 6vw, 4rem);
            height: clamp(3rem, 6vw, 4rem);
            border-radius: 999px;
            border: 2px solid rgb(255 255 255 / 0.5);
            background: rgb(255 255 255 / 0.92);
            object-fit: cover;
            object-position: center;
            flex-shrink: 0;
        }

        .gov-monitor-kicker {
            margin: 0;
            font-size: clamp(0.62rem, 1vw, 0.72rem);
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: rgb(219 234 254 / 0.95);
            font-weight: 700;
        }

        .gov-monitor-title {
            margin: 0.22rem 0 0;
            color: #fff;
            font-size: clamp(1.65rem, 3.2vw, 2.9rem);
            line-height: 1.03;
            font-weight: 700;
        }

        .gov-monitor-subtitle {
            margin: 0.28rem 0 0;
            color: rgb(226 232 240 / 0.95);
            font-size: clamp(0.72rem, 1.1vw, 0.88rem);
            letter-spacing: 0.06em;
            text-transform: uppercase;
            font-weight: 600;
        }

        .gov-monitor-clock {
            text-align: right;
            flex-shrink: 0;
        }

        .gov-monitor-location {
            margin: 0;
            font-size: clamp(0.62rem, 0.9vw, 0.74rem);
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: rgb(191 219 254 / 0.95);
            font-weight: 700;
        }

        .gov-monitor-time {
            margin: 0.12rem 0 0;
            color: #fff;
            font-size: clamp(1.5rem, 2.3vw, 2.35rem);
            line-height: 1;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .gov-monitor-date {
            margin: 0.2rem 0 0;
            color: rgb(219 234 254 / 0.95);
            font-size: clamp(0.68rem, 0.95vw, 0.84rem);
            font-weight: 600;
        }

        .gov-monitor-main {
            flex: 1;
            min-height: 0;
            display: flex;
            flex-direction: column;
            gap: 0.95rem;
            padding: clamp(0.75rem, 1.8vw, 1.4rem);
            overflow: auto;
        }

        .gov-office-monitor-list {
            display: grid;
            grid-template-columns: 1fr;
            gap: 0.95rem;
        }

        .gov-office-monitor-card {
            border-radius: 1rem;
            border: 1px solid var(--gov-border);
            background: rgb(255 255 255 / 0.74);
            box-shadow: 0 20px 34px -34px rgb(15 63 115 / 0.75);
            padding: 0.95rem;
            display: flex;
            flex-direction: column;
            gap: 0.95rem;
            animation: gov-monitor-rise 380ms ease-out both;
        }

        .gov-office-monitor-card-featured {
            min-height: 0;
            overflow: visible;
        }

        .gov-office-monitor-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
        }

        .gov-office-monitor-kicker {
            margin: 0;
            font-size: 0.68rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            font-weight: 700;
            color: var(--gov-ink-500);
        }

        .gov-office-monitor-title {
            margin: 0.24rem 0 0;
            color: var(--gov-ink-900);
            font-size: clamp(1.2rem, 1.6vw, 1.65rem);
            line-height: 1.08;
        }

        .gov-office-monitor-chip {
            border-radius: 999px;
            background: #eef4fb;
            border: 1px solid #c8d8eb;
            color: #1d4ed8;
            padding: 0.34rem 0.72rem;
            font-size: 0.72rem;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            font-weight: 700;
            flex-shrink: 0;
        }

        .gov-monitor-grid {
            min-height: 0;
            display: grid;
            grid-template-columns: minmax(0, 1.65fr) minmax(0, 1fr);
            gap: 0.95rem;
        }

        .gov-monitor-panel {
            border-radius: 1rem;
            border: 1px solid var(--gov-border);
            background: rgb(255 255 255 / 0.78);
            box-shadow: 0 20px 34px -34px rgb(15 63 115 / 0.75);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            min-height: 0;
        }

        .gov-panel-head {
            padding: 0.9rem 1rem;
            border-bottom: 1px solid #dce5f1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.7rem;
            background:
                radial-gradient(circle at left top, rgb(219 234 254 / 0.52), transparent 40%),
                linear-gradient(180deg, #fdfefe, #f6f9fd);
        }

        .gov-panel-title {
            margin: 0;
            font-size: clamp(1.1rem, 1.4vw, 1.6rem);
            line-height: 1.1;
            color: var(--gov-ink-900);
        }

        .gov-status-badge {
            border-radius: 999px;
            padding: 0.3rem 0.7rem;
            font-size: 0.72rem;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            font-weight: 700;
            border: 1px solid;
            flex-shrink: 0;
        }

        .gov-status-badge-active {
            background: var(--gov-emerald-100);
            border-color: #9be5c8;
            color: var(--gov-emerald-700);
        }

        .gov-status-badge-queue {
            background: var(--gov-sky-100);
            border-color: #afd8fb;
            color: var(--gov-sky-700);
        }

        .gov-status-badge-idle {
            background: #eef2f7;
            border-color: #cbd5e1;
            color: #64748b;
        }

        .gov-panel-body {
            flex: 1;
            min-height: 0;
            padding: 0.95rem;
            display: flex;
            flex-direction: column;
        }

        .gov-ticket-card {
            border-radius: 0.92rem;
            border: 1px solid;
            padding: 0.95rem;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            gap: clamp(0.7rem, 1vw, 1rem);
            min-height: 0;
        }

        .gov-ticket-card-serving {
            border-color: #a7e6cb;
            background: linear-gradient(180deg, #f2fdf8 0%, #e5f8ef 100%);
        }

        .gov-ticket-card-next {
            border-color: #b6dcf9;
            background: linear-gradient(180deg, #f3f9ff 0%, #e8f3ff 100%);
        }

        .gov-window-monitor-list {
            display: grid;
            gap: 0.8rem;
        }

        .gov-window-monitor-card {
            border-radius: 0.92rem;
            border: 1px solid #a7e6cb;
            background: linear-gradient(180deg, #f2fdf8 0%, #e5f8ef 100%);
            padding: 0.95rem;
            display: grid;
            gap: 0.8rem;
        }

        .gov-window-monitor-ticket {
            margin: 0.35rem 0 0;
            color: #067a55;
            font-size: clamp(2rem, 4vw, 3rem);
            line-height: 0.95;
            font-weight: 800;
            letter-spacing: -0.03em;
        }

        .gov-ticket-label {
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.11em;
            font-size: clamp(0.64rem, 0.85vw, 0.8rem);
            font-weight: 700;
            color: #1f4f7f;
            flex-shrink: 0;
        }

        .gov-ticket-number {
            margin: 0.5rem 0;
            line-height: 0.88;
            text-align: center;
            font-weight: 800;
            letter-spacing: -0.03em;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
            word-break: keep-all;
            overflow: hidden;
            text-overflow: ellipsis;
            flex-shrink: 0;
        }

        .gov-ticket-number-serving {
            color: #067a55;
            font-size: clamp(3rem, 7vw, 6rem);
        }

        .gov-ticket-number-next {
            color: #1066a9;
            font-size: clamp(2.25rem, 5vw, 4.6rem);
        }

        .gov-ticket-meta-block {
            border-radius: 0.72rem;
            background: rgb(255 255 255 / 0.58);
            border: 1px solid rgb(203 213 225 / 0.75);
            padding: 0.65rem 0.78rem;
            margin-top: auto;
            flex-shrink: 0;
        }

        .gov-ticket-meta-label {
            margin: 0;
            color: var(--gov-ink-700);
            font-size: 0.86rem;
        }

        .gov-ticket-meta-value {
            margin: 0.05rem 0 0;
            color: var(--gov-ink-900);
            font-size: clamp(1.05rem, 1.5vw, 1.4rem);
            font-weight: 700;
            font-variant-numeric: tabular-nums;
        }

        .gov-ticket-empty {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            border-radius: 0.92rem;
            border: 1px dashed #bfd0e6;
            background: #f8fbff;
            padding: 1rem;
        }

        .gov-ticket-empty-soft {
            min-height: 5.4rem;
            padding: 0.9rem;
        }

        .gov-ticket-empty-title {
            margin: 0;
            color: var(--gov-ink-900);
            font-size: clamp(1.05rem, 1.3vw, 1.3rem);
            font-weight: 700;
        }

        .gov-ticket-empty-text {
            margin: 0.35rem 0 0;
            color: var(--gov-ink-500);
            font-size: 0.9rem;
            line-height: 1.45;
        }

        .gov-panel-recent .gov-panel-head {
            background:
                radial-gradient(circle at left top, rgb(254 243 199 / 0.56), transparent 40%),
                linear-gradient(180deg, #fffef7, #fff9eb);
        }

        .gov-recent-count {
            border-radius: 999px;
            background: #fef3c7;
            border: 1px solid #f4d78f;
            color: #8a4b06;
            padding: 0.3rem 0.65rem;
            font-size: 0.72rem;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            font-weight: 700;
            flex-shrink: 0;
        }

        .gov-ticker {
            height: 100%;
            min-height: 5.4rem;
            border-radius: 0.86rem;
            border: 1px solid #d8e3f0;
            background: #f7f9fc;
            overflow: hidden;
            display: flex;
            align-items: center;
            position: relative;
        }

        .gov-ticker::before,
        .gov-ticker::after {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            width: 3rem;
            pointer-events: none;
            z-index: 2;
        }

        .gov-ticker::before {
            left: 0;
            background: linear-gradient(90deg, #f7f9fc 20%, rgb(247 249 252 / 0));
        }

        .gov-ticker::after {
            right: 0;
            background: linear-gradient(270deg, #f7f9fc 20%, rgb(247 249 252 / 0));
        }

        .gov-marquee {
            padding: 1rem 0.95rem;
            line-height: 1;
        }

        .gov-marquee .gov-ticker-pill {
            margin-right: 0.7rem;
            vertical-align: middle;
        }

        .gov-ticker-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 11.5rem;
            height: 3.2rem;
            border-radius: 0.72rem;
            border: 1px solid #d0d9e6;
            background: #fff;
            color: #1e2e48;
            font-size: clamp(1.3rem, 2vw, 2rem);
            font-weight: 700;
            letter-spacing: -0.02em;
            font-variant-numeric: tabular-nums;
            box-shadow: 0 8px 18px -16px rgb(15 63 115 / 0.95);
        }

        @media (max-width: 1200px) {
            .gov-monitor-grid {
                grid-template-columns: 1fr;
            }

            .gov-ticket-number-serving {
                font-size: clamp(2.8rem, 10vw, 5.4rem);
            }

            .gov-ticket-number-next {
                font-size: clamp(2.2rem, 8vw, 4.4rem);
            }
        }

        @media (max-width: 900px) {
            .gov-monitor-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .gov-monitor-clock {
                text-align: left;
            }

            .gov-monitor-main {
                padding: 0.75rem;
            }

            .gov-office-monitor-head {
                align-items: flex-start;
                flex-direction: column;
            }
        }

        @media (max-width: 640px) {
            .gov-panel-head,
            .gov-panel-body,
            .gov-office-monitor-card {
                padding-left: 0.78rem;
                padding-right: 0.78rem;
            }

            .gov-office-monitor-card {
                padding-top: 0.78rem;
                padding-bottom: 0.78rem;
            }

            .gov-ticker-pill {
                min-width: 9rem;
                height: 2.8rem;
                font-size: 1.1rem;
            }
        }

        @keyframes gov-monitor-rise {
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
            .gov-monitor-header,
            .gov-office-monitor-card,
            .gov-monitor-panel {
                animation: none;
            }
        }
    </style>
@endonce
