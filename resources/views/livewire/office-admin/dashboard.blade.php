<div wire:poll.2s>
    @php($isBploOffice = in_array($office->slug, ['business-permits', 'bplo'], true))
    @php($isAccountingOffice = $office->slug === 'accounting')
    @php($isMhoOffice = $office->slug === 'mho')
    @php($isMswdoOffice = $office->slug === 'mswdo')
    @php($usesAdvancedQueueDashboard = in_array($office->slug, ['hrmo', 'business-permits', 'bplo', 'mho', 'mswdo', 'treasury', 'accounting', 'civil-registry', 'assessors-office'], true))
    @php($reportOfficeLabels = [
        'hrmo' => 'HRMO',
        'business-permits' => 'BPLO',
        'bplo' => 'BPLO',
        'accounting' => 'Accounting Office',
        'treasury' => 'Treasury Office',
        'assessors-office' => 'Assessor\'s Office',
        'civil-registry' => 'Civil Registry',
        'mho' => 'MHO',
        'mswdo' => 'MSWDO',
    ])
    @php($reportOfficeLabel = $reportOfficeLabels[$office->slug] ?? $office->name)
    @php($isAllOfficesReportScope = auth()->user()?->isSuperAdmin() && $office->slug === 'hrmo')
    @php($liveMonitorRoute = $office->slug === 'hrmo' ? 'office.hrmo.monitor' : ($isBploOffice ? 'office.bplo.monitor' : 'office.hrmo.monitor'))
    @php($liveMonitorLabel = $office->slug === 'hrmo' ? 'Open HRMO Live Monitor' : ($isBploOffice ? 'Open BPLO Live Monitor' : ($isMhoOffice ? 'Open MHO Live Queue Monitor' : ($isMswdoOffice ? 'Open MSWDO Live Queue Monitor' : ($isAccountingOffice ? 'Open Accounting Live Queue Monitor' : ($office->slug === 'treasury' ? 'Open Treasury Live Queue Monitor' : ($office->slug === 'civil-registry' ? 'Open Civil Registry Live Queue Monitor' : ($office->slug === 'assessors-office' ? 'Open Assessor\'s Live Queue Monitor' : 'Open Live Monitor'))))))))

    @if(session('office_message'))
        <div class="mb-4 p-4 bg-emerald-50 border border-emerald-300 text-emerald-800 rounded-xl text-sm" role="status">{{ session('office_message') }}</div>
    @endif

    @if(!$usesAdvancedQueueDashboard)
        <div class="mb-6">
            <h1 class="lgu-page-title">{{ $office->name }}</h1>
            <p class="text-slate-600 text-sm mt-1">Office queue dashboard - call numbers and manage the line.</p>
        </div>
    @endif

    @if($usesAdvancedQueueDashboard)
        <div class="overflow-hidden rounded-lg border border-slate-300 bg-white shadow-sm">
            <div class="min-w-0 bg-white">
                    <div class="p-4 sm:p-6">
                        @if($hrmoTab === 'dashboard')
                            @include('livewire.office-admin.partials.queue-dashboard-panel', [
                                'showLiveMonitor' => true,
                                'liveMonitorRoute' => $liveMonitorRoute,
                                'liveMonitorLabel' => $liveMonitorLabel,
                            ])
                        @endif

                        @if($hrmoTab === 'reports' && $summary)
<<<<<<< HEAD
                            <div class="space-y-6">
                                <section class="lgu-card p-6" aria-labelledby="summary-heading">
                                    <h2 id="summary-heading" class="lgu-section-title mb-4">
                                        {{ $isAllOfficesReportScope ? 'Total Tickets Accommodated Across All Offices' : 'Total Tickets Accommodated (' . $reportOfficeLabel . ')' }}
                                    </h2>
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
                                            <p class="text-xs uppercase tracking-wide text-indigo-700">
                                                {{ $isAllOfficesReportScope ? 'Total Accommodated (Completed, All Offices)' : 'Total Accommodated (Completed, ' . $reportOfficeLabel . ')' }}
                                            </p>
                                            <p class="text-3xl font-bold text-indigo-700 mt-2">{{ $summary['overall_accommodated'] }}</p>
                                        </div>
                                    </div>
                                </section>

                                @if(auth()->user()?->isSuperAdmin())
                                    @php($topOffice = $officeAccommodatedChartSeries[0] ?? null)
                                    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                                        <section class="lgu-card p-6" aria-labelledby="office-accommodated-pie-heading">
                                            <div class="flex items-center justify-between gap-2">
                                                <h2 id="office-accommodated-pie-heading" class="lgu-section-title">Accommodated Tickets Share by Office</h2>
                                                <span class="text-xs text-slate-500">{{ number_format($officeAccommodatedTotal) }} total</span>
                                            </div>

                                            <div class="mt-4 grid grid-cols-1 lg:grid-cols-[220px_1fr] gap-5 items-center">
                                                <div class="mx-auto">
                                                    <div class="relative h-44 w-44 rounded-full border border-slate-200"
                                                         style="background: {{ $officeAccommodatedPieStyle }};"
                                                         role="img"
                                                         aria-label="Pie chart of accommodated tickets by office">
                                                        <div class="absolute rounded-full border border-slate-100 bg-white" style="inset: 26%;"></div>
                                                        <div class="absolute inset-0 flex flex-col items-center justify-center text-center">
                                                            <span class="text-[11px] font-semibold uppercase tracking-wide text-slate-500">Total</span>
                                                            <span class="text-2xl font-bold text-slate-800">{{ number_format($officeAccommodatedTotal) }}</span>
                                                        </div>
                                                    </div>
                                                    @if(!$officeAccommodatedHasData)
                                                        <p class="mt-2 text-center text-xs text-slate-500">No accommodated tickets yet.</p>
                                                    @endif
                                                </div>

                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                                    @forelse($officeAccommodatedChartSeries as $officeRow)
                                                        <div class="rounded-xl border border-slate-200 bg-white px-3 py-2.5">
                                                            <div class="flex items-center justify-between gap-3">
                                                                <span class="inline-flex items-center gap-2 text-sm font-medium text-slate-700">
                                                                    <span class="h-2.5 w-2.5 rounded-full {{ $officeRow['chip_class'] }}" aria-hidden="true"></span>
                                                                    {{ $officeRow['office_name'] }}
                                                                </span>
                                                                <span class="text-sm font-semibold text-slate-800">{{ number_format($officeRow['accommodated_total']) }}</span>
                                                            </div>
                                                            <p class="mt-0.5 text-xs text-slate-500">{{ number_format($officeRow['percentage'], 1) }}%</p>
                                                        </div>
                                                    @empty
                                                        <p class="text-sm text-slate-500">No office accommodation data found.</p>
                                                    @endforelse
                                                </div>
                                            </div>
                                        </section>

                                        <section class="lgu-card p-6" aria-labelledby="office-accommodated-bar-heading">
                                            <div class="flex items-center justify-between gap-2">
                                                <h2 id="office-accommodated-bar-heading" class="lgu-section-title">Accommodated Tickets by Office (Bar Chart)</h2>
                                                <span class="text-xs text-slate-500">
                                                    @if($topOffice)
                                                        Top: {{ $topOffice['office_name'] }} ({{ number_format($topOffice['accommodated_total']) }})
                                                    @else
                                                        No data
                                                    @endif
                                                </span>
                                            </div>

                                            <div class="mt-4 space-y-3">
                                                @forelse($officeAccommodatedChartSeries as $officeRow)
                                                    @php($barWidth = $officeAccommodatedMax > 0 ? ($officeRow['accommodated_total'] > 0 ? max(2, round(($officeRow['accommodated_total'] / $officeAccommodatedMax) * 100, 1)) : 0) : 0)
                                                    <div>
                                                        <div class="flex items-center justify-between gap-2 text-xs text-slate-600">
                                                            <span class="font-medium">{{ $officeRow['office_name'] }}</span>
                                                            <span>{{ number_format($officeRow['accommodated_total']) }} ({{ number_format($officeRow['percentage'], 1) }}%)</span>
                                                        </div>
                                                        <div class="mt-1 h-3 overflow-hidden rounded-full bg-slate-100">
                                                            @if($barWidth > 0)
                                                                <span class="block h-full {{ $officeRow['bar_class'] }}" style="width: {{ $barWidth }}%;"></span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @empty
                                                    <p class="text-sm text-slate-500">No office accommodation data found.</p>
                                                @endforelse
                                            </div>
                                        </section>
                                    </div>

                                    <section class="lgu-card p-6" aria-labelledby="office-accommodated-summary-heading">
                                        <div class="flex items-center justify-between gap-2">
                                            <h2 id="office-accommodated-summary-heading" class="lgu-section-title">Accommodated Tickets by Office</h2>
                                            <span class="text-xs text-slate-500">{{ count($officeAccommodatedChartSeries) }} offices</span>
                                        </div>

                                        <div class="mt-4 overflow-x-auto">
                                            <table class="w-full text-sm">
                                                <thead>
                                                    <tr class="border-b border-slate-200 text-left text-slate-500">
                                                        <th class="px-3 py-2.5 font-semibold">#</th>
                                                        <th class="px-3 py-2.5 font-semibold">Office</th>
                                                        <th class="px-3 py-2.5 font-semibold text-right">Accommodated</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($officeAccommodatedChartSeries as $index => $row)
                                                        <tr class="border-b border-slate-100 last:border-b-0">
                                                            <td class="px-3 py-2.5 text-slate-600">{{ $index + 1 }}</td>
                                                            <td class="px-3 py-2.5 font-medium text-slate-800">{{ $row['office_name'] }}</td>
                                                            <td class="px-3 py-2.5 text-right font-semibold text-indigo-700">{{ number_format($row['accommodated_total']) }}</td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="3" class="px-3 py-4 text-center text-slate-500">No office accommodation data found.</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </section>
                                @endif

                                @if(!auth()->user()?->isSuperAdmin())
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
                                @endif
                            </div>
=======
                            @include('livewire.office-admin.partials.reports-dashboard-panel')
>>>>>>> d398d9293b8321be14f0b82fc8d6fffc844ca04d
                        @endif

                        @if($hrmoTab === 'queue-reports')
                            @include('livewire.office-admin.partials.queue-reports-dashboard-panel')
                        @endif

                        @if($hrmoTab === 'queue-management')
                            @if(auth()->user()?->isSuperAdmin())
                                <div class="space-y-6">
                                    <section class="overflow-hidden rounded-[1.75rem] border border-slate-200 bg-gradient-to-r from-slate-900 via-blue-900 to-cyan-800 p-1 shadow-sm" aria-labelledby="queue-management-mega-menu-heading">
                                        <div class="rounded-[calc(1.75rem-1px)] bg-white/95 p-5 sm:p-6">
                                            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                                                <div class="max-w-2xl">
                                                    <p class="text-xs font-semibold uppercase tracking-[0.24em] text-blue-700">Queue Management</p>
                                                    <h2 id="queue-management-mega-menu-heading" class="mt-2 text-2xl font-semibold text-slate-900">Records</h2>
                                                    <p class="mt-2 text-sm text-slate-600">
                                                        Switch between today&apos;s office queue activity and the overall ticket data accommodated by each office.
                                                    </p>
                                                </div>

                                                <div class="grid w-full gap-2 rounded-2xl bg-slate-100 p-2 sm:grid-cols-2 xl:max-w-2xl">
                                                    <button
                                                        type="button"
                                                        wire:click="setQueueManagementSection('queued-today')"
                                                        class="rounded-2xl border px-4 py-4 text-left transition {{ $queueManagementSection === 'queued-today' ? 'border-blue-600 bg-blue-600 text-white shadow-sm' : 'border-transparent bg-white text-slate-700 hover:border-slate-200 hover:bg-slate-50' }}"
                                                    >
                                                        <span class="block text-sm font-semibold">Queued Today</span>
                                                        <span class="mt-1 block text-xs {{ $queueManagementSection === 'queued-today' ? 'text-blue-100' : 'text-slate-500' }}">
                                                            Today&apos;s grouped office ticket activity, based on the current queue flow.
                                                        </span>
                                                    </button>
                                                    <button
                                                        type="button"
                                                        wire:click="setQueueManagementSection('overall-data')"
                                                        class="rounded-2xl border px-4 py-4 text-left transition {{ $queueManagementSection === 'overall-data' ? 'border-slate-900 bg-slate-900 text-white shadow-sm' : 'border-transparent bg-white text-slate-700 hover:border-slate-200 hover:bg-slate-50' }}"
                                                    >
                                                        <span class="block text-sm font-semibold">Overall Data</span>
                                                        <span class="mt-1 block text-xs {{ $queueManagementSection === 'overall-data' ? 'text-slate-300' : 'text-slate-500' }}">
                                                            Overall queued tickets and accommodated totals, with office filtering.
                                                        </span>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </section>

                                    @if($queueManagementSection === 'queued-today')
                                        <section class="lgu-card p-6" aria-labelledby="queued-today-heading">
                                            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                                <div>
                                                    <h2 id="queued-today-heading" class="lgu-section-title">Queued Today</h2>
                                                    <p class="mt-1 text-sm text-slate-500">Today&apos;s ticket activity grouped by office.</p>
                                                </div>

                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span class="text-xs font-medium text-slate-500">
                                                        Page {{ $queuedTodayPagination['current_page'] }} of {{ $queuedTodayPagination['last_page'] }}
                                                        | Showing {{ $queuedTodayPagination['from'] }}-{{ $queuedTodayPagination['to'] }} of {{ $queuedTodayPagination['total'] }} offices
                                                    </span>
                                                    <button
                                                        type="button"
                                                        wire:click="previousQueuedTodayPage"
                                                        @disabled(!$queuedTodayPagination['has_previous'])
                                                        class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                                                    >
                                                        Previous
                                                    </button>
                                                    <button
                                                        type="button"
                                                        wire:click="nextQueuedTodayPage"
                                                        @disabled(!$queuedTodayPagination['has_next'])
                                                        class="rounded-lg border border-blue-600 bg-blue-600 px-3 py-2 text-sm font-medium text-white transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:border-blue-300 disabled:bg-blue-300"
                                                    >
                                                        Next
                                                    </button>
                                                </div>
                                            </div>
                                        </section>

                                        @forelse($queuedTodayOfficeActivity as $officeActivity)
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
                                                            <tr class="border-b border-slate-200 text-left text-slate-500">
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
                                                                        <span class="rounded-full px-2 py-1 text-xs font-medium
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
                                        @empty
                                            <section class="lgu-card p-6">
                                                <p class="text-sm text-slate-500">No active offices are available for today&apos;s queue activity.</p>
                                            </section>
                                        @endforelse
                                    @else
                                        <section class="lgu-card p-6" aria-labelledby="overall-data-heading">
                                            <div class="flex flex-col gap-4 xl:flex-row xl:items-end xl:justify-between">
                                                <div>
                                                    <h2 id="overall-data-heading" class="lgu-section-title">Overall Data</h2>
                                                    <p class="mt-1 text-sm text-slate-500">
                                                        Review overall queued tickets and accommodated totals per office, then narrow the list using the office dropdown.
                                                    </p>
                                                </div>

                                                <div class="w-full max-w-sm">
                                                    <label for="queue-management-office-filter" class="mb-2 block text-xs font-semibold uppercase tracking-wide text-slate-500">
                                                        Filter by Office
                                                    </label>
                                                    <select
                                                        id="queue-management-office-filter"
                                                        wire:model.live="queueManagementOfficeFilter"
                                                        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-700 shadow-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                                                    >
                                                        <option value="all">All Offices</option>
                                                        @foreach($queueManagementOfficeOptions as $filterOffice)
                                                            <option value="{{ $filterOffice->slug }}">{{ $filterOffice->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
                                                <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Offices in Scope</p>
                                                    <p class="mt-2 text-3xl font-bold text-slate-900">{{ number_format($overallDataSummary['office_count']) }}</p>
                                                    <p class="mt-1 text-xs text-slate-500">{{ $queueManagementSelectedOfficeLabel }}</p>
                                                </div>
                                                <div class="rounded-2xl border border-blue-200 bg-blue-50 p-4">
                                                    <p class="text-xs font-semibold uppercase tracking-wide text-blue-700">Overall Queued Tickets</p>
                                                    <p class="mt-2 text-3xl font-bold text-blue-700">{{ number_format($overallDataSummary['overall_queued_total']) }}</p>
                                                    <p class="mt-1 text-xs text-blue-600">All recorded queue entries in the selected scope.</p>
                                                </div>
                                                <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4">
                                                    <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Accommodated Tickets</p>
                                                    <p class="mt-2 text-3xl font-bold text-emerald-700">{{ number_format($overallDataSummary['accommodated_total']) }}</p>
                                                    <p class="mt-1 text-xs text-emerald-600">Completed queue numbers marked accommodated by office admins.</p>
                                                </div>
                                            </div>
                                        </section>

                                        <section class="lgu-card p-6" aria-labelledby="overall-data-table-heading">
                                            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                                <div>
                                                    <h2 id="overall-data-table-heading" class="lgu-section-title">Overall Ticket Totals by Office</h2>
                                                    <p class="mt-1 text-sm text-slate-500">Showing {{ $queueManagementSelectedOfficeLabel }}.</p>
                                                </div>

                                                <div class="flex flex-wrap items-center gap-2">
                                                    <span class="text-xs font-medium text-slate-500">
                                                        Page {{ $overallDataPagination['current_page'] }} of {{ $overallDataPagination['last_page'] }}
                                                        | Showing {{ $overallDataPagination['from'] }}-{{ $overallDataPagination['to'] }} of {{ $overallDataPagination['total'] }} row(s)
                                                    </span>
                                                    <button
                                                        type="button"
                                                        wire:click="previousOverallDataPage"
                                                        @disabled(!$overallDataPagination['has_previous'])
                                                        class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-400 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                                                    >
                                                        Previous
                                                    </button>
                                                    <button
                                                        type="button"
                                                        wire:click="nextOverallDataPage"
                                                        @disabled(!$overallDataPagination['has_next'])
                                                        class="rounded-lg border border-slate-900 bg-slate-900 px-3 py-2 text-sm font-medium text-white transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:border-slate-300 disabled:bg-slate-300"
                                                    >
                                                        Next
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="mt-5 overflow-x-auto">
                                                <table class="w-full text-sm">
                                                    <thead>
                                                        <tr class="border-b border-slate-200 text-left text-slate-500">
                                                            <th class="w-56 px-3 py-2.5 font-semibold">Office</th>
                                                            <th class="px-3 py-2.5 font-semibold">Queue Ticket #</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse($overallDataRows as $row)
                                                            <tr class="border-b border-slate-100 last:border-b-0">
                                                                <td class="px-3 py-3 font-medium text-slate-800">{{ $row['office_name'] }}</td>
                                                                <td class="px-3 py-3 align-top">
                                                                    @php($completedQueueNumbers = collect($row['completed_queue_numbers'])->unique()->values())

                                                                    <div class="flex flex-wrap gap-2">
                                                                        @forelse($completedQueueNumbers as $queueNumber)
                                                                            <span class="inline-flex rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-xs font-semibold tracking-wide text-emerald-700">
                                                                                {{ $queueNumber }}
                                                                            </span>
                                                                        @empty
                                                                            <p class="text-xs text-slate-400">No completed queue numbers yet.</p>
                                                                        @endforelse
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="2" class="px-3 py-6 text-center text-slate-500">No overall data found for the selected office.</td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </section>
                                    @endif
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
                                                        <td colspan="5" class="py-6 text-center text-slate-500">No tickets yet for {{ $office->name }} today.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </section>
                            @endif
                        @endif

                        @if($hrmoTab === 'user-management' && auth()->user()?->isSuperAdmin())
                            <div class="space-y-6">
                                <section class="lgu-card p-6" aria-labelledby="user-management-heading">
                                    <div class="flex flex-wrap items-center justify-between gap-3">
                                        <div>
                                            <h2 id="user-management-heading" class="lgu-section-title">User Management</h2>
                                            <p class="mt-1 text-sm text-slate-500">Super Admin view of office-assigned users and queue activity status by office.</p>
                                        </div>
                                        <div class="flex flex-wrap gap-2 text-xs font-medium">
                                            <span class="rounded-full bg-emerald-100 px-3 py-1.5 text-emerald-700">
                                                Active: {{ $userManagementStatusSummary['active'] }}
                                            </span>
                                            <span class="rounded-full bg-slate-200 px-3 py-1.5 text-slate-700">
                                                Not Active: {{ $userManagementStatusSummary['inactive'] }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="mt-5 overflow-x-auto">
                                        <table class="w-full text-sm">
                                            <thead>
                                                <tr class="border-b border-slate-200 text-left text-slate-500">
                                                    <th class="px-3 py-2.5 font-semibold">Name</th>
                                                    <th class="px-3 py-2.5 font-semibold">Role</th>
                                                    <th class="px-3 py-2.5 font-semibold">Office</th>
                                                    <th class="px-3 py-2.5 font-semibold">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($userManagementRows as $userRow)
                                                    <tr class="border-b border-slate-100 last:border-b-0">
                                                        <td class="px-3 py-3 font-medium text-slate-800">{{ $userRow['name'] }}</td>
                                                        <td class="px-3 py-3 text-slate-600">{{ $userRow['role'] }}</td>
                                                        <td class="px-3 py-3 text-slate-600">{{ $userRow['office'] }}</td>
                                                        <td class="px-3 py-3">
                                                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $userRow['status_badge_class'] }}">
                                                                {{ $userRow['status_label'] }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @empty
                                                    <tr>
                                                        <td colspan="4" class="px-3 py-6 text-center text-slate-500">No office-assigned users found.</td>
                                                    </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </section>
                            </div>
                        @endif
                    </div>
            </div>
        </div>
    @else
        @include('livewire.office-admin.general-office-queue-operations-desk', [
            'liveMonitorRoute' => $liveMonitorRoute,
            'liveMonitorLabel' => $liveMonitorLabel,
        ])
    @endif
</div>
