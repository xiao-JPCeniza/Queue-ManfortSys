<?php

namespace App\Livewire\OfficeAdmin;

use App\Models\Office;
use App\Models\QueueEntry;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class HrmoOfficeMonitor extends Component
{
    public Office $office;
    public int $timeoutSeconds = 60;

    public function mount(Office $office): void
    {
        if ($office->slug !== 'hrmo') {
            abort(404, 'HRMO monitor is only available for the HRMO office.');
        }

        $this->office = $office;
    }

    public function tick(): void
    {
        $this->autoAdvanceTimedOutTicket();
    }

    private function autoAdvanceTimedOutTicket(): void
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
            return;
        }

        if ($serving->called_at->diffInSeconds(now()) < $this->timeoutSeconds) {
            return;
        }

        DB::transaction(function () use ($serving): void {
            $serving->refresh();

            if ($serving->status !== QueueEntry::STATUS_SERVING || !$serving->called_at) {
                return;
            }

            if ($serving->called_at->diffInSeconds(now()) < $this->timeoutSeconds) {
                return;
            }

            $serving->update([
                'status' => QueueEntry::STATUS_COMPLETED,
                'served_at' => now(),
            ]);

            $next = QueueEntry::where('office_id', $this->office->id)
                ->waiting()
                ->orderBy('created_at')
                ->first();

            if ($next) {
                $next->update([
                    'status' => QueueEntry::STATUS_SERVING,
                    'called_at' => now(),
                    'served_by' => $serving->served_by ?? auth()->id(),
                ]);
            }
        });
    }

    public function render()
    {
        $this->autoAdvanceTimedOutTicket();

        $serving = QueueEntry::where('office_id', $this->office->id)
            ->serving()
            ->orderBy('called_at')
            ->first();

        $nextInline = QueueEntry::where('office_id', $this->office->id)
            ->waiting()
            ->orderBy('created_at')
            ->first();

        $recentlyCalled = QueueEntry::where('office_id', $this->office->id)
            ->completed()
            ->whereNotNull('served_at')
            ->whereNotNull('called_at')
            ->whereRaw('TIMESTAMPDIFF(SECOND, called_at, served_at) >= ?', [$this->timeoutSeconds])
            ->orderByDesc('served_at')
            ->limit(8)
            ->get();

        $overallTickets = QueueEntry::where('office_id', $this->office->id)
            ->whereDate('created_at', today())
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        $secondsLeft = null;
        if ($serving && $serving->called_at) {
            $elapsed = $serving->called_at->diffInSeconds(now());
            $secondsLeft = max(0, $this->timeoutSeconds - $elapsed);
        }

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

        return view('livewire.office-admin.hrmo-office-manage', [
            'serving' => $serving,
            'nextInline' => $nextInline,
            'recentlyCalled' => $recentlyCalled,
            'overallTickets' => $overallTickets,
            'secondsLeft' => $secondsLeft,
            'summary' => $summary,
        ]);
    }
}
