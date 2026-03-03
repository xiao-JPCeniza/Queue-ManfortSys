<?php

namespace App\Livewire\OfficeAdmin;

use App\Models\Office;
use App\Models\QueueEntry;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public Office $office;

    public function mount(Office $office)
    {
        $this->office = $office;
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

        return view('livewire.office-admin.dashboard', [
            'waiting' => $waiting,
            'serving' => $serving,
        ]);
    }
}
