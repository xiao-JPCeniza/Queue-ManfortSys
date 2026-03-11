<?php

namespace App\Livewire\SuperAdmin;

use App\Models\Office;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Livewire\Component;

class Offices extends Component
{
    public bool $showCreateForm = false;

    public string $officeName = '';

    public string $officePrefix = '';

    public string $officeDescription = '';

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
            'tickets_accommodated_total' => 0,
            'is_active' => true,
            'show_in_public_queue' => true,
        ]);

        $this->resetForm();
        $this->showCreateForm = false;

        session()->flash('success', "{$office->name} was added and is now visible on the public queue page.");
    }

    public function deleteOffice(int $officeId): void
    {
        $office = Office::query()->find($officeId);

        if (! $office) {
            return;
        }

        if ($this->isProtectedOffice($office)) {
            session()->flash('error', "{$office->name} is a protected municipality office and cannot be deleted.");

            return;
        }

        $officeName = $office->name;
        $office->delete();

        session()->flash('success', "{$officeName} was deleted from the public queue.");
    }

    public function render()
    {
        $offices = Office::sortPublicQueueOffices(
            Office::query()
                ->activePublicQueue()
                ->get(['id', 'name', 'slug', 'prefix', 'description'])
        );

        return view('livewire.super-admin.offices', [
            'offices' => $offices,
        ]);
    }

    private function resetForm(): void
    {
        $this->resetValidation();
        $this->officeName = '';
        $this->officePrefix = '';
        $this->officeDescription = '';
    }

    private function isProtectedOffice(Office $office): bool
    {
        return in_array($office->slug, Office::MUNICIPALITY_QUEUE_SERVICE_SLUGS, true);
    }
}
