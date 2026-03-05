<div wire:poll.5s>
    @if(session('office_message'))
        <div class="mb-4 p-4 bg-emerald-50 border border-emerald-300 text-emerald-800 rounded-xl text-sm" role="status">{{ session('office_message') }}</div>
    @endif

    @if($office->slug !== 'hrmo')
        <div class="mb-6">
            <h1 class="lgu-page-title">{{ $office->name }}</h1>
            <p class="text-slate-600 text-sm mt-1">Office queue dashboard - call numbers and manage the line.</p>
        </div>
    @endif

    @if($office->slug === 'hrmo')
        <div class="overflow-hidden rounded-lg border border-slate-300 bg-white shadow-sm">
            <div class="min-w-0 bg-white">
                    <div class="p-4 sm:p-6">
                        @if($hrmoTab === 'dashboard')
                            @include('livewire.office-admin.partials.queue-dashboard-panel', ['showHrmoMonitor' => true])
                        @endif

                        @if($hrmoTab === 'reports' && $summary)
                            <div class="space-y-6">
                                <section class="lgu-card p-6" aria-labelledby="summary-heading">
                                    <h2 id="summary-heading" class="lgu-section-title mb-4">Overall Tickets Being Accommodated</h2>
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                        <div class="rounded-xl border border-slate-200 bg-white p-4">
                                            <p class="text-xs uppercase tracking-wide text-slate-500">Total Today</p>
                                            <p class="text-3xl font-bold text-slate-800 mt-2">{{ $summary['total_today'] }}</p>
                                        </div>
                                        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                                            <p class="text-xs uppercase tracking-wide text-emerald-700">Completed Today</p>
                                            <p class="text-3xl font-bold text-emerald-700 mt-2">{{ $summary['completed_today'] }}</p>
                                        </div>
                                        <div class="rounded-xl border border-blue-200 bg-blue-50 p-4">
                                            <p class="text-xs uppercase tracking-wide text-blue-700">Active Now</p>
                                            <p class="text-3xl font-bold text-blue-700 mt-2">{{ $summary['active_now'] }}</p>
                                        </div>
                                    </div>
                                </section>

                                <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                                    <section class="lgu-card p-6" aria-labelledby="status-distribution-heading">
                                        <div class="flex items-center justify-between gap-2">
                                            <h2 id="status-distribution-heading" class="lgu-section-title">Ticket Status Distribution (Today)</h2>
                                            <span class="text-xs text-slate-500">{{ $summary['total_today'] }} total</span>
                                        </div>

                                        <div class="mt-4 grid grid-cols-1 lg:grid-cols-[220px_1fr] gap-5 items-center">
                                            <div class="mx-auto">
                                                <div class="relative h-44 w-44 rounded-full border border-slate-200"
                                                     style="background: {{ $statusPieStyle }};"
                                                     role="img"
                                                     aria-label="Pie chart of ticket status distribution for today">
                                                    <div class="absolute rounded-full border border-slate-100 bg-white" style="inset: 26%;"></div>
                                                    <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                                                        <span class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Total</span>
                                                        <span class="text-2xl font-bold text-slate-800">{{ $summary['total_today'] }}</span>
                                                    </div>
                                                </div>
                                                @if(!$statusPieHasData)
                                                    <p class="mt-2 text-center text-xs text-slate-500">No tickets yet today.</p>
                                                @endif
                                            </div>

                                            <div>
                                                <div class="h-4 overflow-hidden rounded-full bg-slate-100 flex">
                                                    @foreach($statusBreakdown as $status)
                                                        @php($segmentWidth = $status['count'] > 0 ? max($status['percentage'], 1) : 0)
                                                        @if($segmentWidth > 0)
                                                            <span
                                                                class="{{ $status['bar_class'] }}"
                                                                style="width: {{ $segmentWidth }}%;"
                                                                title="{{ $status['label'] }}: {{ $status['count'] }} ({{ number_format($status['percentage'], 1) }}%)"
                                                            ></span>
                                                        @endif
                                                    @endforeach
                                                </div>

                                                <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                                    @foreach($statusBreakdown as $status)
                                                        <div class="rounded-xl border border-slate-200 bg-white px-3 py-2.5">
                                                            <div class="flex items-center justify-between gap-3">
                                                                <span class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
                                                                    <span class="h-2.5 w-2.5 rounded-full {{ $status['chip_class'] }}" aria-hidden="true"></span>
                                                                    {{ $status['label'] }}
                                                                </span>
                                                                <span class="text-sm font-semibold text-slate-800">{{ $status['count'] }}</span>
                                                            </div>
                                                            <p class="mt-0.5 text-xs text-slate-500">{{ number_format($status['percentage'], 1) }}%</p>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </section>

                                    <section class="lgu-card p-6" aria-labelledby="hourly-volume-heading">
                                        <div class="flex items-center justify-between gap-2">
                                            <h2 id="hourly-volume-heading" class="lgu-section-title">Hourly Ticket Volume (Today)</h2>
                                            <span class="text-xs text-slate-500">Peak: {{ $peakHourLabel }}</span>
                                        </div>

                                        <div class="mt-4 overflow-x-auto">
                                            <div class="min-w-[900px]">
                                                <div class="h-56 border-l border-b border-slate-200 px-2 pt-3 flex items-end gap-2">
                                                    @foreach($hourlyTicketSeries as $hourPoint)
                                                        @php($barHeight = $hourPoint['count'] > 0 ? max(8, (int) round(($hourPoint['count'] / $hourlyMax) * 185)) : 8)
                                                        <div class="flex-1 min-w-[24px] flex flex-col items-center justify-end gap-1">
                                                            <div
                                                                class="w-full rounded-t-md {{ $hourPoint['count'] > 0 ? 'bg-blue-500' : 'bg-slate-200' }}"
                                                                style="height: {{ $barHeight }}px;"
                                                                title="{{ $hourPoint['label'] }}: {{ $hourPoint['count'] }} ticket(s)"
                                                            >
                                                                <span class="sr-only">{{ $hourPoint['label'] }}: {{ $hourPoint['count'] }} ticket(s)</span>
                                                            </div>
                                                            <span class="text-[10px] text-slate-500 uppercase">{{ $hourPoint['short_label'] }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </section>
                                </div>

                                <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                                    <section class="lgu-card p-6" aria-labelledby="monthly-volume-heading">
                                        <div class="flex items-center justify-between gap-2">
                                            <h2 id="monthly-volume-heading" class="lgu-section-title">Ticket Volume Per Month (Last 12 Months)</h2>
                                            <span class="text-xs text-slate-500">Peak: {{ $monthlyPeakMonthLabel }}</span>
                                        </div>

                                        <div class="mt-4 overflow-x-auto">
                                            <div class="min-w-[680px]">
                                                <div class="h-56 border-l border-b border-slate-200 px-2 pt-3 flex items-end gap-3">
                                                    @foreach($monthlyVolumeSeries as $monthPoint)
                                                        @php($monthBarHeight = $monthPoint['total'] > 0 ? max(8, (int) round(($monthPoint['total'] / $monthlyVolumeMax) * 185)) : 8)
                                                        <div class="flex-1 min-w-[34px] flex flex-col items-center justify-end gap-1">
                                                            <div
                                                                class="w-full rounded-t-md {{ $monthPoint['total'] > 0 ? 'bg-indigo-500' : 'bg-slate-200' }}"
                                                                style="height: {{ $monthBarHeight }}px;"
                                                                title="{{ $monthPoint['label'] }}: {{ $monthPoint['total'] }} ticket(s)"
                                                            >
                                                                <span class="sr-only">{{ $monthPoint['label'] }}: {{ $monthPoint['total'] }} ticket(s)</span>
                                                            </div>
                                                            <span class="text-[10px] font-semibold text-slate-600 uppercase">{{ $monthPoint['short_label'] }}</span>
                                                            <span class="text-[10px] text-slate-400">{{ $monthPoint['year_short'] }}</span>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </section>

                                    <section class="lgu-card p-6" aria-labelledby="monthly-status-heading">
                                        <div class="flex flex-wrap items-center justify-between gap-3">
                                            <h2 id="monthly-status-heading" class="lgu-section-title">Status Per Month (Last 12 Months)</h2>
                                            <div class="flex flex-wrap items-center gap-2">
                                                @foreach($monthlyStatusLegend as $legend)
                                                    <span class="inline-flex items-center gap-1.5 rounded-full border border-slate-200 bg-white px-2.5 py-1 text-[11px] font-medium text-slate-600">
                                                        <span class="h-2 w-2 rounded-full {{ $legend['chip_class'] }}" aria-hidden="true"></span>
                                                        {{ $legend['label'] }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>

                                        <div class="mt-4 space-y-2.5">
                                            @foreach($monthlyStatusSeries as $monthRow)
                                                <div class="rounded-xl border border-slate-200 bg-white px-3 py-2.5">
                                                    <div class="flex items-center justify-between gap-3 text-xs text-slate-500">
                                                        <span class="font-medium">{{ $monthRow['label'] }}</span>
                                                        <span>{{ $monthRow['total'] }} total</span>
                                                    </div>
                                                    <div class="mt-2 h-3 overflow-hidden rounded-full bg-slate-100 flex">
                                                        @if($monthRow['total'] > 0)
                                                            @foreach($monthRow['segments'] as $segment)
                                                                @php($segmentWidth = $segment['count'] > 0 ? max($segment['percentage'], 1) : 0)
                                                                @if($segmentWidth > 0)
                                                                    <span
                                                                        class="{{ $segment['bar_class'] }}"
                                                                        style="width: {{ $segmentWidth }}%;"
                                                                        title="{{ $segment['label'] }}: {{ $segment['count'] }} ({{ number_format($segment['percentage'], 1) }}%)"
                                                                    ></span>
                                                                @endif
                                                            @endforeach
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </section>
                                </div>
                            </div>
                        @endif

                        @if($hrmoTab === 'queue-management')
                            <section class="lgu-card p-6" aria-labelledby="overall-activity-heading">
                                <h2 id="overall-activity-heading" class="lgu-section-title mb-4">Overall Ticket Activity (Today)</h2>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm">
                                        <thead>
                                            <tr class="text-left border-b border-slate-200 text-slate-500">
                                                <th class="py-2 pr-4 font-medium">Ticket #</th>
                                                <th class="py-2 pr-4 font-medium">Status</th>
                                                <th class="py-2 pr-4 font-medium">Issued</th>
                                                <th class="py-2 pr-4 font-medium">Called</th>
                                                <th class="py-2 pr-4 font-medium">Completed</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($overallTickets as $entry)
                                                <tr class="border-b border-slate-100">
                                                    <td class="py-2 pr-4 font-semibold text-slate-800">{{ $entry->queue_number }}</td>
                                                    <td class="py-2 pr-4">
                                                        <span class="px-2 py-1 rounded-full text-xs font-medium
                                                            {{ $entry->status === 'serving' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                                            {{ $entry->status === 'waiting' ? 'bg-amber-100 text-amber-700' : '' }}
                                                            {{ $entry->status === 'completed' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                                            {{ $entry->status === 'not_served' ? 'bg-red-100 text-red-700' : '' }}">
                                                            {{ strtoupper(str_replace('_', ' ', $entry->status)) }}
                                                        </span>
                                                    </td>
                                                    <td class="py-2 pr-4 text-slate-600">{{ $entry->created_at->format('h:i:s A') }}</td>
                                                    <td class="py-2 pr-4 text-slate-600">{{ $entry->called_at?->format('h:i:s A') ?? '-' }}</td>
                                                    <td class="py-2 pr-4 text-slate-600">{{ $entry->served_at?->format('h:i:s A') ?? '-' }}</td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="py-6 text-center text-slate-500">No tickets yet for HRMO today.</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </section>
                        @endif
                    </div>
            </div>
        </div>
    @else
        @include('livewire.office-admin.partials.queue-dashboard-panel')
    @endif
</div>

@script
<script>
    window.callServingNumber = (queueNumber, officeName) => {
        if (!queueNumber || !('speechSynthesis' in window)) {
            return;
        }

        window.speechSynthesis.cancel();

        const getBestAnnouncementVoice = () => {
            const voices = window.speechSynthesis.getVoices();
            if (!voices.length) {
                return null;
            }

            const femaleHints = ['female', 'woman', 'girl', 'libby', 'hazel', 'susan', 'zira', 'samantha', 'kate', 'sonia'];
            const naturalHints = ['natural', 'neural', 'premium', 'enhanced', 'online', 'wavenet', 'studio'];

            const scoreVoice = (voice) => {
                const haystack = `${voice.name} ${voice.voiceURI}`.toLowerCase();
                let score = 0;

                if (voice.lang.toLowerCase().startsWith('en-gb')) score += 50;
                if (haystack.includes('uk') || haystack.includes('british') || haystack.includes('england')) score += 20;
                if (femaleHints.some((hint) => haystack.includes(hint))) score += 20;
                if (naturalHints.some((hint) => haystack.includes(hint))) score += 10;
                if (haystack.includes('male') || haystack.includes('man')) score -= 20;
                if (voice.default) score += 2;

                return score;
            };

            return [...voices].sort((a, b) => scoreVoice(b) - scoreVoice(a))[0] ?? null;
        };

        const toSpokenQueue = (value) => {
            const [prefix, number] = value.split('-');

            if (!number) {
                return value.split('').join(' ');
            }

            const letters = prefix.split('').join(' ');
            return `${letters} ${number}`;
        };

        const spokenQueue = toSpokenQueue(queueNumber);
        const message = `Now serving ${spokenQueue} at ${officeName}. Please proceed to the office.`;
        const announcement = new SpeechSynthesisUtterance(message);
        announcement.lang = 'en-GB';
        announcement.rate = 0.92;
        announcement.pitch = 1.03;

        const preferredVoice = getBestAnnouncementVoice();
        if (preferredVoice) {
            announcement.voice = preferredVoice;
            announcement.lang = preferredVoice.lang || 'en-GB';
        }

        window.speechSynthesis.speak(announcement);
    };

    if ('speechSynthesis' in window) {
        window.speechSynthesis.getVoices();
    }
</script>
@endscript
