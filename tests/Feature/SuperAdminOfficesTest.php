<?php

namespace Tests\Feature;

use App\Livewire\SuperAdmin\Offices as SuperAdminOffices;
use App\Models\Office;
use App\Models\QueueEntry;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\OfficeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class SuperAdminOfficesTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

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
            ->assertSee('Service Windows')
            ->assertSee('Service Window Setup')
            ->assertSee('+ Add Office')
            ->assertSee('Live Monitor Videos')
            ->assertSee('Human Resource Management Office')
            ->assertSee('Municipal Accounting Office')
            ->assertSee('2 offices')
            ->assertSee('HRMO')
            ->assertDontSee('Live Monitor Idle Video')
            ->assertDontSee('Queue Link');
    }

    public function test_super_admin_can_add_a_public_queue_office_with_default_queue_settings(): void
    {
        $superAdmin = $this->createSuperAdminUser();

        $this->actingAs($superAdmin);

        Livewire::test(SuperAdminOffices::class)
            ->call('toggleCreateForm')
            ->set('officeName', 'Citizen Center')
            ->set('officePrefix', 'ccen')
            ->set('officeDescription', 'Citizen Center services')
            ->set('officeAdminEmail', 'citizen.center@manolofortich.gov.ph')
            ->set('officeAdminPassword', 'Citizen123')
            ->set('officeAdminPasswordConfirmation', 'Citizen123')
            ->call('createOffice')
            ->assertHasNoErrors()
            ->assertSee('Citizen Center')
            ->assertSee('CCEN')
            ->assertSee('citizen.center@manolofortich.gov.ph')
            ->assertSee('Password');

        $this->assertDatabaseHas('offices', [
            'name' => 'Citizen Center',
            'slug' => 'citizen-center',
            'prefix' => 'CCEN',
            'description' => 'Citizen Center services',
            'next_number' => 1,
            'service_window_count' => 1,
            'tickets_accommodated_total' => 0,
            'is_active' => true,
            'show_in_public_queue' => true,
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Citizen Center Office Admin',
            'email' => 'citizen.center@manolofortich.gov.ph',
            'office_id' => Office::query()->where('slug', 'citizen-center')->value('id'),
            'role_id' => Role::query()->where('slug', 'office_admin')->value('id'),
        ]);

        $officeAdminUser = User::query()
            ->where('email', 'citizen.center@manolofortich.gov.ph')
            ->firstOrFail();

        $this->assertSame('Citizen123', $officeAdminUser->recoverable_password);
    }

    public function test_super_admin_can_update_service_windows_from_the_offices_page(): void
    {
        $superAdmin = $this->createSuperAdminUser();
        $this->createOffice('HRMO', 'hrmo', 'HRMO', true, 1);
        $treasury = $this->createOffice('Treasury', 'treasury', 'TRSY', true, 8);

        $this->actingAs($superAdmin);

        Livewire::test(SuperAdminOffices::class)
            ->set('serviceWindowOfficeSlug', 'treasury')
            ->assertSet('serviceWindowCountSelection', '8')
            ->set('serviceWindowCountSelection', '5')
            ->call('updateServiceWindowCount')
            ->assertSee('Treasury service windows updated to 5.')
            ->assertSet('serviceWindowCountSelection', '5');

        $this->assertDatabaseHas('offices', [
            'id' => $treasury->id,
            'service_window_count' => 5,
        ]);
    }

    public function test_super_admin_cannot_reduce_service_windows_below_an_active_window_from_the_offices_page(): void
    {
        $superAdmin = $this->createSuperAdminUser();
        $treasury = $this->createOffice('Treasury', 'treasury', 'TRSY', true, 4);

        $servingEntry = QueueEntry::create([
            'office_id' => $treasury->id,
            'queue_number' => 'TRSY-001',
            'status' => QueueEntry::STATUS_SERVING,
            'service_window_number' => 4,
        ]);

        $this->actingAs($superAdmin);

        Livewire::test(SuperAdminOffices::class)
            ->set('serviceWindowOfficeSlug', 'treasury')
            ->set('serviceWindowCountSelection', '2')
            ->call('updateServiceWindowCount')
            ->assertSee('Treasury still has an active ticket at Window 4.')
            ->assertSet('serviceWindowCountSelection', '4');

        $this->assertDatabaseHas('offices', [
            'id' => $treasury->id,
            'service_window_count' => 4,
        ]);

        $this->assertDatabaseHas('queue_entries', [
            'id' => $servingEntry->id,
            'service_window_number' => 4,
        ]);
    }

    public function test_reseeding_offices_preserves_saved_service_window_counts(): void
    {
        $treasury = $this->createOffice('Treasury', 'treasury', 'TRSY', true, 5);

        $this->seed(OfficeSeeder::class);

        $this->assertDatabaseHas('offices', [
            'id' => $treasury->id,
            'slug' => 'treasury',
            'service_window_count' => 5,
        ]);
    }

    public function test_super_admin_can_reset_queue_numbering_for_a_specific_office_from_the_offices_page(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 23, 10, 0, 0, 'Asia/Manila'));

        $superAdmin = $this->createSuperAdminUser();
        $office = $this->createOffice('HRMO', 'hrmo', 'HRMO', true, 5);
        $office->update(['next_number' => 14]);

        $yesterdayEntry = QueueEntry::create([
            'office_id' => $office->id,
            'queue_number' => 'HRMO-013',
            'status' => QueueEntry::STATUS_WAITING,
        ]);

        $todayEntry = QueueEntry::create([
            'office_id' => $office->id,
            'queue_number' => 'HRMO-014',
            'status' => QueueEntry::STATUS_WAITING,
        ]);

        QueueEntry::whereKey($yesterdayEntry)->update([
            'created_at' => Carbon::create(2026, 3, 22, 16, 0, 0, 'Asia/Manila')
                ->setTimezone((string) config('app.timezone', 'UTC')),
        ]);

        QueueEntry::whereKey($todayEntry)->update([
            'created_at' => Carbon::create(2026, 3, 23, 9, 45, 0, 'Asia/Manila')
                ->setTimezone((string) config('app.timezone', 'UTC')),
        ]);

        $this->actingAs($superAdmin);

        Livewire::test(SuperAdminOffices::class)
            ->call('resetNumbering', $office->id)
            ->assertSee('Queue numbering reset for HRMO. The next generated number will start from 001.');

        $this->assertDatabaseMissing('queue_entries', ['id' => $todayEntry->id]);
        $this->assertDatabaseHas('queue_entries', ['id' => $yesterdayEntry->id]);
        $this->assertDatabaseHas('offices', [
            'id' => $office->id,
            'next_number' => 1,
        ]);
    }

    public function test_super_admin_cannot_add_duplicate_office_names_or_prefixes(): void
    {
        $superAdmin = $this->createSuperAdminUser();

        $this->createOffice('Citizen Center', 'citizen-center', 'CCEN', true);

        $this->actingAs($superAdmin);

        Livewire::test(SuperAdminOffices::class)
            ->call('toggleCreateForm')
            ->set('officeName', 'Citizen Center')
            ->set('officePrefix', 'NCTR')
            ->set('officeDescription', 'Citizen Center services')
            ->set('officeAdminEmail', 'citizen.center2@manolofortich.gov.ph')
            ->set('officeAdminPassword', 'Citizen123')
            ->set('officeAdminPasswordConfirmation', 'Citizen123')
            ->call('createOffice')
            ->assertHasErrors(['officeName']);

        Livewire::test(SuperAdminOffices::class)
            ->call('toggleCreateForm')
            ->set('officeName', 'New Center')
            ->set('officePrefix', 'ccen')
            ->set('officeDescription', 'New Center services')
            ->set('officeAdminEmail', 'new.center@manolofortich.gov.ph')
            ->set('officeAdminPassword', 'Citizen123')
            ->set('officeAdminPasswordConfirmation', 'Citizen123')
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
            ->assertSee('was deleted from the public queue.')
            ->assertSee('No public queue offices are configured yet.');

        $this->assertDatabaseMissing('offices', [
            'id' => $office->id,
        ]);
        $this->assertDatabaseMissing('queue_entries', [
            'id' => $entry->id,
        ]);
        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    public function test_deleted_office_email_can_be_reused_without_incrementing_the_suggested_suffix(): void
    {
        $superAdmin = $this->createSuperAdminUser();
        $officeAdminRole = $this->createOfficeAdminRole();
        $office = $this->createOffice('Ledipo', 'ledipo', 'LEDI', true);

        User::factory()->create([
            'name' => 'Ledipo Office Admin',
            'email' => 'ledipo@manolofortich.gov.ph',
            'role_id' => $officeAdminRole->id,
            'office_id' => $office->id,
        ]);

        User::factory()->create([
            'name' => 'Ledipo Office Admin 2',
            'email' => 'ledipo2@manolofortich.gov.ph',
            'role_id' => $officeAdminRole->id,
            'office_id' => $office->id,
        ]);

        User::factory()->create([
            'name' => 'Ledipo Office Admin 3',
            'email' => 'ledipo3@manolofortich.gov.ph',
            'role_id' => $officeAdminRole->id,
            'office_id' => $office->id,
        ]);

        $this->actingAs($superAdmin);

        Livewire::test(SuperAdminOffices::class)
            ->call('deleteOffice', $office->id);

        Livewire::test(SuperAdminOffices::class)
            ->call('toggleCreateForm')
            ->set('officeName', 'Ledipo')
            ->assertSet('officeAdminEmail', 'ledipo@manolofortich.gov.ph');
    }

    public function test_super_admin_can_delete_a_default_public_queue_office_without_breaking_admin_routes(): void
    {
        $superAdmin = $this->createSuperAdminUser();
        $office = $this->createOffice('HRMO', 'hrmo', 'HRMO', true);
        $this->createOffice('Accounting', 'accounting', 'ACCT', true);

        $this->actingAs($superAdmin);

        Livewire::test(SuperAdminOffices::class)
            ->call('deleteOffice', $office->id)
            ->assertSee('Accounting')
            ->assertSee('1 office');

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
        $role = Role::firstOrCreate([
            'slug' => 'super_admin',
        ], [
            'name' => 'Super Admin',
            'description' => 'System-wide administrator',
        ]);

        $this->createOfficeAdminRole();

        return User::factory()->create([
            'role_id' => $role->id,
            'office_id' => null,
        ]);
    }

    private function createOfficeAdminRole(): Role
    {
        return Role::firstOrCreate([
            'slug' => 'office_admin',
        ], [
            'name' => 'Office Admin',
            'description' => 'Office-specific administrator',
        ]);
    }

    private function createOffice(string $name, string $slug, string $prefix, bool $showInPublicQueue, int $serviceWindowCount = 1): Office
    {
        return Office::create([
            'name' => $name,
            'slug' => $slug,
            'prefix' => $prefix,
            'description' => $name.' services',
            'next_number' => 1,
            'service_window_count' => $serviceWindowCount,
            'tickets_accommodated_total' => 0,
            'is_active' => true,
            'show_in_public_queue' => $showInPublicQueue,
        ]);
    }
}
