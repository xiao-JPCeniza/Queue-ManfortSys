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
                                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
                                        <div class="rounded-xl border border-slate-200 bg-white p-4">
                                            <p class="text-xs uppercase tracking-wide text-slate-500">Total Today</p>
                                            <p class="text-3xl font-bold text-slate-800 mt-2">{{ $summary['total_today'] }}</p>
                                        </div>
                                        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4">
                                            <p class="text-xs uppercase tracking-wide text-emerald-700">Completed Today</p>
                                            <p class="text-3xl font-bold text-emerald-700 mt-2">{{ $summary['completed_today'] }}</p>
                                        </div>
                                        <div class="rounded-xl border border-indigo-200 bg-indigo-50 p-4">
                                            <p class="text-xs uppercase tracking-wide text-indigo-700">Overall Tickets Accommodated</p>
                                            <p class="text-3xl font-bold text-indigo-700 mt-2">{{ $summary['overall_accommodated'] }}</p>
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

                        @if($hrmoTab === 'queue-reports')
                            <div class="space-y-6">
                                <section id="queue-reports-printable" class="lgu-card p-6" aria-labelledby="queue-reports-heading">
                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                        <div>
                                            <h2 id="queue-reports-heading" class="lgu-section-title inline-flex items-center gap-2">
                                                <svg class="h-5 w-5 text-violet-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 20h16" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 16v-5" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 16V8" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16v-9" />
                                                </svg>
                                                Queue Reports
                                            </h2>
                                            <p class="mt-1 text-xs text-slate-500">Scope: {{ $queueReportScopeLabel }}</p>
                                        </div>
                                        @php($queueReportsPdfUrl = auth()->user()?->isSuperAdmin() ? route('super-admin.queue-reports.pdf') : route('office.queue-reports.pdf', $office->slug))
                                        <a href="{{ $queueReportsPdfUrl }}"
                                           data-no-print="true"
                                           class="lgu-btn inline-flex items-center gap-2 rounded-lg bg-blue-700 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V3h12v6" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18h12v3H6z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 14H4a2 2 0 0 1-2-2v-1a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v1a2 2 0 0 1-2 2h-2" />
                                            </svg>
                                            Print PDF
                                        </a>
                                    </div>

                                    <div class="mt-5 space-y-5">
                                        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                                            <div class="border-b border-slate-200 bg-slate-50 px-4 py-3">
                                                <h3 class="text-sm font-semibold text-slate-700">Daily Queue Counts (Last 7 Days)</h3>
                                            </div>
                                            <div class="overflow-x-auto">
                                                <table class="w-full text-sm">
                                                    <thead>
                                                        <tr class="text-left text-slate-500">
                                                            <th class="border-b border-slate-200 px-4 py-3 font-semibold text-center">Date</th>
                                                            <th class="border-b border-slate-200 px-4 py-3 font-semibold text-center">Total Tickets</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse($queueReportDailyCounts as $row)
                                                            <tr class="border-b border-slate-100 last:border-b-0">
                                                                <td class="px-4 py-2.5 text-center font-semibold text-slate-700">{{ $row['date'] }}</td>
                                                                <td class="px-4 py-2.5 text-center font-semibold text-slate-700">{{ $row['total_tickets'] }}</td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="2" class="px-4 py-4 text-center text-slate-500">No queue activity in the last 7 days.</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </section>

                                        <section class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                                            <div class="border-b border-slate-200 bg-slate-50 px-4 py-3">
                                                <h3 class="text-sm font-semibold text-slate-700">Weekly Queue Counts (Last 5 Weeks)</h3>
                                            </div>
                                            <div class="overflow-x-auto">
                                                <table class="w-full text-sm">
                                                    <thead>
                                                        <tr class="text-left text-slate-500">
                                                            <th class="border-b border-slate-200 px-4 py-3 font-semibold text-center">Week #</th>
                                                            <th class="border-b border-slate-200 px-4 py-3 font-semibold text-center">Total Tickets</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse($queueReportWeeklyCounts as $row)
                                                            <tr class="border-b border-slate-100 last:border-b-0">
                                                                <td class="px-4 py-2.5 text-center font-semibold text-slate-700">{{ $row['week'] }}</td>
                                                                <td class="px-4 py-2.5 text-center font-semibold text-slate-700">{{ $row['total_tickets'] }}</td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="2" class="px-4 py-4 text-center text-slate-500">No queue activity in the last 5 weeks.</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </section>

                                        <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
                                            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                                                <div class="border-b border-slate-200 bg-slate-50 px-4 py-3">
                                                    <h3 class="text-sm font-semibold text-slate-700">Status Summary</h3>
                                                </div>
                                                <div class="overflow-x-auto">
                                                    <table class="w-full text-sm">
                                                        <thead>
                                                            <tr class="text-slate-500">
                                                                <th class="border-b border-slate-200 px-4 py-3 font-semibold text-center">Served</th>
                                                                <th class="border-b border-slate-200 px-4 py-3 font-semibold text-center">Skipped</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr>
                                                                <td class="px-4 py-3 text-center font-semibold text-slate-700">{{ $queueReportStatusSummary['served'] }}</td>
                                                                <td class="px-4 py-3 text-center font-semibold text-slate-700">{{ $queueReportStatusSummary['skipped'] }}</td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </section>

                                            <section class="overflow-hidden rounded-xl border border-slate-200 bg-white">
                                                <div class="border-b border-slate-200 bg-slate-50 px-4 py-3">
                                                    <h3 class="text-sm font-semibold text-slate-700">Average Processing Time</h3>
                                                </div>
                                                <div class="flex h-[94px] items-center justify-center px-4">
                                                    <p class="text-2xl font-bold text-slate-700">{{ $queueReportAverageProcessingTime }}</p>
                                                </div>
                                            </section>
                                        </div>
                                    </div>
                                </section>
                            </div>
                        @endif

                        @if($hrmoTab === 'queue-management')
                            @if(auth()->user()?->isSuperAdmin())
                                <div class="space-y-6">
                                    @foreach($overallTicketsByOffice as $officeActivity)
                                        <section class="lgu-card p-6" aria-labelledby="overall-activity-{{ $officeActivity['office']->slug }}">
                                            <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
                                                <h2 id="overall-activity-{{ $officeActivity['office']->slug }}" class="lgu-section-title">
                                                    Overall Ticket Activity (Today) - {{ $officeActivity['office']->name }}
                                                </h2>
                                                <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700">
                                                    {{ $officeActivity['entries']->count() }} ticket(s)
                                                </span>
                                            </div>
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
                                                        @forelse($officeActivity['entries'] as $entry)
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
                                                                <td class="py-2 pr-4 text-slate-600">{{ $entry->created_at->timezone('Asia/Manila')->format('h:i:s A') }}</td>
                                                                <td class="py-2 pr-4 text-slate-600">{{ $entry->called_at?->timezone('Asia/Manila')?->format('h:i:s A') ?? '-' }}</td>
                                                                <td class="py-2 pr-4 text-slate-600">{{ $entry->served_at?->timezone('Asia/Manila')?->format('h:i:s A') ?? '-' }}</td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="5" class="py-6 text-center text-slate-500">No tickets yet for {{ $officeActivity['office']->name }} today.</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </section>
                                    @endforeach
                                </div>
                            @else
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
                                                        <td class="py-2 pr-4 text-slate-600">{{ $entry->created_at->timezone('Asia/Manila')->format('h:i:s A') }}</td>
                                                        <td class="py-2 pr-4 text-slate-600">{{ $entry->called_at?->timezone('Asia/Manila')?->format('h:i:s A') ?? '-' }}</td>
                                                        <td class="py-2 pr-4 text-slate-600">{{ $entry->served_at?->timezone('Asia/Manila')?->format('h:i:s A') ?? '-' }}</td>
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
    let voiceWarmupPromise = null;

    const getVoicesWithWarmup = () => {
        if (!('speechSynthesis' in window)) {
            return Promise.resolve([]);
        }

        const synth = window.speechSynthesis;
        const voices = synth.getVoices();

        if (voices.length) {
            return Promise.resolve(voices);
        }

        if (voiceWarmupPromise) {
            return voiceWarmupPromise;
        }

        voiceWarmupPromise = new Promise((resolve) => {
            let settled = false;

            const finish = () => {
                if (settled) {
                    return;
                }

                settled = true;
                synth.removeEventListener('voiceschanged', onVoicesChanged);
                resolve(synth.getVoices());
            };

            const onVoicesChanged = () => {
                finish();
            };

            synth.addEventListener('voiceschanged', onVoicesChanged);
            window.setTimeout(finish, 1200);
            synth.getVoices();
        }).then((loadedVoices) => {
            if (!loadedVoices.length) {
                voiceWarmupPromise = null;
            }

            return loadedVoices;
        });

        return voiceWarmupPromise;
    };

    const getBestAnnouncementVoice = (voices) => {
        if (!voices.length) {
            return null;
        }

        const ukVoices = voices.filter((voice) => (voice.lang || '').toLowerCase().startsWith('en-gb'));
        if (!ukVoices.length) {
            return null;
        }

        const preferredNames = [
            'microsoft sonia online (natural) - english (united kingdom)',
            'microsoft libby online (natural) - english (united kingdom)',
            'google uk english female',
            'microsoft hazel desktop - english (great britain)'
        ];

        const femaleHints = ['female', 'woman', 'girl', 'libby', 'hazel', 'sonia', 'kate'];
        const naturalHints = ['natural', 'neural', 'premium', 'enhanced', 'online', 'wavenet', 'studio'];

        const exactMatch = ukVoices.find((voice) => preferredNames.includes(voice.name.toLowerCase()));
        if (exactMatch) {
            return exactMatch;
        }

        const scoreVoice = (voice) => {
            const haystack = `${voice.name} ${voice.voiceURI}`.toLowerCase();
            let score = 0;

            if (haystack.includes('uk') || haystack.includes('british') || haystack.includes('england')) score += 20;
            if (femaleHints.some((hint) => haystack.includes(hint))) score += 20;
            if (naturalHints.some((hint) => haystack.includes(hint))) score += 25;
            if (haystack.includes('male') || haystack.includes('man')) score -= 25;
            if (voice.default) score += 2;

            return score;
        };

        return [...ukVoices].sort((a, b) => scoreVoice(b) - scoreVoice(a))[0] ?? null;
    };

    const toSpokenQueue = (value) => {
        const [prefix, number] = value.split('-');

        if (!number) {
            return value.split('').join(' ');
        }

        const letters = prefix.split('').join(' ');
        const digits = number.split('').join(' ');
        return `${letters}, ${digits}`;
    };

    const toSpokenOffice = (value) => value.replace(/\b([A-Z]{2,})\b/g, (token) => token.split('').join(' '));

    window.callServingNumber = async (queueNumber, officeName) => {
        if (!queueNumber || !('speechSynthesis' in window)) {
            return;
        }

        const synth = window.speechSynthesis;
        synth.cancel();

        const voices = await getVoicesWithWarmup();
        const preferredVoice = getBestAnnouncementVoice(voices);

        const spokenQueue = toSpokenQueue(queueNumber);
        const spokenOffice = toSpokenOffice(officeName);
        const message = `Now serving, ${spokenQueue}, at ${spokenOffice}. Please proceed to the office.`;
        const announcement = new SpeechSynthesisUtterance(message);

        announcement.lang = 'en-GB';
        announcement.rate = 0.9;
        announcement.pitch = 1.0;
        announcement.volume = 1;

        if (preferredVoice) {
            announcement.voice = preferredVoice;
            announcement.lang = preferredVoice.lang || 'en-GB';
        }

        synth.speak(announcement);
    };

    if ('speechSynthesis' in window) {
        window.speechSynthesis.getVoices();
    }
</script>
@endscript
