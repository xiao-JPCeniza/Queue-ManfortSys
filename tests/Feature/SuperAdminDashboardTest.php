<?php

namespace Tests\Feature;

use App\Livewire\SuperAdmin\Dashboard as SuperAdminDashboard;
use App\Models\Office;
use App\Models\QueueEntry;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class SuperAdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_super_admin_recent_queue_activity_shows_activity_from_offices_outside_the_featured_card_list(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 10, 10, 0, 0, 'Asia/Manila'));

        $hrmo = $this->createOffice('HRMO', 'hrmo', 'HRMO');
        $obo = $this->createOffice('OBO', 'obo', 'OBO');
        $superAdmin = $this->createSuperAdminUser();

        $this->createQueueEntry(
            office: $hrmo,
            queueNumber: 'HRMO-001',
            status: QueueEntry::STATUS_WAITING,
            createdAt: Carbon::create(2026, 3, 10, 8, 30, 0, 'Asia/Manila')
        );

        $oboCompleted = $this->createQueueEntry(
            office: $obo,
            queueNumber: 'OBO-007',
            status: QueueEntry::STATUS_COMPLETED,
            createdAt: Carbon::create(2026, 3, 10, 8, 45, 0, 'Asia/Manila')
        );

        $this->setServedAt($oboCompleted, Carbon::create(2026, 3, 10, 9, 15, 0, 'Asia/Manila'));

        $this->actingAs($superAdmin);

        Livewire::test(SuperAdminDashboard::class)
            ->assertSee('OBO')
            ->assertSee('OBO-007')
            ->assertSee('Completed');
    }

    public function test_super_admin_recent_queue_activity_includes_entries_completed_today_even_if_created_yesterday(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 10, 10, 0, 0, 'Asia/Manila'));

        $hrmo = $this->createOffice('HRMO', 'hrmo', 'HRMO');
        $superAdmin = $this->createSuperAdminUser();

        $completedToday = $this->createQueueEntry(
            office: $hrmo,
            queueNumber: 'HRMO-014',
            status: QueueEntry::STATUS_COMPLETED,
            createdAt: Carbon::create(2026, 3, 9, 16, 0, 0, 'Asia/Manila')
        );

        $this->setServedAt($completedToday, Carbon::create(2026, 3, 10, 9, 10, 0, 'Asia/Manila'));

        $this->actingAs($superAdmin);

        Livewire::test(SuperAdminDashboard::class)
            ->assertSee('HRMO-014')
            ->assertSee('Completed');
    }

    public function test_super_admin_office_cards_include_new_public_queue_offices_but_exclude_hidden_ones(): void
    {
        $superAdmin = $this->createSuperAdminUser();

        $this->createOffice('HRMO', 'hrmo', 'HRMO', true);
        $this->createOffice('Citizen Center', 'citizen-center', 'CCEN', true);
        $this->createOffice('OBO', 'obo', 'OBO', false);

        $this->actingAs($superAdmin);

        Livewire::test(SuperAdminDashboard::class)
            ->assertSee('HRMO')
            ->assertSee('Citizen Center')
            ->assertDontSee('OBO');
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

    private function createOffice(string $name, string $slug, string $prefix, bool $showInPublicQueue = false): Office
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

    private function createQueueEntry(Office $office, string $queueNumber, string $status, Carbon $createdAt): QueueEntry
    {
        $entry = QueueEntry::create([
            'office_id' => $office->id,
            'queue_number' => $queueNumber,
            'status' => $status,
        ]);

        $timestamp = $createdAt->copy()->setTimezone((string) config('app.timezone', 'UTC'));

        QueueEntry::whereKey($entry)->update([
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);

        return $entry->fresh();
    }

    private function setServedAt(QueueEntry $entry, Carbon $servedAt): void
    {
        $timestamp = $servedAt->copy()->setTimezone((string) config('app.timezone', 'UTC'));

        QueueEntry::whereKey($entry)->update([
            'served_at' => $timestamp,
            'updated_at' => $timestamp,
        ]);
    }
}
