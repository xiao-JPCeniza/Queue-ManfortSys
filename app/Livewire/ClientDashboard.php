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
        $orderMap = array_flip(Office::MUNICIPALITY_QUEUE_SERVICE_SLUGS);

        return Office::where('is_active', true)
            ->whereIn('slug', Office::MUNICIPALITY_QUEUE_SERVICE_SLUGS)
            ->get()
            ->sortBy(fn (Office $office) => $orderMap[$office->slug] ?? PHP_INT_MAX)
            ->values();
    }

    public function getOffices()
    {
        $orderMap = array_flip(Office::MUNICIPALITY_QUEUE_SERVICE_SLUGS);

        $query = Office::where('is_active', true)
            ->whereIn('slug', Office::MUNICIPALITY_QUEUE_SERVICE_SLUGS);

        if ($this->selectedOfficeSlug !== '') {
            $query->where('slug', $this->selectedOfficeSlug);
        }

        return $query
            ->get()
            ->sortBy(fn (Office $office) => $orderMap[$office->slug] ?? PHP_INT_MAX)
            ->values();
    }

    public function selectOffice(int $officeId): void
    {
        $office = Office::where('id', $officeId)
            ->where('is_active', true)
            ->whereIn('slug', Office::MUNICIPALITY_QUEUE_SERVICE_SLUGS)
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

        $office->increment('tickets_accommodated_total');
        $office->refresh();

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
