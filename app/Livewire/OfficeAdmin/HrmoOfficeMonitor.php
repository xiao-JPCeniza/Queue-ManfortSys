<?php

namespace App\Livewire\OfficeAdmin;

use App\Models\Office;
use App\Models\QueueEntry;
use Livewire\Component;

class HrmoOfficeMonitor extends Component
{
    public Office $office;

    public function mount(Office $office): void
    {
        if ($office->slug !== 'hrmo') {
            abort(404, 'HRMO monitor is only available for the HRMO office.');
        }

        $this->office = $office;
    }

    public function tick(): void
    {
        $this->ensureCurrentServing();
    }

    private function ensureCurrentServing(): void
    {
        $serving = QueueEntry::where('office_id', $this->office->id)
            ->serving()
            ->orderBy('called_at')
            ->first();

        if (!$serving) {
            $next = QueueEntry::where('office_id', $this->office->id)
                ->waiting()
                ->orderBy('created_at')
                ->first();

            if ($next) {
                $next->update([
                    'status' => QueueEntry::STATUS_SERVING,
                    'called_at' => now(),
                    'served_by' => auth()->id(),
                ]);
            }
            return;
        }

        if (!$serving->called_at) {
            $serving->update(['called_at' => now()]);
        }
    }

    public function render()
    {
        $this->ensureCurrentServing();

        $serving = QueueEntry::where('office_id', $this->office->id)
            ->serving()
            ->orderBy('called_at')
            ->first();

        $nextInline = QueueEntry::where('office_id', $this->office->id)
            ->waiting()
            ->orderBy('created_at')
            ->first();

        $recentTransactions = QueueEntry::where('office_id', $this->office->id)
            ->whereIn('status', [QueueEntry::STATUS_COMPLETED, QueueEntry::STATUS_NOT_SERVED])
            ->whereNotNull('served_at')
            ->whereDate('served_at', today())
            ->orderByDesc('served_at')
            ->limit(20)
            ->get();

        $manilaNow = now('Asia/Manila');

        return view('livewire.office-admin.hrmo-office-manage', [
            'serving' => $serving,
            'nextInline' => $nextInline,
            'recentTransactions' => $recentTransactions,
            'manilaNow' => $manilaNow,
        ]);
    }
}

