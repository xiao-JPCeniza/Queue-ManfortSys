<?php

namespace Tests\Feature;

use App\Livewire\SuperAdmin\Offices as SuperAdminOffices;
use App\Models\Office;
use App\Models\QueueEntry;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SuperAdminOfficesTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_view_the_offices_page_and_only_public_queue_offices_are_listed(): void
    {
        $superAdmin = $this->createSuperAdminUser();

        $this->createOffice('HRMO', 'hrmo', 'HRMO', true);
        $this->createOffice('Accounting', 'accounting', 'ACCT', true);
        $this->createOffice('OBO', 'obo', 'OBO', false);

        $this->actingAs($superAdmin)
            ->get(route('super-admin.offices'))
            ->assertOk()
            ->assertSee('Public Queue Offices')
            ->assertSee('Office Name')
            ->assertSee('Label')
            ->assertSee('Prefix Ticket')
            ->assertSee('+ Add Office')
            ->assertSee('Human Resource Management Office')
            ->assertSee('Municipal Accounting Office')
            ->assertSee('HRMO')
            ->assertDontSee('Manage')
            ->assertDontSee('OBO');
    }

    public function test_super_admin_can_add_a_public_queue_office_with_default_queue_settings(): void
    {
        $superAdmin = $this->createSuperAdminUser();

        $this->actingAs($superAdmin);

        Livewire::test(SuperAdminOffices::class)
            ->set('showCreateForm', true)
            ->set('officeName', 'Citizen Center')
            ->set('officePrefix', 'ccen')
            ->call('createOffice')
            ->assertHasNoErrors()
            ->assertSee('Citizen Center')
            ->assertSee('CCEN');

        $this->assertDatabaseHas('offices', [
            'name' => 'Citizen Center',
            'slug' => 'citizen-center',
            'prefix' => 'CCEN',
            'description' => 'Citizen Center services',
            'next_number' => 1,
            'tickets_accommodated_total' => 0,
            'is_active' => true,
            'show_in_public_queue' => true,
        ]);
    }

    public function test_super_admin_cannot_add_duplicate_office_names_or_prefixes(): void
    {
        $superAdmin = $this->createSuperAdminUser();

        $this->createOffice('Citizen Center', 'citizen-center', 'CCEN', true);

        $this->actingAs($superAdmin);

        Livewire::test(SuperAdminOffices::class)
            ->set('showCreateForm', true)
            ->set('officeName', 'Citizen Center')
            ->set('officePrefix', 'NCTR')
            ->call('createOffice')
            ->assertHasErrors(['officeName']);

        Livewire::test(SuperAdminOffices::class)
            ->set('showCreateForm', true)
            ->set('officeName', 'New Center')
            ->set('officePrefix', 'ccen')
            ->call('createOffice')
            ->assertHasErrors(['officePrefix']);
    }

    public function test_super_admin_can_delete_an_office_and_clean_up_related_records(): void
    {
        $superAdmin = $this->createSuperAdminUser();
        $office = $this->createOffice('Citizen Center', 'citizen-center', 'CCEN', true);
        $user = User::factory()->create([
            'role_id' => null,
            'office_id' => $office->id,
        ]);
        $entry = QueueEntry::create([
            'office_id' => $office->id,
            'queue_number' => 'CCEN-001',
            'status' => QueueEntry::STATUS_WAITING,
        ]);

        $this->actingAs($superAdmin);

        Livewire::test(SuperAdminOffices::class)
            ->call('deleteOffice', $office->id)
            ->assertDontSee('Citizen Center');

        $this->assertDatabaseMissing('offices', [
            'id' => $office->id,
        ]);
        $this->assertDatabaseMissing('queue_entries', [
            'id' => $entry->id,
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'office_id' => null,
        ]);
    }

    public function test_super_admin_can_delete_a_default_public_queue_office_without_breaking_admin_routes(): void
    {
        $superAdmin = $this->createSuperAdminUser();
        $office = $this->createOffice('HRMO', 'hrmo', 'HRMO', true);
        $this->createOffice('Accounting', 'accounting', 'ACCT', true);

        $this->actingAs($superAdmin);

        Livewire::test(SuperAdminOffices::class)
            ->call('deleteOffice', $office->id)
            ->assertDontSee('HRMO');

        $this->assertDatabaseMissing('offices', [
            'id' => $office->id,
            'slug' => 'hrmo',
        ]);

        $this->get(route('super-admin.reports'))
            ->assertOk()
            ->assertSee('Accommodated Tickets by Office');
    }

    private function createSuperAdminUser(): User
    {
        $role = Role::create([
            'name' => 'Super Admin',
            'slug' => 'super_admin',
            'description' => 'System-wide administrator',
        ]);

        return User::factory()->create([
            'role_id' => $role->id,
            'office_id' => null,
        ]);
    }

    private function createOffice(string $name, string $slug, string $prefix, bool $showInPublicQueue): Office
    {
        return Office::create([
            'name' => $name,
            'slug' => $slug,
            'prefix' => $prefix,
            'description' => $name.' services',
            'next_number' => 1,
            'tickets_accommodated_total' => 0,
            'is_active' => true,
            'show_in_public_queue' => $showInPublicQueue,
        ]);
    }
}
