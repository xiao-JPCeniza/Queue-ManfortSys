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
                            @include('livewire.office-admin.partials.reports-dashboard-panel')
                        @endif

                        @if($hrmoTab === 'queue-reports')
                            @include('livewire.office-admin.partials.queue-reports-dashboard-panel')
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
