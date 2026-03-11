<?php

namespace App\Livewire\QueueMaster;

use App\Models\Office;
use App\Models\QueueEntry;
use App\Models\User;
use Closure;
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

    private function activityWithinDayScope($dayStart, $dayEnd): Closure
    {
        return function ($query) use ($dayStart, $dayEnd) {
            $query->where(function ($activityQuery) use ($dayStart, $dayEnd) {
                $activityQuery->whereBetween('created_at', [$dayStart, $dayEnd])
                    ->orWhereBetween('called_at', [$dayStart, $dayEnd])
                    ->orWhereBetween('served_at', [$dayStart, $dayEnd]);
            });
        };
    }

    private function resolveRecentEntryActivityAt(QueueEntry $entry): mixed
    {
        return match ($entry->status) {
            QueueEntry::STATUS_COMPLETED, QueueEntry::STATUS_NOT_SERVED => $entry->served_at ?? $entry->created_at,
            QueueEntry::STATUS_SERVING => $entry->called_at ?? $entry->created_at,
            default => $entry->created_at,
        };
    }

    public function render()
    {
        $isSuperAdmin = $this->currentUser()?->isSuperAdmin() ?? false;
        [$dayStart, $dayEnd] = $this->manilaDayBounds();

        $officeQuery = Office::query();
        if ($isSuperAdmin) {
            $officeQuery->activePublicQueue();
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
            ->get();

        $offices = $isSuperAdmin
            ? Office::sortPublicQueueOffices($offices)
            : $offices->sortBy('name')->values();

        $recentEntriesQuery = QueueEntry::with('office')->whereHas('office');

        if ($isSuperAdmin) {
            $recentEntriesQuery
                ->where($this->activityWithinDayScope($dayStart, $dayEnd))
                ->orderByRaw('COALESCE(served_at, called_at, created_at) DESC')
                ->orderByDesc('id');
        } else {
            $recentEntriesQuery
                ->whereIn('status', [QueueEntry::STATUS_WAITING, QueueEntry::STATUS_SERVING])
                ->whereBetween('created_at', [$dayStart, $dayEnd])
                ->orderByDesc('created_at')
                ->orderByDesc('id');
        }

        $recentEntries = $recentEntriesQuery
            ->limit(20)
            ->get()
            ->each(function (QueueEntry $entry) {
                $entry->activityAt = $this->resolveRecentEntryActivityAt($entry);
            });

        return view('livewire.queue-master.dashboard', [
            'offices' => $offices,
            'recentEntries' => $recentEntries,
        ]);
    }
}
