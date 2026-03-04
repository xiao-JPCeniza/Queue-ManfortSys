<?php

namespace App\Livewire;

use App\Models\Office;
use App\Models\QueueEntry;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.public')]
class ClientDashboard extends Component
{
    /** @var array{office_id: int, office_name: string, queue_number: string}|null */
    public ?array $ticket = null;

    public function getOffices()
    {
        return Office::where('is_active', true)->orderBy('name')->get();
    }

    public function selectOffice(int $officeId): void
    {
        $office = Office::where('id', $officeId)->where('is_active', true)->first();

        if (! $office) {
            return;
        }

        $queueNumber = $office->generateNextQueueNumber();

        $entry = QueueEntry::create([
            'office_id' => $office->id,
            'queue_number' => $queueNumber,
            'status' => QueueEntry::STATUS_WAITING,
        ]);

        $this->ticket = [
            'office_id' => $office->id,
            'office_name' => $office->name,
            'queue_number' => $entry->queue_number,
            'prefix' => $office->prefix,
        ];

        $this->dispatch('ticket-issued', queueNumber: $entry->queue_number, officeName: $office->name);
    }

    public function render()
    {
        return view('livewire.client-dashboard', [
            'offices' => $this->getOffices(),
        ]);
    }
}
