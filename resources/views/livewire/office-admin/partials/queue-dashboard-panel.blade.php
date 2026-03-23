@php($showLiveMonitor = $showLiveMonitor ?? false)
@php($liveMonitorRoute = $liveMonitorRoute ?? '')
@php($liveMonitorLabel = $liveMonitorLabel ?? 'Open Live Monitor')
@php($isAdvancedQueueOffice = in_array($office->slug, ['hrmo', 'mho', 'mswdo', 'menro', 'business-permits', 'bplo', 'treasury', 'accounting', 'civil-registry', 'assessors-office'], true))
@php($manilaNow = now('Asia/Manila'))

<div class="gov-queue-shell">
    <section class="gov-queue-masthead" aria-label="Queue operations heading">
        <div>
            <h2 class="gov-font-heading gov-masthead-title">{{ $office->name }} Queue Operations Desk</h2>
            <p class="gov-masthead-meta">Official live transaction console for public service processing.</p>
        </div>

        <div class="gov-masthead-meta-wrap" data-manila-clock data-manila-clock-style="desk" data-manila-clock-suffix=" PHT" data-manila-now="{{ $manilaNow->toIso8601String() }}" aria-label="Current date and time">
            <span class="gov-masthead-chip gov-masthead-chip-date" data-manila-clock-date>{{ $manilaNow->format('F j, Y') }}</span>
            <span class="gov-masthead-chip gov-masthead-chip-time" data-manila-clock-time>{{ $manilaNow->format('h:i:s A') }} PHT</span>
        </div>
    </section>

    <div class="grid grid-cols-1 xl:grid-cols-5 gap-6 items-start">
        <section class="gov-card gov-serving-card xl:col-span-3" aria-labelledby="serving-heading">
            <div class="gov-card-head">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <h2 id="serving-heading" class="gov-font-heading gov-card-title">
                        {{ $usesMultipleServiceWindows ? 'Service Windows' : 'Currently Serving' }}
                    </h2>
                    <span class="gov-status-chip {{ $servingEntries->isNotEmpty() ? 'gov-status-chip-active' : 'gov-status-chip-idle' }}">
                        {{ $servingEntries->count() }} active
                    </span>
                </div>
                <p class="gov-card-subtitle">
                    Call the next number per window to update the live monitor and announce exactly where the client should proceed.
                </p>
            </div>

            <div class="gov-card-body">
                <div class="gov-window-grid">
                    @foreach($serviceWindows as $window)
                        @php($windowEntry = $window['entry'])

                        <article class="gov-window-card">
                            <div class="gov-window-head">
                                <div>
                                    <p class="gov-window-kicker">{{ $window['label'] }}</p>
                                    <h3 class="gov-window-title">
                                        {{ $windowEntry?->queue_number ?? 'Available' }}
                                    </h3>
                                </div>

                                <span class="gov-status-chip {{ $windowEntry ? 'gov-status-chip-active' : 'gov-status-chip-idle' }}">
                                    {{ $windowEntry ? 'Serving' : 'Idle' }}
                                </span>
                            </div>

                            @if($windowEntry)
                                <div class="gov-ticket-board gov-ticket-board-window">
                                    <p class="gov-ticket-label">Ticket Number</p>
                                    <p class="gov-ticket-number" aria-label="{{ $window['label'] }} serving {{ $windowEntry->queue_number }}">
                                        {{ $windowEntry->queue_number }}
                                    </p>
                                    <p class="gov-client-type-chip {{ $windowEntry->isPriorityClient() ? 'gov-client-type-chip-priority' : 'gov-client-type-chip-regular' }}">
                                        {{ $windowEntry->client_type_label }}
                                    </p>
                                    <p class="gov-ticket-meta">Called at {{ $windowEntry->displayCalledAt()?->format('h:i A') }}</p>
                                </div>
                            @else
                                <div class="gov-ticket-empty gov-ticket-empty-window">
                                    <p>{{ $window['label'] }} is ready for the next client.</p>
                                </div>
                            @endif

                            <div class="gov-window-actions">
                                <button
                                    wire:click="callNext({{ $window['number'] }})"
                                    wire:loading.attr="disabled"
                                    wire:target="callNext"
                                    type="button"
                                    class="gov-btn gov-btn-warning"
                                >
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 5 6 9H3v6h3l5 4V5Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.5 8.5a5 5 0 0 1 0 7" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.5 6a9 9 0 0 1 0 12" />
                                    </svg>
                                    Call Next
                                </button>

                                @if($windowEntry)
                                    <button
                                        wire:click="complete({{ $windowEntry->id }})"
                                        type="button"
                                        class="gov-btn gov-btn-complete"
                                    >
                                        Mark Completed
                                    </button>
                                @endif
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <div class="xl:col-span-2 space-y-4">
            <section class="gov-card gov-quick-card" aria-labelledby="quick-actions-heading">
                <div class="gov-card-head">
                    <h2 id="quick-actions-heading" class="gov-font-heading gov-card-title">Quick Actions</h2>
                </div>

                <div class="gov-card-body pt-4">
                    <div class="grid grid-cols-1 gap-2.5">
                        @if($showLiveMonitor && $liveMonitorRoute !== '')
                            <a
                                href="{{ route($liveMonitorRoute, $office->slug) }}"
                                class="gov-btn gov-btn-primary"
                            >
                                {{ $liveMonitorLabel }}
                            </a>
                        @endif

                        <button
                            type="button"
                            wire:click="resetTickets"
                            wire:confirm="Reset queue numbering for {{ $office->name }} to 1? This will clear this office's generated tickets for today."
                            wire:loading.attr="disabled"
                            wire:target="resetTickets"
                            class="gov-btn gov-btn-secondary"
                        >
                            Reset Queue Number
                        </button>

                        @if($isAdvancedQueueOffice)
                            <button
                                type="button"
                                wire:click="clearTransaction"
                                wire:loading.attr="disabled"
                                wire:target="clearTransaction"
                                class="gov-btn gov-btn-danger"
                            >
                                Clear Waiting Line
                            </button>
                        @endif
                    </div>
                </div>
            </section>

            <section class="gov-card gov-waiting-card overflow-hidden" aria-labelledby="waiting-heading">
                <div class="gov-card-head gov-waiting-head">
                    <h2 id="waiting-heading" class="gov-font-heading gov-card-title">Waiting Line</h2>
                    <span class="gov-queue-count">
                        {{ $waiting->count() }} in queue
                    </span>
                </div>

                <div class="max-h-80 overflow-y-auto">
                    @forelse($waiting as $entry)
                        <div class="gov-waiting-row">
                            <div>
                                <span class="gov-waiting-ticket">{{ $entry->queue_number }}</span>
                                <p class="gov-waiting-type {{ $entry->isPriorityClient() ? 'gov-waiting-type-priority' : 'gov-waiting-type-regular' }}">
                                    {{ $entry->client_type_label }}
                                </p>
                                <p class="gov-waiting-time">Joined {{ $entry->displayCreatedAt()?->format('h:i A') }}</p>
                            </div>
                            <span class="gov-waiting-order">#{{ $loop->iteration }}</span>
                        </div>
                    @empty
                        <p class="px-5 py-8 text-slate-500 text-center text-sm">No one waiting.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>
</div>

@include('livewire.office-admin.partials.live-monitor-clock-script')

@once
    <style>
        .gov-queue-shell {
            --gov-blue-950: #0c2e53;
            --gov-blue-900: #154777;
            --gov-blue-800: #2a5f97;
            --gov-blue-100: #dce9f8;
            --gov-gold-500: #b98a2b;
            --gov-gold-100: #f7efdc;
            --gov-emerald-600: #0f8a62;
            --gov-emerald-700: #0d7453;
            --gov-amber-500: #f4a30f;
            --gov-amber-600: #d48806;
            --gov-red-600: #b42339;
            --gov-ink-900: #1b2d46;
            --gov-ink-700: #334a68;
            --gov-ink-500: #5f7592;
            --gov-border: #d7e1ee;
            --gov-surface: #ffffff;
            display: grid;
            gap: 1rem;
        }

        .gov-queue-masthead {
            position: relative;
            overflow: hidden;
            border: 1px solid #c8d8eb;
            border-radius: 1rem;
            padding: 1.15rem 1.2rem;
            background:
                radial-gradient(circle at top right, rgb(255 255 255 / 0.14), transparent 46%),
                linear-gradient(125deg, var(--gov-blue-950), var(--gov-blue-900));
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            animation: gov-fade-up 360ms ease-out both;
        }

        .gov-queue-masthead::after {
            content: '';
            position: absolute;
            inset: auto 0 0;
            height: 0.3rem;
            background: linear-gradient(90deg, #1d4ed8 0%, #1d4ed8 64%, #b98a2b 64%, #b98a2b 84%, #be123c 84%, #be123c 100%);
        }

        .gov-masthead-kicker {
            margin: 0;
            font-size: 0.69rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: rgb(219 234 254 / 0.98);
            font-weight: 600;
        }

        .gov-masthead-title {
            margin: 0.32rem 0 0;
            font-size: clamp(1.15rem, 2vw, 1.6rem);
            line-height: 1.15;
            color: #fff;
            font-weight: 700;
        }

        .gov-masthead-meta {
            margin: 0.38rem 0 0;
            color: rgb(226 232 240 / 0.95);
            font-size: 0.84rem;
            line-height: 1.5;
        }

        .gov-masthead-meta-wrap {
            display: inline-flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 0.45rem;
        }

        .gov-masthead-chip {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            padding: 0.35rem 0.7rem;
            font-size: 0.72rem;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            font-weight: 600;
            border: 1px solid rgb(255 255 255 / 0.32);
            color: #fff;
        }

        .gov-masthead-chip-date {
            background: rgb(22 101 52 / 0.32);
        }

        .gov-masthead-chip-time {
            background: rgb(185 138 43 / 0.32);
        }

        .gov-card {
            background: var(--gov-surface);
            border: 1px solid var(--gov-border);
            border-radius: 1rem;
            box-shadow: 0 16px 30px -28px rgb(21 71 119 / 0.55);
            overflow: hidden;
            animation: gov-fade-up 420ms ease-out both;
        }

        .gov-card-head {
            padding: 1rem 1.1rem;
            border-bottom: 1px solid #e2e8f0;
            background:
                radial-gradient(circle at left top, rgb(220 233 248 / 0.46), transparent 44%),
                linear-gradient(180deg, #fdfefe 0%, #f7fafe 100%);
        }

        .gov-card-body {
            padding: 1.1rem;
        }

        .gov-card-title {
            margin: 0;
            font-size: 1.55rem;
            line-height: 1.1;
            color: var(--gov-ink-900);
        }

        .gov-card-subtitle {
            margin: 0.48rem 0 0;
            color: var(--gov-ink-500);
            font-size: 0.84rem;
            line-height: 1.55;
        }

        .gov-status-chip {
            border-radius: 999px;
            padding: 0.32rem 0.64rem;
            font-size: 0.72rem;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            font-weight: 700;
            border: 1px solid;
        }

        .gov-status-chip-active {
            background: #def7ec;
            border-color: #8ad3b7;
            color: #0f7660;
        }

        .gov-status-chip-idle {
            background: #edf2f7;
            border-color: #cbd5e1;
            color: #475569;
        }

        .gov-window-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(18rem, 1fr));
            gap: 1rem;
        }

        .gov-window-card {
            border: 1px solid #dce5ef;
            border-radius: 1rem;
            background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
            padding: 1rem;
            display: grid;
            gap: 0.9rem;
        }

        .gov-window-head {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 0.75rem;
        }

        .gov-window-kicker {
            margin: 0;
            color: var(--gov-ink-500);
            text-transform: uppercase;
            letter-spacing: 0.11em;
            font-size: 0.7rem;
            font-weight: 700;
        }

        .gov-window-title {
            margin: 0.3rem 0 0;
            color: var(--gov-ink-900);
            font-size: 1.15rem;
            line-height: 1.1;
            font-weight: 800;
        }

        .gov-ticket-board {
            border-radius: 0.95rem;
            border: 1px solid #bfe7d9;
            background:
                linear-gradient(180deg, #f4fdf9 0%, #e9f8f2 100%);
            padding: 1.05rem;
        }

        .gov-ticket-board-window {
            min-height: 13.5rem;
        }

        .gov-ticket-label {
            margin: 0;
            color: #0f7660;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-weight: 700;
            font-size: 0.69rem;
        }

        .gov-ticket-number {
            margin: 0.4rem 0 0;
            font-size: clamp(2.35rem, 5vw, 3.45rem);
            line-height: 1;
            letter-spacing: -0.025em;
            font-weight: 800;
            color: var(--gov-emerald-600);
        }

        .gov-ticket-meta {
            margin: 0.45rem 0 0;
            color: #0f7660;
            font-size: 0.9rem;
        }

        .gov-client-type-chip,
        .gov-waiting-type {
            display: inline-flex;
            align-items: center;
            border-radius: 999px;
            border: 1px solid;
            font-size: 0.72rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .gov-client-type-chip {
            margin-top: 0.55rem;
            padding: 0.34rem 0.7rem;
        }

        .gov-client-type-chip-regular,
        .gov-waiting-type-regular {
            background: #eef4ff;
            border-color: #bfd5f6;
            color: #1d4ed8;
        }

        .gov-client-type-chip-priority,
        .gov-waiting-type-priority {
            background: #fff4db;
            border-color: #f4d28f;
            color: #9a5d06;
        }

        .gov-ticket-empty {
            border-radius: 0.95rem;
            border: 1px dashed #bcc9da;
            background: #f8fafc;
            padding: 1.2rem;
            color: #4b607f;
            text-align: center;
            font-size: 0.94rem;
        }

        .gov-ticket-empty-window {
            min-height: 13.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .gov-window-actions {
            display: grid;
            gap: 0.75rem;
        }

        .gov-btn {
            min-height: 2.9rem;
            width: 100%;
            border-radius: 0.82rem;
            border: 1px solid transparent;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.65rem 1rem;
            font-weight: 700;
            font-size: 0.92rem;
            letter-spacing: 0.01em;
            transition: transform 160ms ease, filter 160ms ease, box-shadow 160ms ease;
        }

        .gov-btn:hover {
            transform: translateY(-1px);
            filter: brightness(1.01);
        }

        .gov-btn:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgb(59 130 246 / 0.2);
        }

        .gov-btn:disabled {
            cursor: not-allowed;
            opacity: 0.58;
            transform: none;
            filter: none;
        }

        .gov-btn-primary {
            background: linear-gradient(180deg, var(--gov-emerald-600), var(--gov-emerald-700));
            color: #fff;
            box-shadow: 0 10px 16px -14px rgb(15 138 98 / 0.95);
        }

        .gov-btn-warning {
            background: linear-gradient(180deg, #f7ad19, var(--gov-amber-500));
            color: #1f2937;
            border-color: #e59f17;
            box-shadow: 0 10px 16px -14px rgb(180 83 9 / 0.65);
        }

        .gov-btn-secondary {
            background: linear-gradient(180deg, #fff8e8 0%, #fef0cf 100%);
            color: #9a5d06;
            border-color: #f0cf8e;
            box-shadow: 0 10px 16px -14px rgb(180 83 9 / 0.45);
        }

        .gov-btn-complete {
            background: linear-gradient(180deg, #314868, #263a57);
            color: #fff;
            border-color: #1f334f;
        }

        .gov-btn-danger {
            background: #fff5f5;
            color: var(--gov-red-600);
            border-color: #f7cad2;
        }

        .gov-btn-danger:hover {
            background: #ffe9ec;
        }

        .gov-waiting-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.65rem;
        }

        .gov-queue-count {
            border-radius: 999px;
            background: #e2edfb;
            color: #1d4ed8;
            border: 1px solid #bfd5f6;
            padding: 0.32rem 0.66rem;
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .gov-waiting-row {
            padding: 0.9rem 1.1rem;
            border-bottom: 1px solid #e8eef6;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.7rem;
            transition: background-color 140ms ease;
        }

        .gov-waiting-row:hover {
            background: #f6faff;
        }

        .gov-waiting-ticket {
            font-size: 1.72rem;
            line-height: 1;
            font-weight: 700;
            color: var(--gov-ink-900);
            letter-spacing: -0.02em;
        }

        .gov-waiting-time {
            margin: 0.28rem 0 0;
            color: var(--gov-ink-500);
            font-size: 0.8rem;
        }

        .gov-waiting-type {
            margin-top: 0.34rem;
            padding: 0.28rem 0.58rem;
        }

        .gov-waiting-order {
            color: #64748b;
            font-size: 0.83rem;
            font-weight: 700;
        }

        @media (max-width: 1024px) {
            .gov-queue-masthead {
                align-items: flex-start;
                flex-direction: column;
            }

            .gov-masthead-meta-wrap {
                justify-content: flex-start;
            }
        }

        @media (max-width: 640px) {
            .gov-queue-masthead,
            .gov-card-head,
            .gov-card-body,
            .gov-waiting-row {
                padding-left: 0.9rem;
                padding-right: 0.9rem;
            }

            .gov-card-title {
                font-size: 1.3rem;
            }

            .gov-waiting-ticket {
                font-size: 1.35rem;
            }

            .gov-window-grid {
                grid-template-columns: 1fr;
            }
        }

        @keyframes gov-fade-up {
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
            .gov-queue-masthead,
            .gov-card {
                animation: none;
            }

            .gov-btn {
                transition: none;
            }
        }
    </style>
@endonce
