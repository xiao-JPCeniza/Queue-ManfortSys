<?php

namespace App\Livewire\OfficeAdmin;

use App\Livewire\OfficeAdmin\Concerns\HandlesOfficeQueueAnnouncements;
use App\Models\Office;
use App\Models\QueueEntry;
use Illuminate\Support\Collection;
use Livewire\Component;

class HrmoOfficeMonitor extends Component
{
    use HandlesOfficeQueueAnnouncements;

    public Office $office;

    public function mount(Office $office): void
    {
        $this->office = $office;
    }

    public function tick(): void
    {
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

        return QueueEntry::where('office_id', $this->office->id)
            ->whereBetween('created_at', [$dayStart, $dayEnd]);
    }

    public function render()
    {
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

                return $entry;
            });

        $nextInline = $this->todayOfficeQueueEntries()
            ->waiting()
            ->orderedForService()
            ->first();

        $recentTransactions = QueueEntry::where('office_id', $this->office->id)
            ->whereIn('status', [QueueEntry::STATUS_COMPLETED, QueueEntry::STATUS_NOT_SERVED])
            ->whereNotNull('served_at')
            ->whereBetween('served_at', $this->manilaDayBounds())
            ->whereNull('recent_transaction_cleared_at')
            ->orderByDesc('served_at')
            ->limit(20)
            ->get()
            ->sortBy('served_at')
            ->values();

        $manilaNow = now('Asia/Manila');

        $view = match (true) {
            $this->office->slug === 'hrmo' => 'livewire.office-admin.hrmo-office-manage',
            default => 'livewire.office-admin.general-office-live-monitor',
        };

        return view($view, [
            'serving' => $servingEntries->first(),
            'servingEntries' => $servingEntries,
            'serviceWindows' => $this->buildServiceWindows($servingEntries),
            'usesMultipleServiceWindows' => $this->office->usesMultipleServiceWindows(),
            'nextInline' => $nextInline,
            'recentTransactions' => $recentTransactions,
            'manilaNow' => $manilaNow,
            'announcementPayload' => $this->getOfficeAnnouncement($this->office),
        ]);
    }

    private function buildServiceWindows(Collection $servingEntries): Collection
    {
        $servingByWindow = $servingEntries->keyBy(fn (QueueEntry $entry) => $entry->service_window_number ?? 1);

        return $this->office->serviceWindowNumbers()
            ->map(function (int $windowNumber) use ($servingByWindow) {
                return [
                    'number' => $windowNumber,
                    'label' => $this->office->serviceWindowLabel($windowNumber),
                    'entry' => $servingByWindow->get($windowNumber),
                ];
            })
            ->values();
    }
}
