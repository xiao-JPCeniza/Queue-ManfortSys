<?php

namespace App\Livewire\SuperAdmin;

use App\Models\Office;
use App\Models\QueueEntry;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Livewire\Component;

class Offices extends Component
{
    private const MAX_CONFIGURABLE_SERVICE_WINDOWS = 10;

    public bool $showCreateForm = false;

    public string $officeName = '';

    public string $officePrefix = '';

    public string $officeDescription = '';

    public string $serviceWindowOfficeSlug = '';

    public string $serviceWindowCountSelection = '1';

    public function mount(): void
    {
        $this->syncServiceWindowSelection();
    }

    public function updatedServiceWindowOfficeSlug(string $officeSlug): void
    {
        $this->serviceWindowOfficeSlug = $officeSlug;
        $this->syncServiceWindowSelection();
    }

    public function toggleCreateForm(): void
    {
        $this->showCreateForm = ! $this->showCreateForm;

        if (! $this->showCreateForm) {
            $this->resetForm();
        }
    }

    public function createOffice(): void
    {
        $name = trim($this->officeName);
        $prefix = Str::upper(trim($this->officePrefix));
        $description = trim($this->officeDescription);

        Validator::make(
            [
                'officeName' => $name,
                'officePrefix' => $prefix,
                'officeDescription' => $description,
            ],
            [
                'officeName' => [
                    'required',
                    'string',
                    'max:255',
                    function (string $attribute, mixed $value, \Closure $fail): void {
                        $slug = Str::slug((string) $value);

                        if ($slug === '') {
                            $fail('Office name must contain letters or numbers.');

                            return;
                        }

                        if (Office::query()->where('slug', $slug)->exists()) {
                            $fail('An office with that name already exists.');
                        }
                    },
                ],
                'officePrefix' => [
                    'required',
                    'string',
                    'max:20',
                    function (string $attribute, mixed $value, \Closure $fail): void {
                        $normalizedPrefix = Str::lower((string) $value);

                        if (Office::query()->whereRaw('LOWER(prefix) = ?', [$normalizedPrefix])->exists()) {
                            $fail('That prefix ticket is already in use.');
                        }
                    },
                ],
                'officeDescription' => [
                    'required',
                    'string',
                    'max:255',
                ],
            ],
            [
                'officeName.required' => 'Office name is required.',
                'officePrefix.required' => 'Prefix ticket is required.',
                'officeDescription.required' => 'Meaning or description of the office is required.',
            ]
        )->validate();

        $office = Office::create([
            'name' => $name,
            'slug' => Str::slug($name),
            'prefix' => $prefix,
            'description' => $description,
            'next_number' => 1,
            'service_window_count' => 1,
            'tickets_accommodated_total' => 0,
            'is_active' => true,
            'show_in_public_queue' => true,
        ]);

        $this->resetForm();
        $this->showCreateForm = false;
        $this->syncServiceWindowSelection($this->publicQueueOffices());

        session()->flash('success', "{$office->name} was added and is now visible on the public queue page.");
    }

    public function deleteOffice(int $officeId): void
    {
        $office = Office::query()->find($officeId);

        if (! $office) {
            return;
        }

        $officeName = $office->name;
        $office->delete();
        $this->syncServiceWindowSelection($this->publicQueueOffices());

        session()->flash('success', "{$officeName} was deleted from the public queue.");
    }

    public function updateServiceWindowCount(): void
    {
        $officeOptions = $this->publicQueueOffices();
        $selectedOffice = $officeOptions->firstWhere('slug', $this->serviceWindowOfficeSlug);

        if (! $selectedOffice instanceof Office) {
            $this->syncServiceWindowSelection($officeOptions);
            session()->flash('error', 'Please select a valid office for the service window update.');

            return;
        }

        $requestedWindowCount = min(
            max(1, (int) $this->serviceWindowCountSelection),
            self::MAX_CONFIGURABLE_SERVICE_WINDOWS
        );
        $currentWindowCount = $selectedOffice->resolvedServiceWindowCount();

        if ($requestedWindowCount === $currentWindowCount) {
            session()->flash(
                'success',
                $selectedOffice->name.' already uses '.$currentWindowCount.' service window'.($currentWindowCount === 1 ? '' : 's').'.'
            );

            return;
        }

        $activeWindowNumbers = QueueEntry::query()
            ->where('office_id', $selectedOffice->id)
            ->serving()
            ->get(['service_window_number'])
            ->map(fn (QueueEntry $entry) => $entry->service_window_number ?? 1)
            ->filter(fn (int $windowNumber) => $windowNumber > $requestedWindowCount)
            ->unique()
            ->sort()
            ->values();

        if ($activeWindowNumbers->isNotEmpty()) {
            $blockedWindowNumber = (int) $activeWindowNumbers->first();
            $this->syncServiceWindowSelection($officeOptions);

            session()->flash(
                'error',
                $selectedOffice->name.' still has an active ticket at '.$selectedOffice->serviceWindowLabel($blockedWindowNumber).'. Complete it before reducing the service windows.'
            );

            return;
        }

        $selectedOffice->update(['service_window_count' => $requestedWindowCount]);
        $this->syncServiceWindowSelection($this->publicQueueOffices());

        session()->flash(
            'success',
            $selectedOffice->name.' service windows updated to '.$requestedWindowCount.'.'
        );
    }

    public function render()
    {
        $offices = $this->publicQueueOffices();
        $selectedOffice = $this->resolveSelectedOffice($offices);

        return view('livewire.super-admin.offices', [
            'offices' => $offices,
            'serviceWindowCountOptions' => $this->serviceWindowCountOptions(),
            'serviceWindowSelectedOfficeLabel' => $selectedOffice?->name ?? 'No office selected',
            'serviceWindowCurrentCount' => $selectedOffice?->resolvedServiceWindowCount() ?? 1,
        ]);
    }

    private function resetForm(): void
    {
        $this->resetValidation();
        $this->officeName = '';
        $this->officePrefix = '';
        $this->officeDescription = '';
    }

    private function publicQueueOffices(): Collection
    {
        return Office::sortPublicQueueOffices(
            Office::query()
                ->activePublicQueue()
                ->get(['id', 'name', 'slug', 'prefix', 'description', 'service_window_count'])
        );
    }

    private function syncServiceWindowSelection(?Collection $officeOptions = null): void
    {
        $selectedOffice = $this->resolveSelectedOffice($officeOptions);
        $this->serviceWindowOfficeSlug = $selectedOffice?->slug ?? '';
        $this->serviceWindowCountSelection = (string) ($selectedOffice?->resolvedServiceWindowCount() ?? 1);
    }

    private function resolveSelectedOffice(?Collection $officeOptions = null): ?Office
    {
        $officeOptions ??= $this->publicQueueOffices();

        if ($officeOptions->isEmpty()) {
            return null;
        }

        $selectedOffice = $officeOptions->firstWhere('slug', $this->serviceWindowOfficeSlug);

        return $selectedOffice instanceof Office
            ? $selectedOffice
            : $officeOptions->first();
    }

    private function serviceWindowCountOptions(): Collection
    {
        return collect(range(1, self::MAX_CONFIGURABLE_SERVICE_WINDOWS));
    }
}
