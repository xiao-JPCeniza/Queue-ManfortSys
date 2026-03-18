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
    @php($isAllOfficesReportScope = auth()->user()?->isSuperAdmin() && $isSuperAdminRouteContext)
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
                            @include('livewire.office-admin.partials.reports-dashboard-panel')
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
                                            @include('livewire.office-admin.partials.queue-activity-panel', [
                                                'panelId' => 'overall-activity-' . $officeActivity['office']->slug,
                                                'heading' => 'Overall Ticket Activity (Today)',
                                                'kicker' => $officeActivity['office']->name,
                                                'description' => 'Live record of issued tickets and service milestones for this office today.',
                                                'entries' => $officeActivity['entries'],
                                                'emptyMessage' => 'No tickets yet for ' . $officeActivity['office']->name . ' today.',
                                            ])
                                        @empty
                                            <section class="lgu-card p-6">
                                                <p class="text-sm text-slate-500">No active offices are available for today&apos;s queue activity.</p>
                                            </section>
                                        @endforelse
                                    @else
                                        <section class="lgu-card p-6" aria-labelledby="overall-data-table-heading">
                                            <div class="flex flex-col gap-3 xl:flex-row xl:items-end xl:justify-between">
                                                <div>
                                                    <h2 id="overall-data-table-heading" class="lgu-section-title">Overall Ticket Totals by Office</h2>
                                                    <p class="mt-1 text-sm text-slate-500">Showing {{ $queueManagementSelectedOfficeLabel }}.</p>
                                                </div>

                                                <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-end sm:justify-end">
                                                    <div class="min-w-[220px]">
                                                        <label for="overall-data-office-filter" class="mb-1 block text-xs font-semibold uppercase tracking-[0.18em] text-slate-500">
                                                            Filter Office
                                                        </label>
                                                        <select
                                                            id="overall-data-office-filter"
                                                            wire:model.live="queueManagementOfficeFilter"
                                                            class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm font-medium text-slate-700 shadow-sm transition focus:border-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20"
                                                        >
                                                            <option value="all">All Offices</option>
                                                            @foreach($queueManagementOfficeOptions as $officeOption)
                                                                <option value="{{ $officeOption->slug }}">{{ $officeOption->name }}</option>
                                                            @endforeach
                                                        </select>
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
                                @include('livewire.office-admin.partials.queue-activity-panel', [
                                    'panelId' => 'overall-activity-heading',
                                    'heading' => 'Overall Ticket Activity (Today)',
                                    'kicker' => $office->name,
                                    'description' => 'Live record of issued tickets, call times, and completed service milestones for today.',
                                    'entries' => $overallTickets,
                                    'emptyMessage' => 'No tickets yet for ' . $office->name . ' today.',
                                ])
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
