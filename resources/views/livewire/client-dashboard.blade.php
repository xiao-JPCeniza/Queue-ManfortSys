<div class="queue-page min-h-screen flex flex-col">
    <header class="queue-header text-white relative overflow-hidden">
        <div class="queue-header-ribbon" aria-hidden="true"></div>
        <div class="max-w-6xl mx-auto px-4 py-6 relative z-10">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3 min-w-0">
                    <img src="{{ asset('images/lgu-logo.png') }}" alt="Municipality of Manolo Fortich logo" class="queue-header-logo">
                    <div class="min-w-0">
                        <p class="queue-header-kicker">Republic of the Philippines</p>
                        <h1 class="queue-header-title">Municipality Queue Services</h1>
                        <p class="queue-header-subtitle">Municipality of Manolo Fortich - Citizen Service Portal</p>
                    </div>
                </div>
                <div class="queue-header-tools">
                    @if(!$ticket)
                        <label for="office-filter" class="sr-only">Choose office</label>
                        <select id="office-filter" wire:model.live="selectedOfficeSlug" class="queue-office-select">
                            <option value="">All offices</option>
                            @foreach($officeOptions as $option)
                                <option value="{{ $option->slug }}">{{ $option->name }}</option>
                            @endforeach
                        </select>
                    @endif

                    <a href="{{ route('home') }}" class="lgu-btn queue-home-btn px-5 py-2.5 rounded-xl font-semibold text-sm transition focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-blue-800">
                        Home
                    </a>
                </div>
            </div>

            <div class="queue-step-grid mt-5" aria-label="Queue process">
                <div class="queue-step-card">
                    <p class="queue-step-title">Step 1</p>
                    <p class="queue-step-copy">Choose your office</p>
                </div>
                <div class="queue-step-card">
                    <p class="queue-step-title">Step 2</p>
                    <p class="queue-step-copy">Get your queue number</p>
                </div>
                <div class="queue-step-card">
                    <p class="queue-step-title">Step 3</p>
                    <p class="queue-step-copy">Wait for your call</p>
                </div>
            </div>
<<<<<<< HEAD
            <a href="{{ route('welcome') }}" class="lgu-btn px-4 py-2.5 bg-white/20 hover:bg-white/30 rounded-xl font-medium text-sm transition focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-blue-800">
                Home
            </a>
=======
>>>>>>> 33cd61525db14823d1eaed211b9282f3f52cb1be
        </div>
    </header>

    <main class="flex-1 max-w-6xl mx-auto w-full p-4 sm:p-6" role="main">
        @if(!$ticket)
            <section class="queue-intro-card">
                <h2 class="queue-intro-title">Select an Office to Get Your Queue Number</h2>
                <p class="queue-intro-copy">Please choose the office you need to visit. A queue number will be generated instantly and announced through the public queue system.</p>
            </section>

            <div class="gov-office-grid grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5 mt-5" role="list">
                @foreach($offices as $office)
                    <button
                        type="button"
                        wire:click="selectOffice({{ $office->id }})"
                        wire:loading.attr="disabled"
                        class="queue-office-card group relative p-6 text-left transition focus:outline-none focus:ring-2 focus:ring-blue-600 focus:ring-offset-2 disabled:opacity-60 disabled:pointer-events-none min-h-[170px]"
                        role="listitem"
                    >
                        <span class="queue-office-index" aria-hidden="true">{{ sprintf('%02d', $loop->iteration) }}</span>

                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <span class="text-xl font-extrabold text-slate-900 group-hover:text-blue-900 transition block leading-tight">{{ $office->name }}</span>
                                <p class="text-slate-600 text-sm mt-2 leading-relaxed">{{ $office->description }}</p>
                            </div>
                            <span class="queue-office-arrow" aria-hidden="true">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M9 5l7 7-7 7"/></svg>
                            </span>
                        </div>

                        <span class="queue-office-meta">Tap to continue</span>

                        <span wire:loading wire:target="selectOffice({{ $office->id }})" class="absolute inset-0 flex items-center justify-center bg-white/90 rounded-[18px]">
                            <div class="flex flex-col items-center gap-2">
                                <svg class="animate-spin h-8 w-8 text-blue-700" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                <span class="text-xs font-semibold text-slate-700">Generating ticket number...</span>
                            </div>
                        </span>
                    </button>
                @endforeach
            </div>

            @if($offices->isEmpty())
                <div class="queue-empty-state mt-5">
                    <p class="font-semibold">No matching office found.</p>
                    <p class="text-sm mt-1">Choose another office or select "All offices".</p>
                </div>
            @endif
        @else
            <section class="queue-ticket-wrap">
                <div class="queue-ticket-card">
                    <div class="queue-ticket-head">
                        <p class="queue-ticket-kicker">Official Queue Ticket</p>
                        <h2 class="text-2xl font-extrabold text-white mt-1">{{ $ticket['office_name'] }}</h2>
                    </div>
                    <div class="queue-ticket-body">
                        <p class="queue-ticket-label">Queue Number</p>
                        <p class="queue-ticket-number" id="ticket-number-display" aria-label="Your queue number is {{ $ticket['queue_number'] }}">{{ $ticket['queue_number'] }}</p>
                        <p class="queue-ticket-note">Please wait for your number to be called at the service desk.</p>
                    </div>
                </div>
            </section>
        @endif
    </main>

    <footer class="queue-footer py-4 text-sm">
        <div class="max-w-6xl mx-auto px-4 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <p class="sm:text-left">Municipality of Manolo Fortich &copy; {{ date('Y') }} - Queue Management System</p>
            <p class="sm:text-right">Developed by Management Information Systems Office</p>
        </div>
    </footer>
</div>

<style>
    .queue-page {
        --gov-blue-900: #cd9e38;
        --gov-blue-800: #e58b15;
        --gov-blue-700: #14539e;
        --gov-gold-500: #8d641c;
        --gov-red-500: #b74231;
        background:
            radial-gradient(1200px 420px at -10% -20%, rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0) 60%),
            radial-gradient(900px 440px at 110% 25%, rgba(158, 82, 20, 0.11), rgba(20, 83, 158, 0) 55%),
            linear-gradient(180deg, #eaf1fa 0%, #f7f9fc 46%, #edf3fa 100%);
    }

    .queue-header {
        background:
            radial-gradient(900px 350px at 82% -42%, rgba(255, 255, 255, 0.22), rgba(255, 255, 255, 0) 72%),
            linear-gradient(142deg, var(--gov-blue-900) 0%, var(--gov-blue-800) 45%, var(--gov-blue-700) 100%);
        box-shadow: inset 0 -1px 0 rgba(255, 255, 255, 0.12), 0 12px 28px rgba(8, 42, 85, 0.24);
    }

    .queue-header-ribbon {
        height: 7px;
        background: linear-gradient(90deg, #0038a8 0 34%, #fcd116 34% 66%, #ce1126 66% 100%);
    }

    .queue-header-logo {
        width: 56px;
        height: 56px;
        object-fit: contain;
        border-radius: 9999px;
        padding: 2px;
        background: rgba(255, 255, 255, 0.95);
        box-shadow: 0 8px 18px rgba(6, 20, 43, 0.25);
    }

    .queue-header-kicker {
        font-size: 0.67rem;
        text-transform: uppercase;
        letter-spacing: 0.18em;
        font-weight: 700;
        color: #bfdbfe;
    }

    .queue-header-title {
        font-size: clamp(1.25rem, 2vw, 1.75rem);
        line-height: 1.1;
        font-weight: 800;
        letter-spacing: -0.015em;
        color: #f8fbff;
    }

    .queue-header-subtitle {
        font-size: 0.9rem;
        color: #dbeafe;
    }

    .queue-home-btn {
        color: #08356a;
        background: linear-gradient(180deg, #ffffff 0%, #e6eefb 100%);
        border: 1px solid rgba(255, 255, 255, 0.55);
        box-shadow: 0 6px 14px rgba(5, 24, 51, 0.25);
    }

    .queue-home-btn:hover {
        background: linear-gradient(180deg, #ffffff 0%, #dbeafe 100%);
    }

    .queue-header-tools {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.6rem;
        margin-left: auto;
    }

    .queue-office-select {
        height: 44px;
        border-radius: 0.75rem;
        border: 1px solid rgba(255, 255, 255, 0.5);
        background: rgba(255, 255, 255, 0.96);
        color: #0f172a;
        font-size: 0.9rem;
        padding: 0 0.85rem;
        box-shadow: 0 5px 14px rgba(5, 24, 51, 0.2);
    }

    .queue-office-select {
        min-width: 170px;
    }

    .queue-office-select:focus {
        outline: none;
        box-shadow: 0 0 0 2px #ffffff, 0 0 0 4px rgba(29, 78, 216, 0.65);
    }

    .queue-step-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 0.75rem;
    }

    .queue-step-card {
        border-radius: 0.95rem;
        padding: 0.7rem 0.85rem;
        border: 1px solid rgba(255, 255, 255, 0.25);
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.2), rgba(255, 255, 255, 0.1));
        backdrop-filter: blur(2px);
    }

    .queue-step-title {
        margin: 0;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        font-size: 0.68rem;
        color: #bfdbfe;
    }

    .queue-step-copy {
        margin: 0.12rem 0 0;
        font-size: 0.88rem;
        font-weight: 600;
        color: #eff6ff;
    }

    .queue-intro-card {
        border-radius: 1.1rem;
        border: 1px solid #cfdbed;
        border-left: 7px solid var(--gov-blue-700);
        background: linear-gradient(180deg, #ffffff 0%, #f6f9ff 100%);
        box-shadow: 0 10px 22px rgba(15, 23, 42, 0.08);
        padding: 1.1rem 1.25rem;
    }

    .queue-intro-title {
        margin: 0;
        color: #0f2f57;
        font-size: clamp(1.05rem, 2vw, 1.35rem);
        font-weight: 800;
        letter-spacing: -0.01em;
    }

    .queue-intro-copy {
        margin: 0.45rem 0 0;
        color: #334155;
        max-width: 58ch;
    }

    .queue-office-card {
        overflow: hidden;
        border-radius: 1.1rem;
        border: 1px solid #d6e1ef;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.07);
        padding-bottom: 2.75rem;
        transition: transform 0.2s ease, border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .queue-office-card::before {
        content: "";
        position: absolute;
        inset: 0 auto 0 0;
        width: 7px;
        border-radius: 1.1rem 0 0 1.1rem;
        background: linear-gradient(180deg, #14539e 0%, #2b7ac7 45%, #f2b635 100%);
    }

    .gov-office-grid > button:nth-child(4n + 2)::before {
        background: linear-gradient(180deg, #0f766e 0%, #14b8a6 45%, #f2b635 100%);
    }

    .gov-office-grid > button:nth-child(4n + 3)::before {
        background: linear-gradient(180deg, #b74231 0%, #2563eb 45%, #f2b635 100%);
    }

    .gov-office-grid > button:nth-child(4n + 4)::before {
        background: linear-gradient(180deg, #4f46e5 0%, #0ea5e9 45%, #f2b635 100%);
    }

    .queue-office-card:hover {
        transform: translateY(-3px);
        border-color: #94a9c7;
        box-shadow: 0 14px 26px rgba(15, 23, 42, 0.13);
    }

    .queue-office-index {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 0.75rem;
        height: 1.5rem;
        min-width: 2.3rem;
        padding: 0 0.5rem;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 800;
        letter-spacing: 0.08em;
        color: #08356a;
        background: #e0ebfb;
        border: 1px solid #bdd2f2;
    }

    .queue-office-arrow {
        color: #94a3b8;
        transition: color 0.2s ease, transform 0.2s ease;
    }

    .queue-office-card:hover .queue-office-arrow {
        color: #14539e;
        transform: translateX(3px);
    }

    .queue-office-meta {
        position: absolute;
        left: 1.5rem;
        bottom: 1rem;
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: #64748b;
    }

    .queue-ticket-wrap {
        max-width: 40rem;
        margin: 2.3rem auto 0;
    }

    .queue-ticket-card {
        border-radius: 1.4rem;
        overflow: hidden;
        border: 1px solid #c9d8ec;
        background: #ffffff;
        box-shadow: 0 18px 36px rgba(12, 59, 115, 0.22);
    }

    .queue-ticket-head {
        padding: 1.25rem 1.4rem;
        text-align: center;
        background: linear-gradient(140deg, #082a55 0%, #0c3b73 58%, #14539e 100%);
    }

    .queue-ticket-kicker {
        margin: 0;
        color: #bfdbfe;
        font-size: 0.72rem;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        font-weight: 700;
    }

    .queue-ticket-body {
        padding: 2rem 1.5rem;
        text-align: center;
    }

    .queue-ticket-label {
        margin: 0;
        color: #64748b;
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.17em;
    }

    .queue-ticket-number {
        margin: 0.4rem 0 0;
        color: #047857;
        font-size: clamp(3rem, 8vw, 4.6rem);
        line-height: 1;
        font-weight: 900;
        letter-spacing: -0.03em;
    }

    .queue-ticket-note {
        margin: 1rem auto 0;
        max-width: 34ch;
        color: #334155;
    }

    .queue-footer {
        margin-top: auto;
        color: #dbeafe;
        border-top: 3px solid var(--gov-gold-500);
        background: linear-gradient(145deg, #082a55 0%, #0c3b73 68%, #14539e 100%);
    }

    .queue-empty-state {
        border-radius: 1rem;
        border: 1px solid #c8d8ef;
        background: linear-gradient(180deg, #ffffff 0%, #f6faff 100%);
        color: #334155;
        text-align: center;
        padding: 1.2rem;
    }

    @media (max-width: 900px) {
        .queue-step-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 640px) {
        .queue-header-logo {
            width: 48px;
            height: 48px;
        }

        .queue-header-tools {
            width: 100%;
        }

        .queue-home-btn {
            width: 100%;
        }

        .queue-office-select {
            width: 100%;
        }

        .queue-office-meta {
            left: 1.25rem;
        }
    }
</style>
