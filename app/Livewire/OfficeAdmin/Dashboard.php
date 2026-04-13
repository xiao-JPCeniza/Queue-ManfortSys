<?php

namespace App\Livewire\OfficeAdmin;

use App\Livewire\OfficeAdmin\Concerns\HandlesOfficeQueueAnnouncements;
use App\Models\Office;
use App\Models\QueueEntry;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    use HandlesOfficeQueueAnnouncements;

    private const ADVANCED_QUEUE_DASHBOARD_SLUGS = [
        'hrmo',
        'business-permits',
        'bplo',
        'mho',
        'mswdo',
        'menro',
        'treasury',
        'accounting',
        'civil-registry',
        'assessors-office',
    ];

    private const HIDDEN_OVERALL_ACTIVITY_OFFICES = [
        'BFP Liaison',
        'Budget',
        'DILG',
        'Engineering',
        'GSO',
        'ICT Unit',
        'Internal Audit',
        'LDRRMO',
        'Legal Office',
        'MAO',
        "Mayor's Office",
        'MENRO',
        'MISO',
        'Motorpool Division',
        'MPDO',
        "Municipal Administrator's Office",
        'Municipal Library',
        'NCIP',
        'Negosyo Center',
        'OBO',
        'OSCA',
        'PDAO',
        'PESO',
        'PNP Liaison',
        'Procurement Division',
        'Public Market Office',
        'Sangguniang Bayan',
        'Slaughter Division',
        'Special Education Fund',
        'Sports Development',
        'Tourism Office',
        "Vice Mayor's Office",
    ];
    private const QUEUE_MANAGEMENT_SECTIONS = [
        'queued-today',
        'overall-data',
    ];

    private const QUEUED_TODAY_OFFICES_PER_PAGE = 3;

    private const OVERALL_DATA_ROWS_PER_PAGE = 6;

    public Office $office;

    public string $hrmoTab = 'queue-management';

    public bool $isSuperAdminRouteContext = false;

    public string $queueManagementSection = 'queued-today';

    public string $queueManagementOfficeFilter = 'all';

    public int $queuedTodayPage = 1;

    public int $overallDataPage = 1;

    public function mount(Office $office): void
    {
        $this->office = $office;
        $this->isSuperAdminRouteContext = request()->routeIs('super-admin.*');

        $superAdminTabByRoute = [
            'super-admin.reports' => 'reports',
            'super-admin.queue-management' => 'queue-management',
            'super-admin.user-management' => 'user-management',
        ];

        foreach ($superAdminTabByRoute as $routeName => $tab) {
            if (request()->routeIs($routeName)) {
                $this->hrmoTab = $tab;

                return;
            }
        }

        $defaultTab = $this->isSuperAdminRouteContext ? 'reports' : 'queue-management';
        $requestedTab = (string) request()->query('tab', $defaultTab);
        $allowedTabs = $this->allowedHrmoTabs();

        if ($this->supportsAdvancedQueueDashboard() && in_array($requestedTab, $allowedTabs, true)) {
            $this->hrmoTab = $requestedTab;
        }
    }

    public function setHrmoTab(string $tab): void
    {
        if (! $this->supportsAdvancedQueueDashboard()) {
            return;
        }

        $allowedTabs = $this->allowedHrmoTabs();

        if (! in_array($tab, $allowedTabs, true)) {
            return;
        }

        $this->hrmoTab = $tab;
    }

    public function setQueueManagementSection(string $section): void
    {
        if (! $this->isSuperAdmin() || $this->hrmoTab !== 'queue-management') {
            return;
        }

        if (! in_array($section, self::QUEUE_MANAGEMENT_SECTIONS, true)) {
            return;
        }

        $this->queueManagementSection = $section;
    }

    public function updatedQueueManagementOfficeFilter(string $officeSlug): void
    {
        $availableOfficeSlugs = $this->queueManagementOffices()->pluck('slug');

        if ($officeSlug !== 'all' && ! $availableOfficeSlugs->contains($officeSlug)) {
            $this->queueManagementOfficeFilter = 'all';
        }

        $this->overallDataPage = 1;
    }

    public function previousQueuedTodayPage(): void
    {
        $this->queuedTodayPage = max(1, $this->queuedTodayPage - 1);
    }

    public function nextQueuedTodayPage(): void
    {
        $this->queuedTodayPage++;
    }

    public function previousOverallDataPage(): void
    {
        $this->overallDataPage = max(1, $this->overallDataPage - 1);
    }

    public function nextOverallDataPage(): void
    {
        $this->overallDataPage++;
    }

    public function callNext(?int $windowNumber = null)
    {
        $requestedWindowNumber = $windowNumber;
        $windowNumber = $this->normalizeWindowNumber($windowNumber);

        $currentServing = $this->servingEntryForWindow($windowNumber);

        if ($currentServing !== null) {
            if ($requestedWindowNumber === null && ! $this->office->usesMultipleServiceWindows()) {
                $currentServing->update([
                    'status' => QueueEntry::STATUS_WAITING,
                    'service_window_number' => null,
                ]);
            } else {
                session()->flash(
                    'office_message',
                    $this->office->serviceWindowLabel($windowNumber).' is still handling an active ticket.'
                );

                return;
            }
        }

        $next = $this->orderedWaitingEntries($windowNumber)->first();

        if (! $next) {
            session()->flash('office_message', 'No one waiting in queue.');

            return;
        }

        $next->update([
            'status' => QueueEntry::STATUS_SERVING,
            'service_window_number' => $windowNumber,
            'called_at' => now(),
            'served_by' => $this->authenticatedUserId(),
        ]);

        $this->storeOfficeAnnouncement($this->office, 'serving', $next->queue_number, $windowNumber);
        session()->flash(
            'office_message',
            sprintf('Now serving %s at %s.', $next->queue_number, $this->office->serviceWindowAnnouncementLabel($windowNumber))
        );
    }

    public function complete(int $entryId)
    {
        $entry = QueueEntry::where('office_id', $this->office->id)->find($entryId);
        if ($entry && $entry->status === QueueEntry::STATUS_SERVING) {
            $entry->update([
                'status' => QueueEntry::STATUS_COMPLETED,
                'served_at' => now(),
                'served_by' => $this->authenticatedUserId(),
            ]);
        }
    }

    public function resetTickets(): void
    {
        [$dayStart, $dayEnd] = $this->manilaDayBounds();

        QueueEntry::where('office_id', $this->office->id)
            ->whereBetween('created_at', [$dayStart, $dayEnd])
            ->delete();

        $this->office->update(['next_number' => 1]);
        $this->office->refresh();

        session()->flash('office_message', 'Tickets reset. The next generated number will start from 001.');
    }

    public function clearTransaction(): void
    {
        $deletedCount = $this->todayOfficeQueueEntries()
            ->waiting()
            ->delete();

        [$dayStart, $dayEnd] = $this->manilaDayBounds();

        $clearedRecentCount = QueueEntry::where('office_id', $this->office->id)
            ->whereIn('status', [QueueEntry::STATUS_COMPLETED, QueueEntry::STATUS_NOT_SERVED])
            ->whereNotNull('served_at')
            ->whereBetween('served_at', [$dayStart, $dayEnd])
            ->whereNull('recent_transaction_cleared_at')
            ->update(['recent_transaction_cleared_at' => now()]);

        session()->flash(
            'office_message',
            ($deletedCount + $clearedRecentCount) > 0
                ? 'Waiting line and recent transactions were cleared.'
                : 'No waiting tickets or recent transactions found for today.'
        );
    }

    private function manilaDayBounds(): array
    {
        $manilaNow = now('Asia/Manila');
        $dbTimezone = (string) config('app.timezone', 'UTC');

        return [
            $manilaNow->copy()->startOfDay()->setTimezone($dbTimezone),
            $manilaNow->copy()->endOfDay()->setTimezone($dbTimezone),
        ];
    }

    private function todayOfficeQueueEntries()
    {
        [$dayStart, $dayEnd] = $this->manilaDayBounds();

        return $this->office->queueEntries()
            ->whereBetween('created_at', [$dayStart, $dayEnd]);
    }

    private function orderedWaitingEntries(?int $windowNumber = null): Collection
    {
        $waitingQuery = $this->todayOfficeQueueEntries()
            ->waiting()
            ->orderBy('created_at')
            ->orderBy('id');

        $this->applyQueueRoutingFilter($waitingQuery, $windowNumber);

        $waitingEntries = $waitingQuery
            ->get()
            ->map(function (QueueEntry $entry) {
                $entry->setRelation('office', $this->office);

                return $entry;
            });

        return QueueEntry::sortWaitingEntriesForService(
            $waitingEntries,
            $this->latestCalledOfficeEntry($windowNumber)
        );
    }

    private function latestCalledOfficeEntry(?int $windowNumber = null): ?QueueEntry
    {
        $query = $this->todayOfficeQueueEntries()
            ->whereNotNull('called_at');

        $this->applyQueueRoutingFilter($query, $windowNumber);

        return $query
            ->orderByDesc('called_at')
            ->orderByDesc('id')
            ->first()?->setRelation('office', $this->office);
    }

    public function render()
    {
        $waiting = $this->orderedWaitingEntries();

        $servingEntries = $this->todayOfficeQueueEntries()
            ->serving()
            ->orderByRaw('COALESCE(service_window_number, 1)')
            ->orderBy('called_at')
            ->orderBy('id')
            ->get()
            ->map(function (QueueEntry $entry) {
                if ($entry->service_window_number === null) {
                    $entry->service_window_number = 1;
                }

                $entry->setRelation('office', $this->office);

                return $entry;
            });

        $viewData = [
            'waiting' => $waiting,
            'serving' => $servingEntries->first(),
            'servingEntries' => $servingEntries,
            'serviceWindows' => $this->buildServiceWindows($servingEntries),
            'serviceWindowCount' => $this->office->resolvedServiceWindowCount(),
            'usesMultipleServiceWindows' => $this->office->usesMultipleServiceWindows(),
        ];

        if ($this->supportsAdvancedQueueDashboard()) {
            $viewData = array_merge($viewData, $this->buildAdvancedDashboardData());
        } else {
            $this->hrmoTab = 'queue-management';
            $viewData = array_merge($viewData, $this->defaultAdvancedDashboardData());
        }

        return view('livewire.office-admin.dashboard', $viewData);
    }

    private function buildServiceWindows(Collection $servingEntries): Collection
    {
        $servingByWindow = $servingEntries->keyBy(fn (QueueEntry $entry) => $entry->service_window_number ?? 1);

        return $this->office->accessibleServiceWindowNumbers()
            ->map(function (int $windowNumber) use ($servingByWindow) {
                return [
                    'number' => $windowNumber,
                    'label' => $this->office->serviceWindowLabel($windowNumber),
                    'entry' => $servingByWindow->get($windowNumber),
                ];
            })
            ->values();
    }

    private function normalizeWindowNumber(?int $windowNumber): int
    {
        $windowNumber = $windowNumber ?? 1;

        return min(
            max(1, $windowNumber),
            $this->office->resolvedServiceWindowCount()
        );
    }

    private function servingEntryForWindow(int $windowNumber): ?QueueEntry
    {
        return $this->todayOfficeQueueEntries()
            ->serving()
            ->where(function ($query) use ($windowNumber) {
                $query->where('service_window_number', $windowNumber);

                if ($windowNumber === 1) {
                    $query->orWhereNull('service_window_number');
                }
            })
            ->orderBy('called_at')
            ->orderBy('id')
            ->first()?->setRelation('office', $this->office);
    }

    private function applyQueueRoutingFilter($query, ?int $windowNumber = null): void
    {
        if ($windowNumber === null || ! $this->office->hasQueueServiceOptions()) {
            return;
        }

        $serviceKeys = $this->office->queueServiceKeysForWindow($windowNumber);

        if ($serviceKeys === []) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->where(function ($serviceQuery) use ($serviceKeys) {
            $serviceQuery->whereIn('service_key', $serviceKeys);

            if (! $this->office->hasConfiguredQueueServiceOptions()) {
                $serviceQuery->orWhereNull('service_key');
            }
        });
    }

    private function defaultAdvancedDashboardData(): array
    {
        return [
            'summary' => null,
            'overallTickets' => collect(),
            'overallTicketsByOffice' => collect(),
            'queuedTodayOfficeActivity' => collect(),
            'queueManagementOfficeOptions' => collect(),
            'queueManagementSelectedOfficeLabel' => 'All Offices',
            'queuedTodayPagination' => $this->emptyPaginationState(self::QUEUED_TODAY_OFFICES_PER_PAGE),
            'overallDataRows' => collect(),
            'overallDataSummary' => [
                'office_count' => 0,
                'overall_queued_total' => 0,
                'accommodated_total' => 0,
            ],
            'overallDataPagination' => $this->emptyPaginationState(self::OVERALL_DATA_ROWS_PER_PAGE),
            'statusBreakdown' => [],
            'statusPieStyle' => 'conic-gradient(#e2e8f0 0 100%)',
            'statusPieHasData' => false,
            'hourlyTicketSeries' => [],
            'hourlyMax' => 1,
            'peakHourLabel' => 'No tickets yet today',
            'monthlyVolumeSeries' => [],
            'monthlyVolumeMax' => 1,
            'monthlyPeakMonthLabel' => 'No tickets recorded this year',
            'monthlyStatusSeries' => [],
            'monthlyStatusLegend' => [],
            'monthlyScopeLabel' => 'Current Year',
            'officeAccommodatedSummary' => [],
            'officeAccommodatedChartSeries' => [],
            'officeAccommodatedPieStyle' => 'conic-gradient(#e2e8f0 0 100%)',
            'officeAccommodatedHasData' => false,
            'officeAccommodatedTotal' => 0,
            'officeAccommodatedMax' => 1,
            'queueReportDailyCounts' => [],
            'queueReportWeeklyCounts' => [],
            'queueReportStatusSummary' => [
                'served' => 0,
                'skipped' => 0,
            ],
            'queueReportScopeLabel' => $this->office->name,
            'userManagementRows' => [],
            'userManagementStatusSummary' => [
                'active' => 0,
                'inactive' => 0,
            ],
        ];
    }

    private function buildAdvancedDashboardData(): array
    {
        $data = $this->defaultAdvancedDashboardData();
        [$dayStart, $dayEnd] = $this->manilaDayBounds();
        $manilaNow = now('Asia/Manila');
        $dbTimezone = (string) config('app.timezone', 'UTC');
        $reportOfficeIds = $this->resolveReportOfficeIds();
        $statusMetadata = $this->statusMetadata();

        $todayEntries = QueueEntry::whereIn('office_id', $reportOfficeIds)
            ->whereBetween('created_at', [$dayStart, $dayEnd])
            ->get();

        $data['summary'] = $this->buildSummary($reportOfficeIds, $todayEntries);
        $data = array_merge($data, $this->buildStatusBreakdownData($todayEntries, $statusMetadata));
        $data = array_merge($data, $this->buildMonthlyChartData($reportOfficeIds, $manilaNow, $dbTimezone, $statusMetadata));
        $data = array_merge($data, $this->buildHourlyChartData($todayEntries));
        $data['overallTickets'] = $this->buildOverallTickets($dayStart, $dayEnd);

        if ($this->isSuperAdmin() && $this->hrmoTab === 'reports') {
            $data = array_merge($data, $this->buildOfficeAccommodatedData($reportOfficeIds));
            $data = array_merge($data, $this->buildQueueReportData($manilaNow, $dbTimezone));
        }

        if ($this->isSuperAdmin() && $this->hrmoTab === 'queue-management') {
            $data = array_merge($data, $this->buildQueueManagementData($dayStart, $dayEnd));
        }

        if ($this->isSuperAdmin() && $this->hrmoTab === 'user-management') {
            $data = array_merge($data, $this->buildUserManagementData($dayStart, $dayEnd));
        }

        return $data;
    }

    private function allowedHrmoTabs(): array
    {
        $allowedTabs = ['reports', 'queue-management'];

        if ($this->isSuperAdmin()) {
            $allowedTabs[] = 'user-management';
        }

        return $allowedTabs;
    }

    private function buildSummary(Collection $reportOfficeIds, Collection $todayEntries): array
    {
        $overallAccommodated = QueueEntry::query()
            ->whereIn('office_id', $reportOfficeIds)
            ->where('status', QueueEntry::STATUS_COMPLETED)
            ->count();

        return [
            'total_today' => $todayEntries->count(),
            'completed_today' => $todayEntries->where('status', QueueEntry::STATUS_COMPLETED)->count(),
            'overall_accommodated' => $overallAccommodated,
        ];
    }

    private function buildStatusBreakdownData(Collection $todayEntries, array $statusMetadata): array
    {
        $totalToday = $todayEntries->count();
        $statusBreakdown = collect($statusMetadata)->map(function (array $status) use ($todayEntries, $totalToday) {
            $count = $todayEntries->where('status', $status['key'])->count();
            $percentage = $totalToday > 0 ? round(($count / $totalToday) * 100, 1) : 0.0;

            return array_merge($status, [
                'count' => $count,
                'percentage' => $percentage,
            ]);
        })->all();

        $statusPieStyle = 'conic-gradient(#e2e8f0 0 100%)';
        $statusPieHasData = $totalToday > 0;

        if ($statusPieHasData) {
            $positiveStatuses = collect($statusBreakdown)
                ->filter(fn (array $status) => $status['count'] > 0)
                ->values();

            $runningCount = 0;
            $segments = [];
            $lastIndex = $positiveStatuses->count() - 1;

            foreach ($positiveStatuses as $index => $status) {
                $start = ($runningCount / $totalToday) * 100;
                $runningCount += $status['count'];
                $end = $index === $lastIndex
                    ? 100
                    : ($runningCount / $totalToday) * 100;

                $segments[] = $status['hex_color'].' '.number_format($start, 3, '.', '').'% '.number_format($end, 3, '.', '').'%';
            }

            if (! empty($segments)) {
                $statusPieStyle = 'conic-gradient('.implode(', ', $segments).')';
            }
        }

        return [
            'statusBreakdown' => $statusBreakdown,
            'statusPieStyle' => $statusPieStyle,
            'statusPieHasData' => $statusPieHasData,
        ];
    }

    private function buildMonthlyChartData(Collection $reportOfficeIds, $manilaNow, string $dbTimezone, array $statusMetadata): array
    {
        $monthlyStatusLegend = collect($statusMetadata)->map(function (array $status) {
            return [
                'label' => $status['label'],
                'chip_class' => $status['chip_class'],
            ];
        })->all();

        $startMonthManila = $manilaNow->copy()->startOfYear();
        $endMonthManila = $manilaNow->copy()->endOfYear();
        $monthStartDb = $startMonthManila->copy()->setTimezone($dbTimezone);
        $monthEndDb = $endMonthManila->copy()->setTimezone($dbTimezone);
        $monthlyScopeLabel = 'Current Year ('.$manilaNow->format('Y').')';

        $monthlyEntries = QueueEntry::whereIn('office_id', $reportOfficeIds)
            ->whereBetween('created_at', [$monthStartDb, $monthEndDb])
            ->get();

        $entriesByMonth = $monthlyEntries->groupBy(function (QueueEntry $entry) {
            return $entry->created_at->copy()->setTimezone('Asia/Manila')->format('Y-m');
        });

        $monthlyStatusSeries = collect(range(0, 11))->map(function (int $offset) use ($startMonthManila, $entriesByMonth, $statusMetadata) {
            $month = $startMonthManila->copy()->addMonths($offset);
            $monthKey = $month->format('Y-m');
            $monthEntries = $entriesByMonth->get($monthKey, collect());
            $total = $monthEntries->count();

            $segments = collect($statusMetadata)->map(function (array $status) use ($monthEntries, $total) {
                $count = $monthEntries->where('status', $status['key'])->count();
                $percentage = $total > 0 ? round(($count / $total) * 100, 1) : 0.0;

                return array_merge($status, [
                    'count' => $count,
                    'percentage' => $percentage,
                ]);
            })->all();

            return [
                'month_key' => $monthKey,
                'label' => $month->format('M Y'),
                'short_label' => $month->format('M'),
                'year_short' => $month->format('y'),
                'total' => $total,
                'segments' => $segments,
            ];
        })->all();

        $monthlyVolumeSeries = collect($monthlyStatusSeries)->map(function (array $monthRow) {
            return [
                'month_key' => $monthRow['month_key'],
                'label' => $monthRow['label'],
                'short_label' => $monthRow['short_label'],
                'year_short' => $monthRow['year_short'],
                'total' => $monthRow['total'],
            ];
        })->all();

        $monthlyVolumeMax = max(1, (int) collect($monthlyVolumeSeries)->max('total'));
        $monthlyPeakMonthLabel = 'No tickets recorded this year';
        $peakMonth = collect($monthlyVolumeSeries)->sortByDesc('total')->first();

        if ($peakMonth && $peakMonth['total'] > 0) {
            $monthlyPeakMonthLabel = $peakMonth['label'].' ('.$peakMonth['total'].' tickets)';
        }

        return [
            'monthlyVolumeSeries' => $monthlyVolumeSeries,
            'monthlyVolumeMax' => $monthlyVolumeMax,
            'monthlyPeakMonthLabel' => $monthlyPeakMonthLabel,
            'monthlyStatusSeries' => $monthlyStatusSeries,
            'monthlyStatusLegend' => $monthlyStatusLegend,
            'monthlyScopeLabel' => $monthlyScopeLabel,
        ];
    }

    private function buildHourlyChartData(Collection $todayEntries): array
    {
        $hourlyCounts = $todayEntries
            ->groupBy(function (QueueEntry $entry) {
                return (int) $entry->created_at->copy()->setTimezone('Asia/Manila')->format('G');
            })
            ->map(fn ($entries) => $entries->count());

        $hourlyTicketSeries = collect(range(0, 23))->map(function (int $hour) {
            $hourStart = now('Asia/Manila')->copy()->startOfDay()->setHour($hour);

            return [
                'hour' => $hour,
                'label' => $hourStart->format('g A'),
                'short_label' => $hourStart->format('ga'),
                'count' => 0,
            ];
        })->all();

        foreach ($hourlyTicketSeries as $index => $hourRow) {
            $hourlyTicketSeries[$index]['count'] = (int) ($hourlyCounts->get($hourRow['hour'], 0));
        }

        $hourlyMax = max(1, (int) collect($hourlyTicketSeries)->max('count'));
        $peakHourLabel = 'No tickets yet today';
        $peakHour = collect($hourlyTicketSeries)->sortByDesc('count')->first();

        if ($peakHour && $peakHour['count'] > 0) {
            $peakHourLabel = $peakHour['label'].' ('.$peakHour['count'].' tickets)';
        }

        return [
            'hourlyTicketSeries' => $hourlyTicketSeries,
            'hourlyMax' => $hourlyMax,
            'peakHourLabel' => $peakHourLabel,
        ];
    }

    private function buildOverallTickets($dayStart, $dayEnd): Collection
    {
        return QueueEntry::where('office_id', $this->office->id)
            ->where($this->activityWithinDayScope($dayStart, $dayEnd))
            ->orderByDesc('served_at')
            ->orderByDesc('called_at')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();
    }

    private function buildOverallTicketsByOffice($dayStart, $dayEnd): Collection
    {
        $officeList = $this->queueManagementOffices();

        $entriesByOffice = QueueEntry::query()
            ->with('office:id,name,slug')
            ->whereIn('office_id', $officeList->pluck('id'))
            ->where($this->activityWithinDayScope($dayStart, $dayEnd))
            ->orderByDesc('served_at')
            ->orderByDesc('called_at')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('office_id');

        return $officeList->map(function (Office $office) use ($entriesByOffice) {
            return [
                'office' => $office,
                'entries' => $entriesByOffice->get($office->id, collect()),
            ];
        })->values();
    }

    private function buildQueueManagementData($dayStart, $dayEnd): array
    {
        $this->normalizeQueueManagementSection();

        $queueManagementOfficeOptions = $this->queueManagementOffices();
        $this->normalizeQueueManagementOfficeFilter($queueManagementOfficeOptions);

        $overallTicketsByOffice = $this->buildOverallTicketsByOffice($dayStart, $dayEnd);
        $queuedTodayPagination = $this->paginateCollection(
            $overallTicketsByOffice,
            $this->queuedTodayPage,
            self::QUEUED_TODAY_OFFICES_PER_PAGE
        );
        $this->queuedTodayPage = $queuedTodayPagination['current_page'];

        $overallDataRows = $this->buildQueueManagementOverallDataRows($queueManagementOfficeOptions);
        $filteredOverallDataRows = $this->filterQueueManagementOverallDataRows($overallDataRows);
        $overallDataPagination = $this->paginateCollection(
            $filteredOverallDataRows,
            $this->overallDataPage,
            self::OVERALL_DATA_ROWS_PER_PAGE
        );
        $this->overallDataPage = $overallDataPagination['current_page'];

        $selectedOffice = $this->queueManagementOfficeFilter === 'all'
            ? null
            : $queueManagementOfficeOptions->firstWhere('slug', $this->queueManagementOfficeFilter);

        return [
            'overallTicketsByOffice' => $overallTicketsByOffice,
            'queuedTodayOfficeActivity' => $queuedTodayPagination['items'],
            'queueManagementOfficeOptions' => $queueManagementOfficeOptions,
            'queueManagementSelectedOfficeLabel' => $selectedOffice?->name ?? 'All Offices',
            'queuedTodayPagination' => $queuedTodayPagination,
            'overallDataRows' => $overallDataPagination['items'],
            'overallDataSummary' => [
                'office_count' => $filteredOverallDataRows->count(),
                'overall_queued_total' => (int) $filteredOverallDataRows->sum('overall_queued_total'),
                'accommodated_total' => (int) $filteredOverallDataRows->sum('accommodated_total'),
            ],
            'overallDataPagination' => $overallDataPagination,
        ];
    }

    private function queueManagementOffices(): Collection
    {
        return Office::query()
            ->activePublicQueue()
            ->orderBy('name')
            ->get(['id', 'name', 'slug']);
    }

    private function buildQueueManagementOverallDataRows(Collection $officeList): Collection
    {
        $officeIds = $officeList->pluck('id');
        $overallQueuedCounts = QueueEntry::query()
            ->selectRaw('office_id, COUNT(*) as total')
            ->whereIn('office_id', $officeIds)
            ->groupBy('office_id')
            ->pluck('total', 'office_id');

        $accommodatedCounts = $this->completedQueueCountsByOffice($officeIds);
        $completedQueueNumbers = $this->completedQueueNumbersByOffice($officeIds);
        $completedQueueDetails = $this->completedQueueDetailsByOffice($officeIds);

        return $officeList
            ->map(function (Office $office) use ($overallQueuedCounts, $accommodatedCounts, $completedQueueNumbers, $completedQueueDetails) {
                return [
                    'office_id' => $office->id,
                    'office_slug' => $office->slug,
                    'office_name' => $office->name,
                    'overall_queued_total' => (int) ($overallQueuedCounts->get($office->id, 0)),
                    'accommodated_total' => (int) ($accommodatedCounts->get($office->id, 0)),
                    'completed_queue_numbers' => $completedQueueNumbers->get($office->id, []),
                    'completed_queue_details' => $completedQueueDetails->get($office->id, []),
                ];
            })
            ->sortBy([
                ['overall_queued_total', 'desc'],
                ['office_name', 'asc'],
            ])
            ->values();
    }

    private function filterQueueManagementOverallDataRows(Collection $overallDataRows): Collection
    {
        if ($this->queueManagementOfficeFilter === 'all') {
            return $overallDataRows->values();
        }

        return $overallDataRows
            ->where('office_slug', $this->queueManagementOfficeFilter)
            ->values();
    }

    private function paginateCollection(Collection $items, int $page, int $perPage): array
    {
        $perPage = max(1, $perPage);
        $total = $items->count();
        $lastPage = max(1, (int) ceil($total / $perPage));
        $currentPage = min(max(1, $page), $lastPage);
        $from = $total === 0 ? 0 : (($currentPage - 1) * $perPage) + 1;
        $to = $total === 0 ? 0 : min($total, $currentPage * $perPage);

        return [
            'items' => $items->slice($from > 0 ? $from - 1 : 0, $perPage)->values(),
            'current_page' => $currentPage,
            'last_page' => $lastPage,
            'per_page' => $perPage,
            'total' => $total,
            'from' => $from,
            'to' => $to,
            'has_previous' => $currentPage > 1,
            'has_next' => $currentPage < $lastPage,
        ];
    }

    private function emptyPaginationState(int $perPage): array
    {
        return [
            'current_page' => 1,
            'last_page' => 1,
            'per_page' => $perPage,
            'total' => 0,
            'from' => 0,
            'to' => 0,
            'has_previous' => false,
            'has_next' => false,
        ];
    }

    private function activityWithinDayScope($dayStart, $dayEnd): \Closure
    {
        return function ($query) use ($dayStart, $dayEnd) {
            $query->where(function ($activityQuery) use ($dayStart, $dayEnd) {
                $activityQuery->whereBetween('created_at', [$dayStart, $dayEnd])
                    ->orWhereBetween('called_at', [$dayStart, $dayEnd])
                    ->orWhereBetween('served_at', [$dayStart, $dayEnd]);
            });
        };
    }

    private function buildUserManagementData($dayStart, $dayEnd): array
    {
        $managedOffices = Office::query()
            ->activePublicQueue()
            ->get(['id', 'name', 'slug']);

        $managedOffices = Office::sortPublicQueueOffices($managedOffices);

        $activeOfficeIds = QueueEntry::query()
            ->whereIn('office_id', $managedOffices->pluck('id'))
            ->whereIn('status', [QueueEntry::STATUS_WAITING, QueueEntry::STATUS_SERVING])
            ->where(function ($query) use ($dayStart, $dayEnd) {
                $query->whereBetween('created_at', [$dayStart, $dayEnd])
                    ->orWhereBetween('called_at', [$dayStart, $dayEnd]);
            })
            ->pluck('office_id')
            ->unique();

        $managedOfficeIds = $managedOffices->pluck('id');

        $userManagementRows = User::query()
            ->with([
                'role:id,name,slug',
                'office:id,name,slug',
            ])
            ->where(function ($query) use ($managedOfficeIds) {
                $query->whereIn('office_id', $managedOfficeIds)
                    ->orWhereHas('role', function ($roleQuery) {
                        $roleQuery->where('slug', 'super_admin');
                    });
            })
            ->get()
            ->sortBy(function (User $user) {
                return sprintf(
                    '%s|%s|%s',
                    strtolower((string) $user->office?->name),
                    strtolower((string) $user->role?->name),
                    strtolower($user->name)
                );
            })
            ->values()
            ->map(function (User $user) use ($activeOfficeIds) {
                $isQueueActive = $user->isSuperAdmin()
                    || ($user->office_id !== null && $activeOfficeIds->contains($user->office_id));
                $passwordInfo = $this->resolveUserManagementPasswordInfo($user);

                return [
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role?->name ?? 'Unassigned',
                    'office' => $user->isSuperAdmin()
                        ? 'All Offices'
                        : ($user->office?->name ?? 'Unassigned'),
                    'status_label' => $isQueueActive ? 'Active' : 'Not Active',
                    'status_badge_class' => $isQueueActive
                        ? 'bg-emerald-100 text-emerald-700'
                        : 'bg-slate-200 text-slate-600',
                    'password_value' => $passwordInfo['value'],
                    'password_help' => $passwordInfo['help'],
                    'password_is_recoverable' => $passwordInfo['is_recoverable'],
                ];
            })
            ->all();

        return [
            'userManagementRows' => $userManagementRows,
            'userManagementStatusSummary' => [
                'active' => collect($userManagementRows)->where('status_label', 'Active')->count(),
                'inactive' => collect($userManagementRows)->where('status_label', 'Not Active')->count(),
            ],
        ];
    }

    private function buildQueueReportData($manilaNow, string $dbTimezone): array
    {
        $queueReportOfficeIds = collect([$this->office->id]);
        $queueReportScopeLabel = $this->office->name;

        if ($this->isSuperAdmin()) {
            $queueReportOfficeIds = Office::query()
                ->activePublicQueue()
                ->pluck('id');
            $queueReportScopeLabel = 'All Offices';
        }

        $dailyStartManila = $manilaNow->copy()->startOfDay()->subDays(6);
        $dailyEndManila = $manilaNow->copy()->endOfDay();

        $dailyEntries = QueueEntry::whereIn('office_id', $queueReportOfficeIds)
            ->whereBetween('created_at', [
                $dailyStartManila->copy()->setTimezone($dbTimezone),
                $dailyEndManila->copy()->setTimezone($dbTimezone),
            ])
            ->get(['created_at']);

        $dailyCountMap = $dailyEntries
            ->groupBy(fn (QueueEntry $entry) => $entry->created_at->copy()->setTimezone('Asia/Manila')->format('Y-m-d'))
            ->map(fn ($entries) => $entries->count());

        $queueReportDailyCounts = collect(range(0, 6))->map(function (int $offset) use ($dailyStartManila, $dailyCountMap) {
            $day = $dailyStartManila->copy()->addDays($offset);
            $dayKey = $day->format('Y-m-d');

            return [
                'date' => $dayKey,
                'total_tickets' => (int) ($dailyCountMap->get($dayKey, 0)),
            ];
        })
            ->filter(fn (array $row) => $row['total_tickets'] > 0)
            ->values()
            ->all();

        $weeklyStartManila = $manilaNow->copy()->startOfWeek()->subWeeks(4);
        $weeklyEndManila = $manilaNow->copy()->endOfWeek();

        $weeklyEntries = QueueEntry::whereIn('office_id', $queueReportOfficeIds)
            ->whereBetween('created_at', [
                $weeklyStartManila->copy()->setTimezone($dbTimezone),
                $weeklyEndManila->copy()->setTimezone($dbTimezone),
            ])
            ->get(['created_at']);

        $weeklyCountMap = $weeklyEntries
            ->groupBy(fn (QueueEntry $entry) => $entry->created_at->copy()->setTimezone('Asia/Manila')->format('oW'))
            ->map(fn ($entries) => $entries->count());

        $queueReportWeeklyCounts = collect(range(0, 4))->map(function (int $offset) use ($weeklyStartManila, $weeklyCountMap) {
            $weekStart = $weeklyStartManila->copy()->addWeeks($offset);
            $weekKey = $weekStart->format('oW');

            return [
                'week' => $weekKey,
                'total_tickets' => (int) ($weeklyCountMap->get($weekKey, 0)),
            ];
        })
            ->filter(fn (array $row) => $row['total_tickets'] > 0)
            ->values()
            ->all();

        $queueReportStatusSummary = [
            'served' => QueueEntry::whereIn('office_id', $queueReportOfficeIds)
                ->where('status', QueueEntry::STATUS_COMPLETED)
                ->count(),
            'skipped' => QueueEntry::whereIn('office_id', $queueReportOfficeIds)
                ->where('status', QueueEntry::STATUS_NOT_SERVED)
                ->count(),
        ];

        return [
            'queueReportDailyCounts' => $queueReportDailyCounts,
            'queueReportWeeklyCounts' => $queueReportWeeklyCounts,
            'queueReportStatusSummary' => $queueReportStatusSummary,
            'queueReportScopeLabel' => $queueReportScopeLabel,
        ];
    }

    private function buildOfficeAccommodatedData(Collection $reportOfficeIds): array
    {
        $reportOffices = Office::sortPublicQueueOffices(
            Office::query()
                ->whereIn('id', $reportOfficeIds)
                ->activePublicQueue()
                ->get(['id', 'name'])
        );

        $completedCounts = $this->completedQueueCountsByOffice($reportOffices->pluck('id'));

        $officeAccommodatedSummary = $reportOffices
            ->map(function (Office $office) use ($completedCounts) {
                return [
                    'office_name' => $office->name,
                    'accommodated_total' => (int) ($completedCounts->get($office->id, 0)),
                ];
            })
            ->sortBy([
                ['accommodated_total', 'desc'],
                ['office_name', 'asc'],
            ])
            ->values()
            ->all();

        $officeChartPalette = [
            ['hex_color' => '#3b82f6', 'bar_class' => 'bg-blue-500', 'chip_class' => 'bg-blue-500'],
            ['hex_color' => '#10b981', 'bar_class' => 'bg-emerald-500', 'chip_class' => 'bg-emerald-500'],
            ['hex_color' => '#f59e0b', 'bar_class' => 'bg-amber-500', 'chip_class' => 'bg-amber-500'],
            ['hex_color' => '#f43f5e', 'bar_class' => 'bg-rose-500', 'chip_class' => 'bg-rose-500'],
            ['hex_color' => '#8b5cf6', 'bar_class' => 'bg-violet-500', 'chip_class' => 'bg-violet-500'],
            ['hex_color' => '#06b6d4', 'bar_class' => 'bg-cyan-500', 'chip_class' => 'bg-cyan-500'],
            ['hex_color' => '#14b8a6', 'bar_class' => 'bg-teal-500', 'chip_class' => 'bg-teal-500'],
            ['hex_color' => '#6366f1', 'bar_class' => 'bg-indigo-500', 'chip_class' => 'bg-indigo-500'],
        ];

        $paletteCount = count($officeChartPalette);
        $officeAccommodatedTotal = (int) collect($officeAccommodatedSummary)->sum('accommodated_total');
        $officeAccommodatedMax = max(1, (int) collect($officeAccommodatedSummary)->max('accommodated_total'));
        $officeAccommodatedChartSeries = collect($officeAccommodatedSummary)
            ->values()
            ->map(function (array $officeRow, int $index) use ($officeAccommodatedTotal, $officeChartPalette, $paletteCount) {
                $palette = $officeChartPalette[$index % $paletteCount];
                $percentage = $officeAccommodatedTotal > 0
                    ? round(($officeRow['accommodated_total'] / $officeAccommodatedTotal) * 100, 1)
                    : 0.0;

                return array_merge($officeRow, [
                    'percentage' => $percentage,
                    'hex_color' => $palette['hex_color'],
                    'bar_class' => $palette['bar_class'],
                    'chip_class' => $palette['chip_class'],
                ]);
            })
            ->all();

        $officeAccommodatedPieStyle = 'conic-gradient(#e2e8f0 0 100%)';
        $officeAccommodatedHasData = $officeAccommodatedTotal > 0;

        if ($officeAccommodatedHasData) {
            $positiveOfficeSegments = collect($officeAccommodatedChartSeries)
                ->filter(fn (array $officeRow) => $officeRow['accommodated_total'] > 0)
                ->values();

            $runningCount = 0;
            $pieSegments = [];
            $lastIndex = $positiveOfficeSegments->count() - 1;

            foreach ($positiveOfficeSegments as $index => $officeRow) {
                $start = ($runningCount / $officeAccommodatedTotal) * 100;
                $runningCount += $officeRow['accommodated_total'];
                $end = $index === $lastIndex
                    ? 100
                    : ($runningCount / $officeAccommodatedTotal) * 100;

                $pieSegments[] = $officeRow['hex_color'].' '.number_format($start, 3, '.', '').'% '.number_format($end, 3, '.', '').'%';
            }

            if (! empty($pieSegments)) {
                $officeAccommodatedPieStyle = 'conic-gradient('.implode(', ', $pieSegments).')';
            }
        }

        return [
            'officeAccommodatedSummary' => $officeAccommodatedSummary,
            'officeAccommodatedChartSeries' => $officeAccommodatedChartSeries,
            'officeAccommodatedPieStyle' => $officeAccommodatedPieStyle,
            'officeAccommodatedHasData' => $officeAccommodatedHasData,
            'officeAccommodatedTotal' => $officeAccommodatedTotal,
            'officeAccommodatedMax' => $officeAccommodatedMax,
        ];
    }

    private function completedQueueCountsByOffice(Collection $officeIds): Collection
    {
        if ($officeIds->isEmpty()) {
            return collect();
        }

        return QueueEntry::query()
            ->selectRaw('office_id, COUNT(*) as total')
            ->whereIn('office_id', $officeIds)
            ->where('status', QueueEntry::STATUS_COMPLETED)
            ->groupBy('office_id')
            ->pluck('total', 'office_id');
    }

    private function completedQueueNumbersByOffice(Collection $officeIds): Collection
    {
        if ($officeIds->isEmpty()) {
            return collect();
        }

        return QueueEntry::query()
            ->select(['office_id', 'queue_number'])
            ->whereIn('office_id', $officeIds)
            ->where('status', QueueEntry::STATUS_COMPLETED)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get()
            ->groupBy('office_id')
            ->map(fn (Collection $entries) => $entries->pluck('queue_number')->values()->all());
    }

    private function completedQueueDetailsByOffice(Collection $officeIds): Collection
    {
        if ($officeIds->isEmpty()) {
            return collect();
        }

        return QueueEntry::query()
            ->select(['office_id', 'queue_number', 'created_at'])
            ->whereIn('office_id', $officeIds)
            ->where('status', QueueEntry::STATUS_COMPLETED)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get()
            ->groupBy('office_id')
            ->map(function (Collection $entries) {
                return $entries
                    ->map(function (QueueEntry $entry) {
                        $queuedAt = $entry->displayCreatedAt('Asia/Manila');

                        return [
                            'queue_number' => $entry->queue_number,
                            'queued_at_label' => $queuedAt?->format('M d, Y h:i:s A') ?? 'Unknown queue time',
                        ];
                    })
                    ->values()
                    ->all();
            });
    }

    private function statusMetadata(): array
    {
        return [
            ['key' => QueueEntry::STATUS_COMPLETED, 'label' => 'Completed', 'bar_class' => 'bg-emerald-500', 'chip_class' => 'bg-emerald-500', 'hex_color' => '#10b981'],
            ['key' => QueueEntry::STATUS_SERVING, 'label' => 'Serving', 'bar_class' => 'bg-sky-500', 'chip_class' => 'bg-sky-500', 'hex_color' => '#0ea5e9'],
            ['key' => QueueEntry::STATUS_WAITING, 'label' => 'Waiting', 'bar_class' => 'bg-amber-500', 'chip_class' => 'bg-amber-500', 'hex_color' => '#f59e0b'],
        ];
    }

    private function resolveReportOfficeIds(): Collection
    {
        if ($this->shouldUseAllOfficesReportScope()) {
            return Office::query()->pluck('id');
        }

        return collect([$this->office->id]);
    }

    private function shouldUseAllOfficesReportScope(): bool
    {
        return $this->isSuperAdmin() && $this->isSuperAdminRouteContext;
    }

    private function currentUser(): ?User
    {
        $user = Auth::user();

        return $user instanceof User ? $user : null;
    }

    private function authenticatedUserId(): int|string|null
    {
        return Auth::id();
    }

    private function isSuperAdmin(): bool
    {
        return $this->currentUser()?->isSuperAdmin() ?? false;
    }

    private function normalizeQueueManagementSection(): void
    {
        if (! in_array($this->queueManagementSection, self::QUEUE_MANAGEMENT_SECTIONS, true)) {
            $this->queueManagementSection = 'queued-today';
        }
    }

    private function normalizeQueueManagementOfficeFilter(Collection $officeList): void
    {
        $availableOfficeSlugs = $officeList->pluck('slug');

        if ($this->queueManagementOfficeFilter !== 'all' && ! $availableOfficeSlugs->contains($this->queueManagementOfficeFilter)) {
            $this->queueManagementOfficeFilter = 'all';
        }
    }

    private function supportsAdvancedQueueDashboard(): bool
    {
        return true;
    }

    private function resolveUserManagementPasswordInfo(User $user): array
    {
        $recoverablePassword = trim((string) $user->recoverable_password);

        if ($recoverablePassword !== '') {
            return [
                'value' => $recoverablePassword,
                'help' => 'This is the current password on file for this account.',
                'is_recoverable' => true,
            ];
        }

        $hashedPassword = (string) $user->getAuthPassword();

        if ($hashedPassword !== '' && Hash::check('password', $hashedPassword)) {
            return [
                'value' => 'password',
                'help' => 'This account is still using the default password.',
                'is_recoverable' => true,
            ];
        }

        return [
            'value' => 'Unavailable',
            'help' => 'This password was not stored for this account. Set or reset the password again to make it visible here.',
            'is_recoverable' => false,
        ];
    }
}
