<?php

namespace App\Livewire\SuperAdmin;

use App\Models\Office;
use App\Models\QueueEntry;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Livewire\Component;

class Offices extends Component
{
    private const MAX_CONFIGURABLE_SERVICE_WINDOWS = 10;
    private const GENERATED_ACCOUNT_DOMAIN = 'manolofortich.gov.ph';

    public bool $showCreateForm = false;

    public string $officeName = '';

    public string $officePrefix = '';

    public string $officeDescription = '';

    public string $officeAdminEmail = '';

    public string $officeAdminPassword = '';

    public string $officeAdminPasswordConfirmation = '';

    public bool $officeAdminEmailManuallyEdited = false;

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

        if ($this->showCreateForm) {
            $this->prepareSuggestedOfficeAdminCredentials();
        } else {
            $this->resetForm();
        }
    }

    public function updatedOfficeName(string $officeName): void
    {
        $this->officeName = $officeName;

        if (! $this->officeAdminEmailManuallyEdited) {
            $this->officeAdminEmail = $this->suggestOfficeAdminEmail($officeName);
        }
    }

    public function updatedOfficeAdminEmail(string $officeAdminEmail): void
    {
        $normalizedEmail = Str::lower(trim($officeAdminEmail));
        $suggestedEmail = $this->suggestOfficeAdminEmail($this->officeName);

        $this->officeAdminEmail = $normalizedEmail;
        $this->officeAdminEmailManuallyEdited = $normalizedEmail !== '' && $normalizedEmail !== $suggestedEmail;
    }

    public function regenerateOfficeAdminPassword(): void
    {
        $generatedPassword = $this->generateTemporaryOfficeAdminPassword();

        $this->officeAdminPassword = $generatedPassword;
        $this->officeAdminPasswordConfirmation = $generatedPassword;
    }

    public function createOffice(): void
    {
        $name = trim($this->officeName);
        $prefix = Str::upper(trim($this->officePrefix));
        $description = trim($this->officeDescription);
        $officeAdminEmail = Str::lower(trim($this->officeAdminEmail));
        $officeAdminPassword = trim($this->officeAdminPassword);
        $officeAdminPasswordConfirmation = trim($this->officeAdminPasswordConfirmation);

        Validator::make(
            [
                'officeName' => $name,
                'officePrefix' => $prefix,
                'officeDescription' => $description,
                'officeAdminEmail' => $officeAdminEmail,
                'officeAdminPassword' => $officeAdminPassword,
                'officeAdminPasswordConfirmation' => $officeAdminPasswordConfirmation,
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
                'officeAdminEmail' => [
                    'required',
                    'email',
                    'max:255',
                    function (string $attribute, mixed $value, \Closure $fail): void {
                        $normalizedEmail = Str::lower((string) $value);

                        if (User::query()->whereRaw('LOWER(email) = ?', [$normalizedEmail])->exists()) {
                            $fail('That office login email is already in use.');
                        }
                    },
                ],
                'officeAdminPassword' => [
                    'required',
                    'string',
                    'min:8',
                    'max:255',
                ],
                'officeAdminPasswordConfirmation' => [
                    'required',
                    'same:officeAdminPassword',
                ],
            ],
            [
                'officeName.required' => 'Office name is required.',
                'officePrefix.required' => 'Prefix ticket is required.',
                'officeDescription.required' => 'Meaning or description of the office is required.',
                'officeAdminEmail.required' => 'Office login email is required.',
                'officeAdminPassword.required' => 'Temporary password is required.',
                'officeAdminPassword.min' => 'Temporary password must be at least 8 characters.',
                'officeAdminPasswordConfirmation.required' => 'Please confirm the temporary password.',
                'officeAdminPasswordConfirmation.same' => 'The password confirmation does not match.',
            ]
        )->validate();

        $officeAdminRole = Role::query()->where('slug', 'office_admin')->first();

        if (! $officeAdminRole) {
            session()->flash('error', 'Office Admin role is missing. Please seed roles first before creating a new office account.');

            return;
        }

        [$office, $officeAdminUser, $generatedPassword] = DB::transaction(function () use ($description, $name, $officeAdminEmail, $officeAdminPassword, $officeAdminRole, $prefix): array {
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

            $officeAdminUser = User::create([
                'name' => $office->name.' Office Admin',
                'email' => $officeAdminEmail,
                'password' => $officeAdminPassword,
                'role_id' => $officeAdminRole->id,
                'office_id' => $office->id,
            ]);

            return [$office, $officeAdminUser, $officeAdminPassword];
        });

        $this->resetForm();
        $this->showCreateForm = false;
        $this->syncServiceWindowSelection($this->publicQueueOffices());

        session()->flash('success', "{$office->name} was added, is now visible on the public queue page, and already has its own office login.");
        session()->flash('generatedOfficeAccount', [
            'office_name' => $office->name,
            'email' => $officeAdminUser->email,
            'password' => $generatedPassword,
            'dashboard_url' => route('office.dashboard', $office->slug),
            'login_url' => route('login'),
        ]);
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
        $this->officeAdminEmail = '';
        $this->officeAdminPassword = '';
        $this->officeAdminPasswordConfirmation = '';
        $this->officeAdminEmailManuallyEdited = false;
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

    private function prepareSuggestedOfficeAdminCredentials(): void
    {
        if ($this->officeName !== '' && ! $this->officeAdminEmailManuallyEdited) {
            $this->officeAdminEmail = $this->suggestOfficeAdminEmail($this->officeName);
        }

        if ($this->officeAdminPassword === '') {
            $this->regenerateOfficeAdminPassword();
        }
    }

    private function suggestOfficeAdminEmail(string $officeName): string
    {
        $officeSlug = Str::slug(trim($officeName));

        if ($officeSlug === '') {
            return '';
        }

        return $this->generateAvailableOfficeAdminEmail($officeSlug);
    }

    private function generateAvailableOfficeAdminEmail(string $officeSlug): string
    {
        $baseLocalPart = Str::of($officeSlug)
            ->replaceMatches('/[^a-z0-9]+/i', '.')
            ->trim('.')
            ->lower()
            ->value();

        if ($baseLocalPart === '') {
            $baseLocalPart = 'office.admin';
        }

        $candidateEmail = $baseLocalPart.'@'.self::GENERATED_ACCOUNT_DOMAIN;

        if (! User::query()->whereRaw('LOWER(email) = ?', [$candidateEmail])->exists()) {
            return $candidateEmail;
        }

        $suffix = 2;

        do {
            $candidateEmail = $baseLocalPart.$suffix.'@'.self::GENERATED_ACCOUNT_DOMAIN;
            $suffix++;
        } while (User::query()->whereRaw('LOWER(email) = ?', [$candidateEmail])->exists());

        return $candidateEmail;
    }

    private function generateTemporaryOfficeAdminPassword(): string
    {
        return Str::password(12, true, true, false, false);
    }
}
