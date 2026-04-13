<?php

namespace App\Livewire;

use App\Models\Office;
use App\Models\QueueEntry;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.public')]
class ClientDashboard extends Component
{
    /** @var array{entry_id: int, office_id: int, office_name: string, queue_number: string, prefix: string|null, client_type: string, client_type_label: string, issued_date: string, issued_time: string}|null */
    public ?array $ticket = null;
    public string $selectedOfficeSlug = '';
    public ?int $pendingOfficeId = null;
    public ?string $pendingOfficeName = null;
    public ?string $pendingServiceKey = null;
    public bool $showClientTypeModal = false;

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

    public function promptOfficeSelection(int $officeId): void
    {
        $office = Office::query()
            ->activePublicQueue()
            ->where('id', $officeId)
            ->first();

        if (! $office) {
            $this->cancelOfficeSelection();

            return;
        }

        $this->pendingOfficeId = $office->id;
        $this->pendingOfficeName = $office->name;
        $this->showClientTypeModal = true;
    }

    public function cancelOfficeSelection(): void
    {
        $this->pendingOfficeId = null;
        $this->pendingOfficeName = null;
        $this->pendingServiceKey = null;
        $this->showClientTypeModal = false;
    }

    public function selectPendingService(string $serviceKey): void
    {
        $office = $this->pendingOffice();

        if (! $office?->queueServiceOption($serviceKey)) {
            return;
        }

        $this->pendingServiceKey = $serviceKey;
    }

    public function backToServiceSelection(): void
    {
        $this->pendingServiceKey = null;
    }

    public function confirmOfficeSelection(string $clientType): void
    {
        if ($this->pendingOfficeId === null) {
            return;
        }

        $this->selectOffice($this->pendingOfficeId, $clientType, $this->pendingServiceKey);
    }

    public function selectOffice(int $officeId, string $clientType = QueueEntry::TYPE_REGULAR, ?string $serviceKey = null): void
    {
        $office = Office::query()
            ->activePublicQueue()
            ->where('id', $officeId)
            ->first();

        if (! $office) {
            return;
        }

        $normalizedClientType = QueueEntry::normalizeClientType($clientType);
        $resolvedServiceKey = $office->queueServiceOption($serviceKey) !== null ? $serviceKey : null;

        if ($office->hasQueueServiceOptions() && $resolvedServiceKey === null) {
            $this->pendingOfficeId = $office->id;
            $this->pendingOfficeName = $office->name;
            $this->showClientTypeModal = true;

            return;
        }

        $queueNumber = $office->generateNextQueueNumber();

        $entry = QueueEntry::create([
            'office_id' => $office->id,
            'queue_number' => $queueNumber,
            'client_type' => $normalizedClientType,
            'service_key' => $resolvedServiceKey,
            'status' => QueueEntry::STATUS_WAITING,
        ]);

        $issuedAt = ($entry->created_at ?? now())->copy()->timezone('Asia/Manila');

        $this->ticket = [
            'entry_id' => $entry->id,
            'office_id' => $office->id,
            'office_name' => $office->name,
            'queue_number' => $entry->queue_number,
            'prefix' => $office->queuePrefix(),
            'client_type' => $normalizedClientType,
            'client_type_label' => QueueEntry::clientTypeLabel($normalizedClientType),
            'service_key' => $resolvedServiceKey,
            'service_label' => $office->queueServiceLabel($resolvedServiceKey),
            'issued_date' => $issuedAt->format('F j, Y'),
            'issued_time' => $issuedAt->format('g:i A'),
        ];

        $this->cancelOfficeSelection();
        $this->dispatch('queue-ticket-created', entryId: $entry->id);
    }

    public function render()
    {
        $officeOptions = $this->getOfficeOptions();
        $this->normalizeSelectedOfficeSlug($officeOptions);

        $offices = $this->selectedOfficeSlug === ''
            ? $officeOptions
            : $officeOptions
                ->where('slug', $this->selectedOfficeSlug)
                ->values();

        return view('livewire.client-dashboard', [
            'offices' => $offices,
            'officeOptions' => $officeOptions,
            'clientTypeOptions' => QueueEntry::selectableClientTypeOptions(),
            'priorityClientTypeOptions' => QueueEntry::priorityClientTypeOptions(),
            'pendingOfficeSlug' => $this->pendingOffice()?->slug,
            'pendingQueueServiceOptions' => $this->pendingOffice()?->queueServiceOptions() ?? [],
            'pendingQueueService' => $this->pendingOffice()?->queueServiceOption($this->pendingServiceKey),
        ]);
    }

    private function normalizeSelectedOfficeSlug(Collection $officeOptions): void
    {
        if ($this->selectedOfficeSlug === '') {
            return;
        }

        $selectedOfficeStillExists = $officeOptions
            ->contains(fn (Office $office) => $office->slug === $this->selectedOfficeSlug);

        if (! $selectedOfficeStillExists) {
            $this->selectedOfficeSlug = '';
        }
    }

    private function pendingOffice(): ?Office
    {
        if ($this->pendingOfficeId === null) {
            return null;
        }

        return Office::query()
            ->activePublicQueue()
            ->where('id', $this->pendingOfficeId)
            ->first();
    }
}
