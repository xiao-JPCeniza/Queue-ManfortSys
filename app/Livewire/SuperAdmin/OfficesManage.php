<?php

namespace App\Livewire\SuperAdmin;

use App\Models\AuditLog;
use App\Models\Office;
use App\Models\QueueEntry;
use App\Models\Role;
use App\Models\User;
use App\Services\OfficeAdminCredentialService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class OfficesManage extends Component
{
    public string $name = '';
    public string $slug = '';
    public string $prefix = '';
    public string $description = '';

    /** @var array{username: string, password: string, office_name: string}|null One-time credentials shown after create */
    public ?array $showCredentials = null;

    public function mount(): void
    {
        if (! auth()->user()->isSuperAdmin()) {
            abort(403, 'Only Super Admin can manage queueing offices.');
        }
    }

    public function updatedName(string $value): void
    {
        if ($this->slug === '' || Str::slug($this->slug) === Str::slug($value)) {
            $this->slug = Str::slug($value);
        }
    }

    public function addOffice(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'unique:offices,slug'],
            'prefix' => ['nullable', 'string', 'max:20'],
            'description' => ['nullable', 'string', 'max:500'],
        ], [
            'slug.regex' => 'The slug must be lowercase letters, numbers, and hyphens only.',
            'slug.unique' => 'An office with this slug already exists.',
        ]);

        $officeAdminRole = Role::where('slug', 'office_admin')->first();
        if (! $officeAdminRole) {
            throw ValidationException::withMessages(['name' => ['Office Admin role is not set up. Run role seeder.']]);
        }

        $email = OfficeAdminCredentialService::emailForOfficeSlug($this->slug);
        if (User::where('email', $email)->exists()) {
            throw ValidationException::withMessages(['slug' => ['An office admin with this slug already exists. Choose a different slug.']]);
        }

        $plainPassword = OfficeAdminCredentialService::generateSecurePassword(16);

        try {
            DB::beginTransaction();

            $office = Office::create([
                'name' => $this->name,
                'slug' => $this->slug,
                'prefix' => $this->prefix ?: strtoupper(substr($this->slug, 0, 4)),
                'description' => $this->description ?: null,
                'next_number' => 1,
                'is_active' => true,
            ]);

            $user = User::create([
                'name' => $office->name . ' Admin',
                'email' => $email,
                'password' => $plainPassword, // cast to hashed in User model
                'role_id' => $officeAdminRole->id,
                'office_id' => $office->id,
            ]);

            AuditLog::log('office_created', Office::class, $office->id, null, [
                'name' => $office->name,
                'slug' => $office->slug,
                'prefix' => $office->prefix,
            ]);
            AuditLog::log('office_admin_created', User::class, $user->id, null, [
                'email' => $user->email,
                'office_id' => $office->id,
                'role_id' => $officeAdminRole->id,
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw ValidationException::withMessages(['name' => ['Failed to create office: ' . $e->getMessage()]]);
        }

        $this->showCredentials = [
            'username' => $email,
            'password' => $plainPassword,
            'office_name' => $office->name,
        ];
        $this->reset(['name', 'slug', 'prefix', 'description']);
        $this->dispatch('office-added');
    }

    public function dismissCredentials(): void
    {
        $this->showCredentials = null;
    }

    public function removeOffice(int $officeId): void
    {
        $office = Office::find($officeId);
        if (! $office) {
            return;
        }

        $waitingCount = $office->queueEntries()->where('status', QueueEntry::STATUS_WAITING)->count();
        $servingCount = $office->queueEntries()->where('status', QueueEntry::STATUS_SERVING)->count();
        if ($waitingCount > 0 || $servingCount > 0) {
            throw ValidationException::withMessages([
                'office' => 'Cannot remove this office while it has active queue entries (waiting or being served). Please clear the queue first.',
            ]);
        }

        $officeUsers = $office->users()->get();
        $officeName = $office->name;
        $officeSlug = $office->slug;
        $officeIdForLog = $office->id;

        try {
            DB::beginTransaction();

            foreach ($officeUsers as $u) {
                AuditLog::log('office_admin_deleted', User::class, $u->id, [
                    'email' => $u->email,
                    'office_id' => $u->office_id,
                ], null);
            }
            User::where('office_id', $office->id)->delete();
            $office->delete();

            AuditLog::log('office_deleted', Office::class, null, [
                'id' => $officeIdForLog,
                'name' => $officeName,
                'slug' => $officeSlug,
            ], null);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw ValidationException::withMessages(['office' => 'Failed to remove office: ' . $e->getMessage()]);
        }

        $this->dispatch('office-removed');
    }

    public function render()
    {
        $offices = Office::withCount('users')
            ->orderBy('name')
            ->get();

        $auditLogs = AuditLog::with('user')
            ->orderByDesc('created_at')
            ->limit(15)
            ->get();

        return view('livewire.super-admin.offices-manage', [
            'offices' => $offices,
            'auditLogs' => $auditLogs,
        ]);
    }
}
