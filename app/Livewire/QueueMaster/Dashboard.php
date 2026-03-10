<?php

namespace App\Livewire\QueueMaster;

use App\Models\Office;
use App\Models\QueueEntry;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
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

    private function currentUser(): ?User
    {
        $user = Auth::user();

        return $user instanceof User ? $user : null;
    }

    public function render()
    {
        $isSuperAdmin = $this->currentUser()?->isSuperAdmin() ?? false;
        [$dayStart, $dayEnd] = $this->manilaDayBounds();

        $officeQuery = Office::query();
        if ($isSuperAdmin) {
            $officeQuery->whereIn('slug', Office::MUNICIPALITY_QUEUE_SERVICE_SLUGS);
        }

        $offices = $officeQuery
            ->addSelect([
                'serving_ticket' => QueueEntry::query()
                    ->select('queue_number')
                    ->whereColumn('office_id', 'offices.id')
                    ->where('status', QueueEntry::STATUS_SERVING)
                    ->whereBetween('created_at', [$dayStart, $dayEnd])
                    ->orderBy('called_at')
                    ->orderBy('id')
                    ->limit(1),
                'next_waiting_ticket' => QueueEntry::query()
                    ->select('queue_number')
                    ->whereColumn('office_id', 'offices.id')
                    ->where('status', QueueEntry::STATUS_WAITING)
                    ->whereBetween('created_at', [$dayStart, $dayEnd])
                    ->orderBy('created_at')
                    ->orderBy('id')
                    ->limit(1),
            ])
            ->withCount(['queueEntries as waiting_count' => function ($q) use ($dayStart, $dayEnd) {
                $q->where('status', QueueEntry::STATUS_WAITING)
                    ->whereBetween('created_at', [$dayStart, $dayEnd]);
            }])
            ->orderBy('name')
            ->get();

        $recentEntriesQuery = QueueEntry::with('office')
            ->whereIn('status', [QueueEntry::STATUS_WAITING, QueueEntry::STATUS_SERVING])
            ->whereBetween('created_at', [$dayStart, $dayEnd])
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        if ($isSuperAdmin) {
            $recentEntriesQuery->whereIn('office_id', $offices->pluck('id'));
        }

        $recentEntries = $recentEntriesQuery
            ->limit(20)
            ->get();

        return view('livewire.queue-master.dashboard', [
            'offices' => $offices,
            'recentEntries' => $recentEntries,
        ]);
    }
}
