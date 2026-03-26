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

    public ?string $selectedServiceKey = null;

    public function mount(string $office)
    {
        $this->office = Office::where('slug', $office)->where('is_active', true)->first();

        if (!$this->office) {
            abort(404, 'Office not found or inactive.');
        }
    }

    public function selectService(string $serviceKey): void
    {
        if (! $this->office?->queueServiceOption($serviceKey)) {
            return;
        }

        $this->selectedServiceKey = $serviceKey;
    }

    public function resetServiceSelection(): void
    {
        $this->selectedServiceKey = null;
    }

    public function joinQueue(string $clientType = QueueEntry::TYPE_REGULAR, ?string $serviceKey = null)
    {
        if (!$this->office) {
            return;
        }

        $normalizedClientType = QueueEntry::normalizeClientType($clientType);
        $resolvedServiceKey = $this->resolveServiceKey($serviceKey);

        if ($this->office->hasQueueServiceOptions() && $resolvedServiceKey === null) {
            return;
        }

        $queueNumber = $this->office->generateNextQueueNumber();

        $this->entry = QueueEntry::create([
            'office_id' => $this->office->id,
            'queue_number' => $queueNumber,
            'client_type' => $normalizedClientType,
            'service_key' => $resolvedServiceKey,
            'status' => QueueEntry::STATUS_WAITING,
        ]);

        $this->joined = true;
    }

    public function render()
    {
        return view('livewire.queue-join', [
            'queueServiceOptions' => $this->office?->queueServiceOptions() ?? [],
            'selectedQueueService' => $this->office?->queueServiceOption($this->selectedServiceKey),
            'priorityClientTypeOptions' => QueueEntry::priorityClientTypeOptions(),
        ]);
    }

    private function resolveServiceKey(?string $serviceKey): ?string
    {
        $serviceKey ??= $this->selectedServiceKey;

        return $this->office?->queueServiceOption($serviceKey) !== null
            ? $serviceKey
            : null;
    }
}
