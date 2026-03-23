<?php

namespace App\Livewire\OfficeAdmin;

use App\Livewire\OfficeAdmin\Concerns\HandlesOfficeQueueAnnouncements;
use App\Models\Office;
use App\Models\QueueEntry;
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

    private function latestCalledOfficeEntry(): ?QueueEntry
    {
        return $this->todayOfficeQueueEntries()
            ->whereNotNull('called_at')
            ->orderByDesc('called_at')
            ->orderByDesc('id')
            ->first();
    }

    public function render()
    {
        $servingEntries = $this->todayOfficeQueueEntries()
            ->serving()
            ->orderByRaw('COALESCE(called_at, created_at) DESC')
            ->orderByDesc('id')
            ->get()
            ->map(function (QueueEntry $entry) {
                if ($entry->service_window_number === null) {
                    $entry->service_window_number = 1;
                }

                return $entry;
            });

        $waitingEntries = $this->todayOfficeQueueEntries()
            ->waiting()
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();

        $nextInline = QueueEntry::sortWaitingEntriesForService(
            $waitingEntries,
            $this->latestCalledOfficeEntry()
        )->first();

        $manilaNow = now('Asia/Manila');

        $view = match (true) {
            $this->office->slug === 'hrmo' => 'livewire.office-admin.hrmo-office-manage',
            default => 'livewire.office-admin.general-office-live-monitor',
        };

        return view($view, [
            'serving' => $servingEntries->first(),
            'servingEntries' => $servingEntries,
            'nextInline' => $nextInline,
            'manilaNow' => $manilaNow,
            'announcementPayload' => $this->getOfficeAnnouncement($this->office),
        ]);
    }
}
