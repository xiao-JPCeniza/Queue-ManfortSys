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
        $todayEntries = QueueEntry::query()
            ->with('office:id,name,slug,prefix,service_window_count,service_window_labels')
            ->whereBetween('created_at', [$dayStart, $dayEnd])
            ->orderBy('created_at')
            ->orderBy('id')
            ->get()
            ->filter(fn (QueueEntry $entry) => $entry->office !== null);

        $officeRows = $todayEntries
            ->groupBy('office_id')
            ->map(fn (Collection $officeEntries) => $this->buildOfficeRow($officeEntries))
            ->filter()
            ->sort(fn (array $left, array $right) => $this->compareOfficeRows($left, $right))
            ->values();

        $hasCurrentTransaction = $officeRows->contains(fn (array $row) => $row['serving'] !== null);
        $hasQueuedNextInline = $officeRows->contains(fn (array $row) => $row['nextInline'] !== null);
        $featuredOfficeRow = $hasCurrentTransaction
            ? $officeRows->first(fn (array $row) => $row['serving'] !== null)
            : $this->selectUpcomingOfficeRow($officeRows);
        $featuredNextInlineRow = $this->selectUpcomingOfficeRow($officeRows);

        return view('livewire.office-admin.all-offices-monitor', [
            'featuredOfficeRow' => $featuredOfficeRow,
            'featuredNextInlineRow' => $featuredNextInlineRow,
            'announcementOfficeRows' => $officeRows,
            'hasCurrentTransaction' => $hasCurrentTransaction,
            'hasQueuedNextInline' => $hasQueuedNextInline,
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
            ->sortByDesc(fn (QueueEntry $entry) => sprintf(
                '%020d-%010d',
                $entry->called_at?->getTimestamp() ?? $entry->created_at?->getTimestamp() ?? 0,
                $entry->id
            ))
            ->values();

        $lastCalledEntry = $officeEntries
            ->filter(fn (QueueEntry $entry) => $entry->called_at !== null)
            ->sortByDesc(fn (QueueEntry $entry) => sprintf(
                '%020d-%010d',
                $entry->called_at?->getTimestamp() ?? 0,
                $entry->id
            ))
            ->first();

        $waitingEntries = QueueEntry::sortWaitingEntriesForService(
            $officeEntries
                ->where('status', QueueEntry::STATUS_WAITING)
                ->values(),
            $lastCalledEntry
        );

        $nextInline = $waitingEntries->first();

        if ($servingEntries->isEmpty() && $nextInline === null) {
            return null;
        }

        $announcementPayload = $this->getOfficeAnnouncement($office);
        $activeWindowCount = $servingEntries->count();
        $windowCount = max(
            $office->resolvedServiceWindowCount(),
            max(1, (int) ($servingEntries->max(fn (QueueEntry $entry) => $entry->service_window_number ?? 1) ?? 1))
        );

        return [
            'office' => $office,
            'serving' => $servingEntries->first(),
            'servingEntries' => $servingEntries,
            'nextInline' => $nextInline,
            'next_inline_timestamp' => $nextInline?->displayCreatedAt()?->getTimestamp() ?? 0,
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

    private function selectUpcomingOfficeRow(Collection $officeRows): ?array
    {
        return $officeRows
            ->filter(fn (array $row) => $row['nextInline'] !== null)
            ->sort(function (array $left, array $right) {
                $nextInlineComparison = ($right['next_inline_timestamp'] ?? 0) <=> ($left['next_inline_timestamp'] ?? 0);

                if ($nextInlineComparison !== 0) {
                    return $nextInlineComparison;
                }

                return strcasecmp((string) $left['office']->name, (string) $right['office']->name);
            })
            ->first();
    }

    private function announcementTimestamp(?array $announcementPayload): int
    {
        $triggeredAt = $announcementPayload['triggered_at'] ?? null;

        if (! is_string($triggeredAt) || $triggeredAt === '') {
            return 0;
        }

        return strtotime($triggeredAt) ?: 0;
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
