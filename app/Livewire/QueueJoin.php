<?php

namespace App\Livewire;

use App\Models\Office;
use App\Models\QueueEntry;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.public')]
class QueueJoin extends Component
{
    public ?Office $office = null;

    public ?QueueEntry $entry = null;

    public bool $joined = false;

    public function mount(string $office)
    {
        $this->office = Office::where('slug', $office)->where('is_active', true)->first();

        if (!$this->office) {
            abort(404, 'Office not found or inactive.');
        }
    }

    public function joinQueue(string $clientType = QueueEntry::TYPE_REGULAR)
    {
        if (!$this->office) {
            return;
        }

        $normalizedClientType = QueueEntry::normalizeClientType($clientType);
        $queueNumber = $this->office->generateNextQueueNumber();

        $this->entry = QueueEntry::create([
            'office_id' => $this->office->id,
            'queue_number' => $queueNumber,
            'client_type' => $normalizedClientType,
            'status' => QueueEntry::STATUS_WAITING,
        ]);

        $this->joined = true;
    }

    public function render()
    {
        return view('livewire.queue-join');
    }
}
