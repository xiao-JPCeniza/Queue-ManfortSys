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
    public string $hrmoTab = 'dashboard';

    public function mount(Office $office): void
    {
        $this->office = $office;

        $requestedTab = (string) request()->query('tab', 'dashboard');
        $allowedTabs = ['dashboard', 'reports', 'queue-management'];

        if ($this->office->slug === 'hrmo' && in_array($requestedTab, $allowedTabs, true)) {
            $this->hrmoTab = $requestedTab;
        }
    }

    public function setHrmoTab(string $tab): void
    {
        if ($this->office->slug !== 'hrmo') {
            return;
        }

        $allowedTabs = ['dashboard', 'reports', 'queue-management'];
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

        $summary = null;
        $overallTickets = collect();
        if ($this->office->slug === 'hrmo') {
            $summary = [
                'total_today' => QueueEntry::where('office_id', $this->office->id)
                    ->whereDate('created_at', today())
                    ->count(),
                'completed_today' => QueueEntry::where('office_id', $this->office->id)
                    ->whereDate('created_at', today())
                    ->completed()
                    ->count(),
                'active_now' => QueueEntry::where('office_id', $this->office->id)
                    ->whereIn('status', [QueueEntry::STATUS_WAITING, QueueEntry::STATUS_SERVING])
                    ->count(),
            ];

            $overallTickets = QueueEntry::where('office_id', $this->office->id)
                ->whereDate('created_at', today())
                ->orderByDesc('created_at')
                ->limit(20)
                ->get();
        } else {
            $this->hrmoTab = 'dashboard';
        }

        return view('livewire.office-admin.dashboard', [
            'waiting' => $waiting,
            'serving' => $serving,
            'summary' => $summary,
            'overallTickets' => $overallTickets,
        ]);
    }
}
