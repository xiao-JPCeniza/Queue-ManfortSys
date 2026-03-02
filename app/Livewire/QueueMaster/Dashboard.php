<?php

namespace App\Livewire\QueueMaster;

use App\Models\Office;
use App\Models\QueueEntry;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public function resetNumbering(int $officeId)
    {
        $office = Office::find($officeId);
        if ($office) {
            $office->update(['next_number' => 1]);
            $this->dispatch('numbering-reset');
        }
    }

    public function render()
    {
        $offices = Office::withCount(['queueEntries as waiting_count' => function ($q) {
            $q->where('status', QueueEntry::STATUS_WAITING);
        }])->orderBy('name')->get();

        $recentEntries = QueueEntry::with('office')
            ->whereIn('status', [QueueEntry::STATUS_WAITING, QueueEntry::STATUS_SERVING])
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return view('livewire.queue-master.dashboard', [
            'offices' => $offices,
            'recentEntries' => $recentEntries,
        ]);
    }
}
