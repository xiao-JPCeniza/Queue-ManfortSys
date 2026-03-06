<?php

namespace App\Livewire\OfficeAdmin;

use App\Models\Office;
use App\Models\QueueEntry;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class HrmoOfficeMonitor extends Component
{
    public Office $office;

    public function mount(Office $office): void
    {
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

        $updatedCount = QueueEntry::where('office_id', $this->office->id)
            ->whereIn('status', [QueueEntry::STATUS_COMPLETED, QueueEntry::STATUS_NOT_SERVED])
            ->whereBetween('served_at', [$dayStart, $dayEnd])
            ->whereNull('recent_transaction_cleared_at')
            ->update(['recent_transaction_cleared_at' => now()]);

        session()->flash(
            'office_message',
            $updatedCount > 0
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
                $updatePayload = [
                    'status' => QueueEntry::STATUS_SERVING,
                    'called_at' => now(),
                ];

                $servedBy = Auth::id();
                if ($servedBy !== null) {
                    $updatePayload['served_by'] = $servedBy;
                }

                $next->update($updatePayload);
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
            ->whereNull('recent_transaction_cleared_at')
            ->orderByDesc('served_at')
            ->limit(20)
            ->get()
            ->sortBy('served_at')
            ->values();

        $manilaNow = now('Asia/Manila');

        $view = match (true) {
            $this->office->slug === 'hrmo' => 'livewire.office-admin.hrmo-office-manage',
            in_array($this->office->slug, ['business-permits', 'bplo'], true) => 'livewire.office-admin.bplo-office-manage',
            default => 'livewire.office-admin.general-office-live-monitor',
        };

        return view($view, [
            'serving' => $serving,
            'nextInline' => $nextInline,
            'recentTransactions' => $recentTransactions,
            'manilaNow' => $manilaNow,
        ]);
    }
}
