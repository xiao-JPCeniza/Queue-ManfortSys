<?php

namespace App\Livewire\OfficeAdmin;

use App\Livewire\OfficeAdmin\Concerns\HandlesOfficeQueueAnnouncements;
use App\Models\Office;
use App\Models\QueueEntry;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class WindowDesk extends Component
{
    use HandlesOfficeQueueAnnouncements;

    public Office $office;

    public int $windowNumber;

    public function mount(Office $office, int $windowNumber): void
    {
        abort_if(
            $windowNumber < 1 || $windowNumber > $office->accessibleServiceWindowCount(),
            404,
            'Service window not found.'
        );

        $this->office = $office;
        $this->windowNumber = $windowNumber;
    }

    public function callNext(): void
    {
        if ($this->windowNumber > $this->office->resolvedServiceWindowCount()) {
            session()->flash('office_message', 'This service window is no longer available for new tickets.');

            return;
        }

        $currentServing = $this->servingEntryQuery()->first();

        if ($currentServing !== null) {
            session()->flash(
                'office_message',
                $this->office->serviceWindowLabel($this->windowNumber).' is still handling an active ticket.'
            );

            return;
        }

        $next = $this->orderedWaitingEntries()->first();

        if (! $next) {
            session()->flash('office_message', 'No one waiting in queue.');

            return;
        }

        $next->update([
            'status' => QueueEntry::STATUS_SERVING,
            'service_window_number' => $this->windowNumber,
            'called_at' => now(),
            'served_by' => Auth::id(),
        ]);

        $this->storeOfficeAnnouncement($this->office, 'serving', $next->queue_number, $this->windowNumber);

        session()->flash(
            'office_message',
            sprintf('Now serving %s at %s.', $next->queue_number, $this->office->serviceWindowAnnouncementLabel($this->windowNumber))
        );
    }

    public function complete(int $entryId): void
    {
        $entry = $this->servingEntryQuery()
            ->whereKey($entryId)
            ->first();

        if ($entry === null) {
            return;
        }

        $entry->update([
            'status' => QueueEntry::STATUS_COMPLETED,
            'served_at' => now(),
            'served_by' => Auth::id(),
        ]);
    }

    public function render()
    {
        $this->office->refresh();

        $windowEntry = $this->servingEntryQuery()->first()?->setRelation('office', $this->office);
        $waiting = $this->orderedWaitingEntries();
        $windowLabel = $windowEntry?->service_window_label
            ?? $this->office->serviceWindowLabel($this->windowNumber);
        $windowDisplayTitle = $windowEntry?->service_label
            ?? $windowLabel;

        return view('livewire.office-admin.window-desk', [
            'windowEntry' => $windowEntry,
            'waiting' => $waiting,
            'windowLabel' => $windowLabel,
            'windowDisplayTitle' => $windowDisplayTitle,
        ]);
    }

    private function todayOfficeQueueEntries(): HasMany
    {
        [$dayStart, $dayEnd] = $this->manilaDayBounds();

        return $this->office->queueEntries()
            ->whereBetween('created_at', [$dayStart, $dayEnd]);
    }

    private function servingEntryQuery(): HasMany
    {
        return $this->todayOfficeQueueEntries()
            ->serving()
            ->where(function ($query) {
                $query->where('service_window_number', $this->windowNumber);

                if ($this->windowNumber === 1) {
                    $query->orWhereNull('service_window_number');
                }
            })
            ->orderBy('called_at')
            ->orderBy('id');
    }

    private function orderedWaitingEntries(): Collection
    {
        $waitingQuery = $this->todayOfficeQueueEntries()
            ->waiting()
            ->orderBy('created_at')
            ->orderBy('id');

        $this->applyQueueRoutingFilter($waitingQuery);

        $waitingEntries = $waitingQuery
            ->get()
            ->map(function (QueueEntry $entry) {
                $entry->setRelation('office', $this->office);

                return $entry;
            });

        return QueueEntry::sortWaitingEntriesForService(
            $waitingEntries,
            $this->latestCalledOfficeEntry()
        );
    }

    private function latestCalledOfficeEntry(): ?QueueEntry
    {
        $query = $this->todayOfficeQueueEntries()
            ->whereNotNull('called_at');

        $this->applyQueueRoutingFilter($query);

        return $query
            ->orderByDesc('called_at')
            ->orderByDesc('id')
            ->first()?->setRelation('office', $this->office);
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

    private function applyQueueRoutingFilter($query): void
    {
        if (! $this->office->hasQueueServiceOptions()) {
            return;
        }

        $serviceKeys = $this->office->queueServiceKeysForWindow($this->windowNumber);

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
}
