<?php

namespace App\Livewire\SuperAdmin;

use App\Models\Office;
use App\Models\QueueEntry;
use Closure;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public function resetNumbering(int $officeId): void
    {
        $office = Office::query()->find($officeId);

        if (! $office) {
            return;
        }

        [$dayStart, $dayEnd] = $this->manilaDayBounds();

        QueueEntry::query()
            ->where('office_id', $office->id)
            ->whereBetween('created_at', [$dayStart, $dayEnd])
            ->delete();

        $office->update(['next_number' => 1]);
        $office->refresh();

        session()->flash('success', "Queue numbering reset for {$office->name}. The next generated number will start from 001.");
    }

    public function render()
    {
        [$dayStart, $dayEnd] = $this->manilaDayBounds();

        $offices = Office::query()
            ->activePublicQueue()
            ->withCount(['queueEntries as waiting_count' => function ($query) use ($dayStart, $dayEnd) {
                $query->where('status', QueueEntry::STATUS_WAITING)
                    ->whereBetween('created_at', [$dayStart, $dayEnd]);
            }])
            ->get();

        $offices = Office::sortPublicQueueOffices($offices);

        $waitingEntriesByOffice = QueueEntry::query()
            ->whereIn('office_id', $offices->pluck('id'))
            ->where('status', QueueEntry::STATUS_WAITING)
            ->whereBetween('created_at', [$dayStart, $dayEnd])
            ->orderBy('created_at')
            ->orderBy('id')
            ->get()
            ->groupBy('office_id');

        $latestCalledEntriesByOffice = QueueEntry::query()
            ->whereIn('office_id', $offices->pluck('id'))
            ->whereBetween('created_at', [$dayStart, $dayEnd])
            ->whereNotNull('called_at')
            ->orderByDesc('called_at')
            ->orderByDesc('id')
            ->get()
            ->groupBy('office_id')
            ->map(fn ($entries) => $entries->first());

        $servingEntriesByOffice = QueueEntry::query()
            ->whereIn('office_id', $offices->pluck('id'))
            ->where('status', QueueEntry::STATUS_SERVING)
            ->whereBetween('created_at', [$dayStart, $dayEnd])
            ->orderByRaw('COALESCE(service_window_number, 1)')
            ->orderBy('called_at')
            ->orderBy('id')
            ->get()
            ->map(function (QueueEntry $entry) {
                if ($entry->service_window_number === null) {
                    $entry->service_window_number = 1;
                }

                return $entry;
            })
            ->groupBy('office_id');

        $offices->each(function (Office $office) use ($servingEntriesByOffice, $waitingEntriesByOffice, $latestCalledEntriesByOffice): void {
            $servingEntries = $servingEntriesByOffice->get($office->id, collect())->values();
            $orderedWaitingEntries = QueueEntry::sortWaitingEntriesForService(
                $waitingEntriesByOffice->get($office->id, collect())->values(),
                $latestCalledEntriesByOffice->get($office->id)
            );
            $windowCount = $office->resolvedServiceWindowCount();

            $office->serving_entries = $servingEntries;
            $office->active_window_count = $servingEntries->count();
            $office->available_window_count = max(0, $windowCount - $office->active_window_count);
            $office->window_count = $windowCount;
            $office->next_waiting_ticket = $orderedWaitingEntries->first()?->queue_number;
            $office->serving_ticket = $servingEntries
                ->map(function (QueueEntry $entry) use ($office): string {
                    return sprintf(
                        '%s: %s',
                        $office->serviceWindowLabel($entry->service_window_number ?? 1),
                        $entry->queue_number
                    );
                })
                ->implode(' | ');
        });

        $recentEntries = QueueEntry::query()
            ->with('office')
            ->whereHas('office')
            ->where($this->activityWithinDayScope($dayStart, $dayEnd))
            ->orderByRaw('COALESCE(served_at, called_at, created_at) DESC')
            ->orderByDesc('id')
            ->limit(20)
            ->get()
            ->each(function (QueueEntry $entry): void {
                $entry->activityAt = $this->resolveRecentEntryActivityAt($entry);
            });

        return view('livewire.super-admin.dashboard', [
            'offices' => $offices,
            'recentEntries' => $recentEntries,
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

    private function activityWithinDayScope($dayStart, $dayEnd): Closure
    {
        return function ($query) use ($dayStart, $dayEnd): void {
            $query->where(function ($activityQuery) use ($dayStart, $dayEnd): void {
                $activityQuery->whereBetween('created_at', [$dayStart, $dayEnd])
                    ->orWhereBetween('called_at', [$dayStart, $dayEnd])
                    ->orWhereBetween('served_at', [$dayStart, $dayEnd]);
            });
        };
    }

    private function resolveRecentEntryActivityAt(QueueEntry $entry): mixed
    {
        return match ($entry->status) {
            QueueEntry::STATUS_COMPLETED, QueueEntry::STATUS_NOT_SERVED => $entry->served_at ?? $entry->created_at,
            QueueEntry::STATUS_SERVING => $entry->called_at ?? $entry->created_at,
            default => $entry->created_at,
        };
    }
}
