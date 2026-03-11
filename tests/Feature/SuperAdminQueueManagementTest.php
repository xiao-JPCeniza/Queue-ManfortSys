<?php

namespace Tests\Feature;

use App\Livewire\OfficeAdmin\Dashboard;
use App\Models\Office;
use App\Models\QueueEntry;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class SuperAdminQueueManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_super_admin_queue_management_paginates_queued_today_office_cards(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 10, 10, 0, 0, 'Asia/Manila'));

        $hrmo = $this->createOffice('HRMO', 'hrmo', 'HRMO', 10);
        $this->createOffice('Accounting', 'accounting', 'ACCT', 12);
        $this->createOffice("Assessor's Office", 'assessors-office', 'ASSR', 8);
        $this->createOffice('Business Permits', 'business-permits', 'BPLO', 16);
        $this->createOffice('Treasury', 'treasury', 'TRES', 11);

        $superAdmin = $this->createSuperAdminUser();

        $this->actingAs($superAdmin);

        Livewire::test(Dashboard::class, ['office' => $hrmo])
            ->set('hrmoTab', 'queue-management')
            ->assertSee('Mega Menu')
            ->assertSee('Queued Today')
            ->assertViewHas('queuedTodayPagination', function (array $pagination) {
                return $pagination['current_page'] === 1
                    && $pagination['last_page'] === 2
                    && $pagination['total'] === 5
                    && $pagination['from'] === 1
                    && $pagination['to'] === 3;
            })
            ->assertViewHas('queuedTodayOfficeActivity', function ($rows) {
                return $rows->count() === 3
                    && $rows->pluck('office.slug')->all() === [
                        'accounting',
                        'assessors-office',
                        'business-permits',
                    ];
            })
            ->call('nextQueuedTodayPage')
            ->assertViewHas('queuedTodayPagination', function (array $pagination) {
                return $pagination['current_page'] === 2
                    && $pagination['last_page'] === 2
                    && $pagination['from'] === 4
                    && $pagination['to'] === 5;
            })
            ->assertViewHas('queuedTodayOfficeActivity', function ($rows) {
                return $rows->count() === 2
                    && $rows->pluck('office.slug')->all() === [
                        'hrmo',
                        'treasury',
                    ];
            });
    }

    public function test_super_admin_overall_data_can_be_filtered_by_office(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 10, 10, 0, 0, 'Asia/Manila'));

        $hrmo = $this->createOffice('HRMO', 'hrmo', 'HRMO', 10);
        $accounting = $this->createOffice('Accounting', 'accounting', 'ACCT', 12);
        $treasury = $this->createOffice('Treasury', 'treasury', 'TRES', 7);

        $superAdmin = $this->createSuperAdminUser();

        $this->createQueueEntry(
            office: $accounting,
            queueNumber: 'ACCT-001',
            status: QueueEntry::STATUS_COMPLETED,
            createdAt: Carbon::create(2026, 3, 10, 8, 15, 0, 'Asia/Manila')
        );
        $this->createQueueEntry(
            office: $accounting,
            queueNumber: 'ACCT-002',
            status: QueueEntry::STATUS_WAITING,
            createdAt: Carbon::create(2026, 3, 10, 8, 30, 0, 'Asia/Manila')
        );
        $this->createQueueEntry(
            office: $accounting,
            queueNumber: 'ACCT-003',
            status: QueueEntry::STATUS_COMPLETED,
            createdAt: Carbon::create(2026, 3, 10, 9, 0, 0, 'Asia/Manila')
        );
        $this->createQueueEntry(
            office: $treasury,
            queueNumber: 'TRES-001',
            status: QueueEntry::STATUS_COMPLETED,
            createdAt: Carbon::create(2026, 3, 10, 9, 30, 0, 'Asia/Manila')
        );

        $this->actingAs($superAdmin);

        Livewire::test(Dashboard::class, ['office' => $hrmo])
            ->set('hrmoTab', 'queue-management')
            ->call('setQueueManagementSection', 'overall-data')
            ->set('queueManagementOfficeFilter', 'accounting')
            ->assertViewHas('queueManagementSelectedOfficeLabel', 'Accounting')
            ->assertViewHas('overallDataSummary', function (array $summary) {
                return $summary['office_count'] === 1
                    && $summary['overall_queued_total'] === 3
                    && $summary['accommodated_total'] === 2;
            })
            ->assertViewHas('overallDataRows', function ($rows) {
                $accountingRow = $rows->first();

                return $rows->count() === 1
                    && $accountingRow['office_slug'] === 'accounting'
                    && $accountingRow['overall_queued_total'] === 3
                    && $accountingRow['accommodated_total'] === 2
                    && $accountingRow['completed_queue_numbers'] === ['ACCT-001', 'ACCT-003'];
            });
    }

    public function test_super_admin_reports_count_only_completed_tickets_as_accommodated(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 10, 10, 0, 0, 'Asia/Manila'));

        $hrmo = $this->createOffice('HRMO', 'hrmo', 'HRMO', 25);
        $accounting = $this->createOffice('Accounting', 'accounting', 'ACCT', 12);
        $treasury = $this->createOffice('Treasury', 'treasury', 'TRES', 7);

        $superAdmin = $this->createSuperAdminUser();

        $this->createQueueEntry(
            office: $accounting,
            queueNumber: 'ACCT-001',
            status: QueueEntry::STATUS_COMPLETED,
            createdAt: Carbon::create(2026, 3, 10, 8, 15, 0, 'Asia/Manila')
        );
        $this->createQueueEntry(
            office: $accounting,
            queueNumber: 'ACCT-002',
            status: QueueEntry::STATUS_WAITING,
            createdAt: Carbon::create(2026, 3, 10, 8, 30, 0, 'Asia/Manila')
        );
        $this->createQueueEntry(
            office: $treasury,
            queueNumber: 'TRES-001',
            status: QueueEntry::STATUS_COMPLETED,
            createdAt: Carbon::create(2026, 3, 10, 9, 30, 0, 'Asia/Manila')
        );

        $this->actingAs($superAdmin);

        Livewire::test(Dashboard::class, ['office' => $hrmo])
            ->set('hrmoTab', 'reports')
            ->assertViewHas('summary', function (array $summary) {
                return $summary['total_today'] === 3
                    && $summary['completed_today'] === 2
                    && $summary['overall_accommodated'] === 2;
            })
            ->assertViewHas('officeAccommodatedChartSeries', function (array $rows) {
                return count($rows) === 3
                    && collect($rows)->firstWhere('office_name', 'Accounting')['accommodated_total'] === 1
                    && collect($rows)->firstWhere('office_name', 'Treasury')['accommodated_total'] === 1
                    && collect($rows)->firstWhere('office_name', 'HRMO')['accommodated_total'] === 0;
            });
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

    private function createOffice(string $name, string $slug, string $prefix, int $ticketsAccommodatedTotal): Office
    {
        return Office::create([
            'name' => $name,
            'slug' => $slug,
            'prefix' => $prefix,
            'description' => $name.' services',
            'next_number' => 1,
            'tickets_accommodated_total' => $ticketsAccommodatedTotal,
            'is_active' => true,
            'show_in_public_queue' => true,
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
}
