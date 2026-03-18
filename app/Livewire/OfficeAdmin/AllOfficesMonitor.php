<?php

namespace App\Livewire\OfficeAdmin;

use App\Livewire\OfficeAdmin\Concerns\HandlesOfficeQueueAnnouncements;
use App\Models\QueueEntry;
use Illuminate\Support\Collection;
use Livewire\Component;

class AllOfficesMonitor extends Component
{
    use HandlesOfficeQueueAnnouncements;

    public function tick(): void
    {
    }

    public function render()
    {
        [$dayStart, $dayEnd] = $this->manilaDayBounds();
        $todayRecentTransactions = $this->todayRecentTransactions($dayStart, $dayEnd);
        $recentTransactionsByOffice = $todayRecentTransactions
            ->groupBy('office_id')
            ->map(fn (Collection $entries) => $entries
                ->take(7)
                ->sortBy('served_at')
                ->values());

        $todayEntries = QueueEntry::query()
            ->with('office:id,name,slug,prefix')
            ->whereBetween('created_at', [$dayStart, $dayEnd])
            ->orderBy('created_at')
            ->orderBy('id')
            ->get()
            ->filter(fn (QueueEntry $entry) => $entry->office !== null);

        $officeRows = $todayEntries
            ->groupBy('office_id')
            ->map(fn (Collection $officeEntries) => $this->buildOfficeRow($officeEntries))
            ->filter()
            ->map(function (array $row) use ($recentTransactionsByOffice) {
                $row['recentTransactions'] = $recentTransactionsByOffice->get($row['office']->id, collect());

                return $row;
            })
            ->sort(fn (array $left, array $right) => $this->compareOfficeRows($left, $right))
            ->values();

        $featuredOfficeRow = $officeRows->first();

        return view('livewire.office-admin.all-offices-monitor', [
            'featuredOfficeRow' => $featuredOfficeRow,
            'announcementOfficeRows' => $officeRows,
            'manilaNow' => now('Asia/Manila'),
        ]);
    }

    private function buildOfficeRow(Collection $officeEntries): ?array
    {
        $office = $officeEntries->first()?->office;

        if ($office === null) {
            return null;
        }

        $servingEntries = $officeEntries
            ->where('status', QueueEntry::STATUS_SERVING)
            ->map(function (QueueEntry $entry) {
                if ($entry->service_window_number === null) {
                    $entry->service_window_number = 1;
                }

                return $entry;
            })
            ->sortBy(fn (QueueEntry $entry) => sprintf(
                '%05d-%020d-%010d',
                $entry->service_window_number ?? 1,
                $entry->called_at?->getTimestamp() ?? 0,
                $entry->id
            ))
            ->values();

        $waitingEntries = $officeEntries
            ->where('status', QueueEntry::STATUS_WAITING)
            ->sortBy(fn (QueueEntry $entry) => $entry->serviceOrderKey())
            ->values();

        $nextInline = $waitingEntries->first();

        if ($servingEntries->isEmpty() && $nextInline === null) {
            return null;
        }

        $announcementPayload = $this->getOfficeAnnouncement($office);
        $activeWindowCount = $servingEntries->count();
        $windowCount = $office->resolvedServiceWindowCount();

        return [
            'office' => $office,
            'serving' => $servingEntries->first(),
            'servingEntries' => $servingEntries,
            'nextInline' => $nextInline,
            'waiting_count' => $waitingEntries->count(),
            'active_window_count' => $activeWindowCount,
            'window_count' => $windowCount,
            'announcementPayload' => $announcementPayload,
            'priority_timestamp' => max(
                $this->announcementTimestamp($announcementPayload),
                $servingEntries->max(fn (QueueEntry $entry) => $entry->called_at?->getTimestamp() ?? 0) ?? 0
            ),
        ];
    }

    private function compareOfficeRows(array $left, array $right): int
    {
        $priorityComparison = ($right['priority_timestamp'] ?? 0) <=> ($left['priority_timestamp'] ?? 0);

        if ($priorityComparison !== 0) {
            return $priorityComparison;
        }

        return strcasecmp((string) $left['office']->name, (string) $right['office']->name);
    }

    private function announcementTimestamp(?array $announcementPayload): int
    {
        $triggeredAt = $announcementPayload['triggered_at'] ?? null;

        if (! is_string($triggeredAt) || $triggeredAt === '') {
            return 0;
        }

        return strtotime($triggeredAt) ?: 0;
    }

    private function todayRecentTransactions($dayStart, $dayEnd): Collection
    {
        return QueueEntry::query()
            ->with('office:id,name,slug,prefix')
            ->whereIn('status', [QueueEntry::STATUS_COMPLETED, QueueEntry::STATUS_NOT_SERVED])
            ->whereNotNull('served_at')
            ->whereBetween('served_at', [$dayStart, $dayEnd])
            ->whereNull('recent_transaction_cleared_at')
            ->orderByDesc('served_at')
            ->get();
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
}
