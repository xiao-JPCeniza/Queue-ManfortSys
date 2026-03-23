<div
    wire:poll.keep-alive.2s="tick"
    data-session-keepalive="always"
    data-live-monitor-root
    data-has-current-transaction="{{ $hasCurrentTransaction ? 'true' : 'false' }}"
    data-has-queued-next-inline="{{ $hasQueuedNextInline ? 'true' : 'false' }}"
    data-idle-video-delay-ms="60000"
    class="gov-monitor-root"
>
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

                    <section class="gov-monitor-panel gov-panel-windows" aria-labelledby="windows-{{ $featuredOfficeRow['office']->slug }}">
                        <div class="gov-panel-head">
                            <h3 id="windows-{{ $featuredOfficeRow['office']->slug }}" class="gov-font-heading gov-panel-title">Windows Currently Serving</h3>
                            <span class="gov-status-badge {{ $featuredOfficeRow['servingEntries']->isNotEmpty() ? 'gov-status-badge-active' : 'gov-status-badge-idle' }}">
                                {{ $featuredOfficeRow['servingEntries']->isNotEmpty() ? $featuredOfficeRow['servingEntries']->count().' Active' : 'All Idle' }}
                            </span>
                        </div>

                        <div class="gov-panel-body">
                            @php
                                $servingEntriesByWindow = $featuredOfficeRow['servingEntries']->keyBy(fn ($entry) => $entry->service_window_number ?? 1);
                                $windowNumbers = range(1, max(1, (int) ($featuredOfficeRow['window_count'] ?? 1)));
                            @endphp

                            <div
                                class="gov-window-monitor-list"
                                style="--gov-window-columns: {{ count($windowNumbers) }};"
                                aria-label="Service windows currently serving in {{ $featuredOfficeRow['office']->name }}"
                            >
                                @foreach($windowNumbers as $windowNumber)
                                    @php
                                        $entry = $servingEntriesByWindow->get($windowNumber);
                                        $windowLabel = $featuredOfficeRow['office']->serviceWindowLabel($windowNumber);
                                    @endphp
                                    <article class="gov-window-monitor-card {{ $entry ? '' : 'gov-window-monitor-card-idle' }}">
                                        <div>
                                            <p class="gov-ticket-label">{{ $windowLabel }}</p>
                                            @if($entry)
                                                <p class="gov-window-monitor-ticket">{{ $entry->queue_number }}</p>
                                            @else
                                                <div class="gov-window-monitor-placeholder" aria-label="{{ $windowLabel }} is currently idle"></div>
                                            @endif
                                        </div>

                                        <div class="gov-ticket-meta-block {{ $entry ? '' : 'gov-ticket-meta-block-idle' }}">
                                            <p class="gov-ticket-meta-label">Called at</p>
                                            <p class="gov-ticket-meta-value {{ $entry ? '' : 'gov-ticket-meta-value-idle' }}">{{ $entry?->displayCalledAt()?->format('h:i:s A') ?? '' }}</p>
                                        </div>
                                    </article>
                                @endforeach
                            </div>
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
    @include('livewire.office-admin.partials.live-monitor-idle-video')

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
            overflow: hidden;
            background:
                radial-gradient(circle at 10% 5%, rgb(255 255 255 / 0.66), transparent 34%),
                linear-gradient(180deg, #e6edf5 0%, #dce5f1 100%);
        }

        .gov-monitor-shell {
            width: 100%;
            min-height: 100%;
            height: 100dvh;
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
            gap: 0.85rem;
            padding: clamp(0.72rem, 1.5vw, 1rem) clamp(0.85rem, 1.8vw, 1.2rem);
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
            width: clamp(2.6rem, 5vw, 3.35rem);
            height: clamp(2.6rem, 5vw, 3.35rem);
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
            font-size: clamp(1.65rem, 2.8vw, 2.45rem);
            line-height: 1.03;
            font-weight: 700;
        }

        .gov-monitor-subtitle {
            margin: 0.28rem 0 0;
            color: rgb(226 232 240 / 0.95);
            font-size: clamp(0.76rem, 1.12vw, 0.96rem);
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
            font-size: clamp(0.74rem, 1vw, 0.9rem);
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: rgb(191 219 254 / 0.95);
            font-weight: 700;
        }

        .gov-monitor-time {
            margin: 0.12rem 0 0;
            color: #fff;
            font-size: clamp(1.55rem, 2.45vw, 2.45rem);
            line-height: 1;
            font-weight: 800;
            letter-spacing: -0.02em;
        }

        .gov-monitor-date {
            margin: 0.2rem 0 0;
            color: rgb(219 234 254 / 0.95);
            font-size: clamp(0.76rem, 0.96vw, 0.92rem);
            font-weight: 600;
        }

        .gov-monitor-main {
            flex: 1;
            min-height: 0;
            display: flex;
            flex-direction: column;
            gap: 0.65rem;
            padding: clamp(0.55rem, 1.1vw, 0.9rem);
            overflow: hidden;
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
            padding: 0.72rem;
            display: flex;
            flex-direction: column;
            gap: 0.65rem;
            animation: gov-monitor-rise 380ms ease-out both;
        }

        .gov-office-monitor-card-featured {
            flex: 1;
            min-height: 0;
            overflow: hidden;
        }

        .gov-office-monitor-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.55rem;
        }

        .gov-office-monitor-kicker {
            margin: 0;
            font-size: 0.8rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            font-weight: 700;
            color: var(--gov-ink-500);
        }

        .gov-office-monitor-title {
            margin: 0.24rem 0 0;
            color: var(--gov-ink-900);
            font-size: clamp(1.22rem, 1.35vw, 1.5rem);
            line-height: 1.08;
        }

        .gov-office-monitor-chip {
            border-radius: 999px;
            background: #eef4fb;
            border: 1px solid #c8d8eb;
            color: #1d4ed8;
            padding: 0.28rem 0.62rem;
            font-size: 0.78rem;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            font-weight: 700;
            flex-shrink: 0;
        }

        .gov-monitor-grid {
            min-height: 0;
            display: grid;
            grid-template-columns: minmax(0, 1.65fr) minmax(0, 1fr);
            gap: 0.65rem;
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
            padding: 0.65rem 0.8rem;
            border-bottom: 1px solid #dce5f1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.55rem;
            background:
                radial-gradient(circle at left top, rgb(219 234 254 / 0.52), transparent 40%),
                linear-gradient(180deg, #fdfefe, #f6f9fd);
        }

        .gov-panel-title {
            margin: 0;
            font-size: clamp(1.2rem, 1.35vw, 1.42rem);
            line-height: 1.1;
            color: var(--gov-ink-900);
        }

        .gov-status-badge {
            border-radius: 999px;
            padding: 0.24rem 0.58rem;
            font-size: 0.78rem;
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
            padding: 0.7rem;
            display: flex;
            flex-direction: column;
        }

        .gov-ticket-card {
            border-radius: 0.92rem;
            border: 1px solid;
            padding: 0.75rem;
            flex: 1;
            display: grid;
            grid-template-rows: auto minmax(0, 1fr) auto;
            align-items: stretch;
            gap: 0.55rem;
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
            grid-template-columns: repeat(var(--gov-window-columns, 1), minmax(0, 1fr));
            align-items: start;
            align-content: start;
            gap: 0.42rem;
            flex: 0 0 auto;
        }

        .gov-window-monitor-card {
            border-radius: 0.92rem;
            border: 1px solid #a7e6cb;
            background: linear-gradient(180deg, #f2fdf8 0%, #e5f8ef 100%);
            padding: 0.48rem;
            display: grid;
            grid-template-rows: auto auto;
            align-content: start;
            gap: 0.28rem;
            min-height: 0;
        }

        .gov-window-monitor-card-idle {
            border-color: #d8e3f0;
            background: linear-gradient(180deg, #fbfdff 0%, #f2f7fc 100%);
        }

        .gov-window-monitor-ticket {
            margin: 0.1rem 0 0;
            color: #067a55;
            font-size: clamp(1.18rem, 1.45vw, 1.62rem);
            line-height: 0.95;
            font-weight: 800;
            letter-spacing: -0.03em;
        }

        .gov-window-monitor-placeholder {
            margin-top: 0.1rem;
            min-height: clamp(0.95rem, 1.3vw, 1.25rem);
            border-radius: 0.78rem;
            border: 1px dashed #cbd5e1;
            background: linear-gradient(180deg, rgb(255 255 255 / 0.55), rgb(241 245 249 / 0.82));
        }

        .gov-window-monitor-card .gov-ticket-label {
            font-size: clamp(0.62rem, 0.68vw, 0.74rem);
            letter-spacing: 0.08em;
        }

        .gov-window-monitor-card .gov-ticket-meta-block {
            padding: 0.32rem 0.42rem;
        }

        .gov-window-monitor-card .gov-ticket-meta-label {
            font-size: 0.7rem;
        }

        .gov-window-monitor-card .gov-ticket-meta-value {
            font-size: clamp(0.82rem, 0.88vw, 0.94rem);
        }

        .gov-ticket-label {
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.11em;
            font-size: clamp(0.76rem, 0.95vw, 0.96rem);
            font-weight: 700;
            color: #1f4f7f;
            flex-shrink: 0;
        }

        .gov-ticket-number {
            margin: 0;
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
            align-self: center;
        }

        .gov-ticket-number-serving {
            color: #067a55;
            font-size: clamp(3.65rem, 6.9vw, 7.1rem);
        }

        .gov-ticket-number-next {
            color: #1066a9;
            font-size: clamp(2.7rem, 5.1vw, 4.9rem);
        }

        .gov-ticket-meta-block {
            border-radius: 0.72rem;
            background: rgb(255 255 255 / 0.58);
            border: 1px solid rgb(203 213 225 / 0.75);
            padding: 0.42rem 0.56rem;
            margin-top: 0.15rem;
            flex-shrink: 0;
        }

        .gov-ticket-card > .gov-ticket-meta-block {
            margin-top: 0;
            align-self: stretch;
        }

        .gov-ticket-meta-block-idle {
            background: rgb(255 255 255 / 0.46);
            border-style: dashed;
        }

        .gov-ticket-meta-label {
            margin: 0;
            color: var(--gov-ink-700);
            font-size: 0.95rem;
        }

        .gov-ticket-meta-value {
            margin: 0.05rem 0 0;
            color: var(--gov-ink-900);
            font-size: clamp(1.12rem, 1.25vw, 1.32rem);
            font-weight: 700;
            font-variant-numeric: tabular-nums;
        }

        .gov-ticket-meta-value-idle {
            min-height: 1.45em;
            color: #94a3b8;
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
            padding: 0.8rem;
        }

        .gov-ticket-empty-soft {
            min-height: 5.4rem;
            padding: 0.9rem;
        }

        .gov-ticket-empty-title {
            margin: 0;
            color: var(--gov-ink-900);
            font-size: clamp(1.3rem, 1.5vw, 1.65rem);
            font-weight: 700;
        }

        .gov-ticket-empty-text {
            margin: 0.35rem 0 0;
            color: var(--gov-ink-500);
            font-size: clamp(0.96rem, 1.08vw, 1.16rem);
            line-height: 1.45;
        }

        .gov-panel-windows .gov-panel-head {
            background:
                radial-gradient(circle at left top, rgb(209 250 229 / 0.55), transparent 40%),
                linear-gradient(180deg, #fcfffe, #eefcf5);
        }

        .gov-panel-windows .gov-panel-body {
            justify-content: flex-start;
        }

        @media (min-width: 1201px) {
            .gov-office-monitor-card-featured > .gov-monitor-grid,
            .gov-office-monitor-card-featured > .gov-panel-windows {
                min-height: 0;
            }

            .gov-office-monitor-card-featured > .gov-monitor-grid {
                flex: 1.28 1 0%;
            }

            .gov-office-monitor-card-featured > .gov-panel-windows {
                flex: 0.72 1 0%;
            }
        }

        @media (max-width: 1200px) {
            .gov-monitor-grid {
                grid-template-columns: 1fr;
            }

            .gov-ticket-number-serving {
                font-size: clamp(3.55rem, 10.9vw, 6.45rem);
            }

            .gov-ticket-number-next {
                font-size: clamp(2.8rem, 8.5vw, 5rem);
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

            .gov-window-monitor-list {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .gov-window-monitor-ticket {
                font-size: clamp(1.25rem, 4.8vw, 1.7rem);
            }
        }

        @media (max-height: 900px) {
            .gov-monitor-header {
                padding: 0.62rem 0.9rem;
            }

            .gov-monitor-main {
                gap: 0.5rem;
                padding: 0.5rem 0.65rem;
            }

            .gov-office-monitor-card {
                padding: 0.6rem;
                gap: 0.55rem;
            }

            .gov-monitor-grid {
                gap: 0.5rem;
            }

            .gov-panel-head,
            .gov-panel-body {
                padding: 0.55rem 0.68rem;
            }

            .gov-ticket-card,
            .gov-window-monitor-card {
                padding: 0.5rem;
            }

            .gov-ticket-number-serving {
                font-size: clamp(3rem, 5.6vw, 5.55rem);
            }

            .gov-ticket-number-next {
                font-size: clamp(2.3rem, 4.1vw, 3.8rem);
            }

            .gov-window-monitor-ticket {
                font-size: clamp(1.45rem, 2.2vw, 1.9rem);
            }

            .gov-window-monitor-placeholder {
                min-height: 1.35rem;
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
