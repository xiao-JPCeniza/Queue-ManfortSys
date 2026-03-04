<?php

namespace App\Livewire\OfficeAdmin;

use App\Models\AuditLog;
use App\Models\Office;
use App\Models\QueueEntry;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Dashboard extends Component
{
    public Office $office;
    public ?string $windowNumber = null;

    public function mount(Office $office)
    {
        $this->office = $office;
        $this->windowNumber = auth()->user()?->window_number;
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

    public function saveWindowNumber(): void
    {
        if (! auth()->user()?->isOfficeAdmin()) {
            abort(403, 'Only office admins can set a window number.');
        }

        $this->validate([
            'windowNumber' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('users', 'window_number')
                    ->where('office_id', auth()->user()->office_id)
                    ->ignore(auth()->id()),
            ],
        ], [
            'windowNumber.unique' => 'This window number is already in use for your office.',
        ]);

        $value = trim((string) $this->windowNumber);
        $value = $value === '' ? null : $value;

        $user = auth()->user();
        $old = $user->window_number;
        $user->update(['window_number' => $value]);

        AuditLog::log('window_number_updated', $user::class, $user->id, [
            'window_number' => $old,
        ], [
            'window_number' => $value,
        ]);

        $this->windowNumber = $value;
        session()->flash('office_message', 'Window number updated.');
    }

    public function render()
    {
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
