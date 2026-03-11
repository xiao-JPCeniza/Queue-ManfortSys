<?php

namespace App\Livewire;

use App\Models\Office;
use App\Models\QueueEntry;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.public')]
class ClientDashboard extends Component
{
    /** @var array{entry_id: int, office_id: int, office_name: string, queue_number: string, prefix: string|null, issued_date: string, issued_time: string}|null */
    public ?array $ticket = null;
    public string $selectedOfficeSlug = '';

    public function getOfficeOptions()
    {
        return Office::sortPublicQueueOffices(
            Office::query()
                ->activePublicQueue()
                ->get()
        );
    }

    public function getOffices()
    {
        $query = Office::query()->activePublicQueue();

        if ($this->selectedOfficeSlug !== '') {
            $query->where('slug', $this->selectedOfficeSlug);
        }

        return Office::sortPublicQueueOffices($query->get());
    }

    public function selectOffice(int $officeId): void
    {
        $office = Office::query()
            ->activePublicQueue()
            ->where('id', $officeId)
            ->first();

        if (! $office) {
            return;
        }

        $queueNumber = $office->generateNextQueueNumber();

        $entry = QueueEntry::create([
            'office_id' => $office->id,
            'queue_number' => $queueNumber,
            'status' => QueueEntry::STATUS_WAITING,
        ]);

        $issuedAt = ($entry->created_at ?? now())->copy()->timezone('Asia/Manila');

        $this->ticket = [
            'entry_id' => $entry->id,
            'office_id' => $office->id,
            'office_name' => $office->name,
            'queue_number' => $entry->queue_number,
            'prefix' => $office->prefix,
            'issued_date' => $issuedAt->format('F j, Y'),
            'issued_time' => $issuedAt->format('g:i A'),
        ];
    }

    public function render()
    {
        return view('livewire.client-dashboard', [
            'offices' => $this->getOffices(),
            'officeOptions' => $this->getOfficeOptions(),
        ]);
    }
}
