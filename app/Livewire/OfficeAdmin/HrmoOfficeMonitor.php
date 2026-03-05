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
        [$dayStart, $dayEnd] = $this->manilaDayBounds();

        $deletedCount = QueueEntry::where('office_id', $this->office->id)
            ->whereIn('status', [QueueEntry::STATUS_COMPLETED, QueueEntry::STATUS_NOT_SERVED])
            ->whereBetween('served_at', [$dayStart, $dayEnd])
            ->delete();

        session()->flash(
            'office_message',
            $deletedCount > 0
                ? 'Recent transactions for today were cleared.'
                : 'No recent transactions found for today.'
        );
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

    private function manilaDayBounds(): array
    {
        $manilaNow = now('Asia/Manila');
        $dbTimezone = (string) config('app.timezone', 'UTC');

        return [
            $manilaNow->copy()->startOfDay()->setTimezone($dbTimezone),
            $manilaNow->copy()->endOfDay()->setTimezone($dbTimezone),
        ];
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
            ->whereBetween('served_at', $this->manilaDayBounds())
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

