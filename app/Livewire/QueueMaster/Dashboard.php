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
            [$dayStart, $dayEnd] = $this->manilaDayBounds();

            QueueEntry::where('office_id', $office->id)
                ->whereBetween('created_at', [$dayStart, $dayEnd])
                ->delete();

            $office->update(['next_number' => 1]);
            $office->refresh();

            session()->flash('success', "Queue numbering reset for {$office->name}. The next generated number will start from 001.");
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
