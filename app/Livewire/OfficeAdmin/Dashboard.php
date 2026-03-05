<?php

namespace App\Livewire\OfficeAdmin;

use App\Models\Office;
use App\Models\QueueEntry;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Illuminate\Support\Facades\Schema;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public Office $office;
    public string $hrmoTab = 'dashboard';

    public function mount(Office $office): void
    {
        $this->office = $office;

        $requestedTab = (string) request()->query('tab', 'dashboard');
        $allowedTabs = ['dashboard', 'reports', 'queue-reports', 'queue-management'];

        if ($this->office->slug === 'hrmo' && in_array($requestedTab, $allowedTabs, true)) {
            $this->hrmoTab = $requestedTab;
        }
    }

    public function setHrmoTab(string $tab): void
    {
        if ($this->office->slug !== 'hrmo') {
            return;
        }

        $allowedTabs = ['dashboard', 'reports', 'queue-reports', 'queue-management'];
        if (!in_array($tab, $allowedTabs, true)) {
            return;
        }

        $this->hrmoTab = $tab;
    }

    public function callNext()
    {
        $next = $this->office->queueEntries()
            ->waiting()
            ->orderBy('created_at')
            ->first();

        if (!$next) {
            session()->flash('office_message', 'No one waiting in queue.');
            return;
        }

        $this->office->queueEntries()->serving()->update(['status' => QueueEntry::STATUS_WAITING]);

        $next->update([
            'status' => QueueEntry::STATUS_SERVING,
            'called_at' => now(),
            'served_by' => auth()->id(),
        ]);

        session()->flash('office_message', "Now serving {$next->queue_number}");
    }

    public function complete(int $entryId)
    {
        $entry = QueueEntry::where('office_id', $this->office->id)->find($entryId);
        if ($entry && $entry->status === QueueEntry::STATUS_SERVING) {
            $entry->update([
                'status' => QueueEntry::STATUS_COMPLETED,
                'served_at' => now(),
                'served_by' => auth()->id(),
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
        [$dayStart, $dayEnd] = $this->manilaDayBounds();

        $deletedCount = QueueEntry::where('office_id', $this->office->id)
            ->whereIn('status', [QueueEntry::STATUS_COMPLETED, QueueEntry::STATUS_NOT_SERVED])
            ->whereBetween('served_at', [$dayStart, $dayEnd])
            ->delete();

        session()->flash(
            'office_message',
            $deletedCount > 0
                ? 'Recent transactions for today were cleared.'
                : 'No recent transactions found for today.'
        );
    }

    private function ensureCurrentServing(): void
    {
        $servingExists = QueueEntry::where('office_id', $this->office->id)
            ->serving()
            ->exists();

        if ($servingExists) {
            return;
        }

        $next = QueueEntry::where('office_id', $this->office->id)
            ->waiting()
            ->orderBy('created_at')
            ->first();

        if (!$next) {
            return;
        }

        $next->update([
            'status' => QueueEntry::STATUS_SERVING,
            'called_at' => now(),
            'served_by' => auth()->id(),
        ]);
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

    public function render()
    {
        $this->ensureCurrentServing();

        $waiting = $this->office->queueEntries()
            ->waiting()
            ->orderBy('created_at')
            ->get();

        $serving = $this->office->queueEntries()
            ->serving()
            ->first();

        $summary = null;
        $overallTickets = collect();
        $statusBreakdown = [];
        $statusPieStyle = 'conic-gradient(#e2e8f0 0 100%)';
        $statusPieHasData = false;
        $hourlyTicketSeries = [];
        $hourlyMax = 1;
        $peakHourLabel = 'No tickets yet today';
        $monthlyVolumeSeries = [];
        $monthlyVolumeMax = 1;
        $monthlyPeakMonthLabel = 'No tickets in the last 12 months';
        $monthlyStatusSeries = [];
        $monthlyStatusLegend = [];
        $queueReportDailyCounts = [];
        $queueReportWeeklyCounts = [];
        $queueReportStatusSummary = [
            'served' => 0,
            'skipped' => 0,
        ];
        $queueReportAverageProcessingTime = '00h 00m 00s';
        if ($this->office->slug === 'hrmo') {
            [$dayStart, $dayEnd] = $this->manilaDayBounds();
            $manilaNow = now('Asia/Manila');
            $dbTimezone = (string) config('app.timezone', 'UTC');

            $todayEntries = QueueEntry::where('office_id', $this->office->id)
                ->whereBetween('created_at', [$dayStart, $dayEnd])
                ->get();

            $totalToday = $todayEntries->count();
            $overallAccommodated = Schema::hasColumn('offices', 'tickets_accommodated_total')
                ? (int) Office::query()
                    ->whereKey($this->office->id)
                    ->value('tickets_accommodated_total')
                : QueueEntry::where('office_id', $this->office->id)->count();

            $summary = [
                'total_today' => $totalToday,
                'completed_today' => $todayEntries
                    ->where('status', QueueEntry::STATUS_COMPLETED)
                    ->count(),
                'overall_accommodated' => $overallAccommodated,
            ];

            $statusMetadata = [
                ['key' => QueueEntry::STATUS_COMPLETED, 'label' => 'Completed', 'bar_class' => 'bg-emerald-500', 'chip_class' => 'bg-emerald-500', 'hex_color' => '#10b981'],
                ['key' => QueueEntry::STATUS_SERVING, 'label' => 'Serving', 'bar_class' => 'bg-sky-500', 'chip_class' => 'bg-sky-500', 'hex_color' => '#0ea5e9'],
                ['key' => QueueEntry::STATUS_WAITING, 'label' => 'Waiting', 'bar_class' => 'bg-amber-500', 'chip_class' => 'bg-amber-500', 'hex_color' => '#f59e0b'],
                ['key' => QueueEntry::STATUS_NOT_SERVED, 'label' => 'Not Served', 'bar_class' => 'bg-rose-500', 'chip_class' => 'bg-rose-500', 'hex_color' => '#f43f5e'],
                ['key' => QueueEntry::STATUS_CANCELLED, 'label' => 'Cancelled', 'bar_class' => 'bg-slate-400', 'chip_class' => 'bg-slate-400', 'hex_color' => '#94a3b8'],
            ];

            $monthlyStatusLegend = collect($statusMetadata)->map(function (array $status) {
                return [
                    'label' => $status['label'],
                    'chip_class' => $status['chip_class'],
                ];
            })->all();

            $statusBreakdown = collect($statusMetadata)->map(function (array $status) use ($todayEntries, $totalToday) {
                $count = $todayEntries->where('status', $status['key'])->count();
                $percentage = $totalToday > 0 ? round(($count / $totalToday) * 100, 1) : 0.0;

                return [
                    ...$status,
                    'count' => $count,
                    'percentage' => $percentage,
                ];
            })->all();

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

                if (!empty($segments)) {
                    $statusPieStyle = 'conic-gradient('.implode(', ', $segments).')';
                }
            }

            $startMonthManila = $manilaNow->copy()->startOfMonth()->subMonths(11);
            $endMonthManila = $manilaNow->copy()->endOfMonth();
            $monthStartDb = $startMonthManila->copy()->setTimezone($dbTimezone);
            $monthEndDb = $endMonthManila->copy()->setTimezone($dbTimezone);

            $monthlyEntries = QueueEntry::where('office_id', $this->office->id)
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

                    return [
                        ...$status,
                        'count' => $count,
                        'percentage' => $percentage,
                    ];
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
            $peakMonth = collect($monthlyVolumeSeries)->sortByDesc('total')->first();

            if ($peakMonth && $peakMonth['total'] > 0) {
                $monthlyPeakMonthLabel = $peakMonth['label'].' ('.$peakMonth['total'].' tickets)';
            }

            $hourlyCounts = $todayEntries
                ->groupBy(function (QueueEntry $entry) {
                    return (int) $entry->created_at->copy()->setTimezone('Asia/Manila')->format('G');
                })
                ->map(fn ($entries) => $entries->count());

            $hourlyTicketSeries = collect(range(0, 23))->map(function (int $hour) use ($hourlyCounts) {
                $hourStart = now('Asia/Manila')->copy()->startOfDay()->setHour($hour);

                return [
                    'hour' => $hour,
                    'label' => $hourStart->format('g A'),
                    'short_label' => $hourStart->format('ga'),
                    'count' => (int) ($hourlyCounts->get($hour, 0)),
                ];
            })->all();

            $hourlyMax = max(1, (int) collect($hourlyTicketSeries)->max('count'));
            $peakHour = collect($hourlyTicketSeries)->sortByDesc('count')->first();

            if ($peakHour && $peakHour['count'] > 0) {
                $peakHourLabel = $peakHour['label'].' ('.$peakHour['count'].' tickets)';
            }

            $overallTickets = QueueEntry::where('office_id', $this->office->id)
                ->whereBetween('created_at', [$dayStart, $dayEnd])
                ->orderByDesc('created_at')
                ->limit(20)
                ->get();

            if ($this->hrmoTab === 'queue-reports') {
                $dailyStartManila = $manilaNow->copy()->startOfDay()->subDays(6);
                $dailyEndManila = $manilaNow->copy()->endOfDay();

                $dailyEntries = QueueEntry::where('office_id', $this->office->id)
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

                $weeklyEntries = QueueEntry::where('office_id', $this->office->id)
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
                    'served' => QueueEntry::where('office_id', $this->office->id)
                        ->where('status', QueueEntry::STATUS_COMPLETED)
                        ->count(),
                    'skipped' => QueueEntry::where('office_id', $this->office->id)
                        ->where('status', QueueEntry::STATUS_NOT_SERVED)
                        ->count(),
                ];

                $processedEntries = QueueEntry::where('office_id', $this->office->id)
                    ->where('status', QueueEntry::STATUS_COMPLETED)
                    ->whereNotNull('called_at')
                    ->whereNotNull('served_at')
                    ->get(['called_at', 'served_at']);

                $processedCount = $processedEntries->count();
                $averageSeconds = 0;

                if ($processedCount > 0) {
                    $totalSeconds = $processedEntries->sum(function (QueueEntry $entry) {
                        return max(0, $entry->called_at->diffInSeconds($entry->served_at));
                    });

                    $averageSeconds = (int) round($totalSeconds / $processedCount);
                }

                $hours = intdiv($averageSeconds, 3600);
                $minutes = intdiv($averageSeconds % 3600, 60);
                $seconds = $averageSeconds % 60;
                $queueReportAverageProcessingTime = sprintf('%02dh %02dm %02ds', $hours, $minutes, $seconds);
            }
        } else {
            $this->hrmoTab = 'dashboard';
        }

        return view('livewire.office-admin.dashboard', [
            'waiting' => $waiting,
            'serving' => $serving,
            'summary' => $summary,
            'overallTickets' => $overallTickets,
            'statusBreakdown' => $statusBreakdown,
            'statusPieStyle' => $statusPieStyle,
            'statusPieHasData' => $statusPieHasData,
            'hourlyTicketSeries' => $hourlyTicketSeries,
            'hourlyMax' => $hourlyMax,
            'peakHourLabel' => $peakHourLabel,
            'monthlyVolumeSeries' => $monthlyVolumeSeries,
            'monthlyVolumeMax' => $monthlyVolumeMax,
            'monthlyPeakMonthLabel' => $monthlyPeakMonthLabel,
            'monthlyStatusSeries' => $monthlyStatusSeries,
            'monthlyStatusLegend' => $monthlyStatusLegend,
            'queueReportDailyCounts' => $queueReportDailyCounts,
            'queueReportWeeklyCounts' => $queueReportWeeklyCounts,
            'queueReportStatusSummary' => $queueReportStatusSummary,
            'queueReportAverageProcessingTime' => $queueReportAverageProcessingTime,
        ]);
    }
}
