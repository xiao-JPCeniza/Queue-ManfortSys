<?php

namespace Tests\Feature;

use App\Livewire\OfficeAdmin\Dashboard;
use App\Livewire\OfficeAdmin\WindowDesk;
use App\Models\Office;
use App\Models\QueueEntry;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Tests\TestCase;

class OfficeQueueDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_waiting_line_only_lists_todays_queue_entries(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 9, 9, 30, 0, 'Asia/Manila'));

        $office = $this->createOffice();
        $user = $this->createOfficeAdminUser($office);

        $this->createQueueEntry(
            office: $office,
            queueNumber: 'ACCT-001',
            status: QueueEntry::STATUS_WAITING,
            createdAt: Carbon::create(2026, 3, 8, 14, 40, 0, 'Asia/Manila')
        );

        $todayEntry = $this->createQueueEntry(
            office: $office,
            queueNumber: 'ACCT-001',
            status: QueueEntry::STATUS_WAITING,
            createdAt: Carbon::create(2026, 3, 9, 9, 15, 0, 'Asia/Manila')
        );

        $this->actingAs($user);

        Livewire::test(Dashboard::class, ['office' => $office])
            ->assertSee($todayEntry->queue_number)
            ->assertSee('Joined 09:15 AM')
            ->assertDontSee('Joined 02:40 PM');
    }

    public function test_call_next_ignores_waiting_entries_from_previous_days(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 9, 9, 30, 0, 'Asia/Manila'));

        $office = $this->createOffice();
        $user = $this->createOfficeAdminUser($office);

        $oldEntry = $this->createQueueEntry(
            office: $office,
            queueNumber: 'ACCT-001',
            status: QueueEntry::STATUS_WAITING,
            createdAt: Carbon::create(2026, 3, 8, 14, 40, 0, 'Asia/Manila')
        );

        $todayEntry = $this->createQueueEntry(
            office: $office,
            queueNumber: 'ACCT-001',
            status: QueueEntry::STATUS_WAITING,
            createdAt: Carbon::create(2026, 3, 9, 9, 15, 0, 'Asia/Manila')
        );

        $this->actingAs($user);

        Livewire::test(Dashboard::class, ['office' => $office])
            ->call('callNext');

        $this->assertDatabaseHas('queue_entries', [
            'id' => $todayEntry->id,
            'status' => QueueEntry::STATUS_SERVING,
        ]);

        $this->assertDatabaseHas('queue_entries', [
            'id' => $oldEntry->id,
            'status' => QueueEntry::STATUS_WAITING,
        ]);
    }

    public function test_service_window_tabs_ignore_stale_serving_entries_from_previous_days(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 4, 13, 10, 0, 0, 'Asia/Manila'));

        $office = $this->createOffice(
            serviceWindowCount: 5,
            name: 'Civil Registry',
            slug: 'civil-registry',
            prefix: 'CR',
            description: 'Municipal Local Civil Registry Office',
            attributes: [
                'service_window_labels' => [
                    1 => 'Birth Registration',
                    2 => 'Marriage Registration',
                    3 => 'Death Registration',
                    4 => 'PSA Request',
                    5 => 'Correction of Clerical Error',
                ],
            ]
        );

        $user = $this->createOfficeAdminUser($office);

        $staleServingEntry = $this->createQueueEntry(
            office: $office,
            queueNumber: 'CR-001',
            status: QueueEntry::STATUS_SERVING,
            createdAt: Carbon::create(2026, 4, 12, 15, 0, 0, 'Asia/Manila')
        );

        QueueEntry::whereKey($staleServingEntry)->update([
            'service_window_number' => 6,
            'called_at' => Carbon::create(2026, 4, 12, 15, 5, 0, 'Asia/Manila')
                ->setTimezone((string) config('app.timezone', 'UTC')),
        ]);

        $this->actingAs($user);

        Livewire::test(Dashboard::class, ['office' => $office])
            ->assertSee('Birth Registration')
            ->assertSee('Marriage Registration')
            ->assertSee('Death Registration')
            ->assertSee('PSA Request')
            ->assertSee('Correction of Clerical Error')
            ->assertDontSee('Window 6');
    }

    public function test_call_next_can_assign_the_next_ticket_to_another_available_window(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 9, 10, 0, 0, 'Asia/Manila'));

        $office = $this->createOffice(serviceWindowCount: 2);
        $user = $this->createOfficeAdminUser($office);

        $currentServing = $this->createQueueEntry(
            office: $office,
            queueNumber: 'ACCT-007',
            status: QueueEntry::STATUS_SERVING,
            createdAt: Carbon::create(2026, 3, 9, 9, 45, 0, 'Asia/Manila')
        );

        QueueEntry::whereKey($currentServing)->update([
            'service_window_number' => 1,
            'called_at' => Carbon::create(2026, 3, 9, 9, 46, 0, 'Asia/Manila')->setTimezone((string) config('app.timezone', 'UTC')),
        ]);

        $nextInline = $this->createQueueEntry(
            office: $office,
            queueNumber: 'ACCT-008',
            status: QueueEntry::STATUS_WAITING,
            createdAt: Carbon::create(2026, 3, 9, 9, 50, 0, 'Asia/Manila')
        );

        $this->actingAs($user);

        Livewire::test(Dashboard::class, ['office' => $office])
            ->call('callNext', 2)
            ->assertSee('Window 2')
            ->assertSee($nextInline->queue_number);

        $announcement = Cache::get('office-queue-announcement:'.$office->id);

        $this->assertIsArray($announcement);
        $this->assertSame('serving', $announcement['type'] ?? null);
        $this->assertSame($nextInline->queue_number, $announcement['queue_number'] ?? null);
        $this->assertSame(2, $announcement['service_window_number'] ?? null);
        $this->assertSame('Window 2', $announcement['service_window_label'] ?? null);

        $this->assertDatabaseHas('queue_entries', [
            'id' => $currentServing->id,
            'status' => QueueEntry::STATUS_SERVING,
            'service_window_number' => 1,
        ]);

        $this->assertDatabaseHas('queue_entries', [
            'id' => $nextInline->id,
            'status' => QueueEntry::STATUS_SERVING,
            'service_window_number' => 2,
        ]);
    }

    public function test_call_next_does_not_replace_an_active_ticket_on_the_same_window(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 9, 10, 0, 0, 'Asia/Manila'));

        $office = $this->createOffice();
        $user = $this->createOfficeAdminUser($office);

        $currentServing = $this->createQueueEntry(
            office: $office,
            queueNumber: 'ACCT-007',
            status: QueueEntry::STATUS_SERVING,
            createdAt: Carbon::create(2026, 3, 9, 9, 45, 0, 'Asia/Manila')
        );

        $nextInline = $this->createQueueEntry(
            office: $office,
            queueNumber: 'ACCT-008',
            status: QueueEntry::STATUS_WAITING,
            createdAt: Carbon::create(2026, 3, 9, 9, 50, 0, 'Asia/Manila')
        );

        $this->actingAs($user);

        Livewire::test(Dashboard::class, ['office' => $office])
            ->call('callNext', 1)
            ->assertSee('Window 1 is still handling an active ticket.');

        $this->assertDatabaseHas('queue_entries', [
            'id' => $currentServing->id,
            'status' => QueueEntry::STATUS_SERVING,
        ]);

        $this->assertDatabaseHas('queue_entries', [
            'id' => $nextInline->id,
            'status' => QueueEntry::STATUS_WAITING,
        ]);
    }

    public function test_call_next_prioritizes_senior_pregnant_tickets_before_regular_waiting_entries(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 9, 10, 0, 0, 'Asia/Manila'));

        $office = $this->createOffice();
        $user = $this->createOfficeAdminUser($office);

        $regularEntry = $this->createQueueEntry(
            office: $office,
            queueNumber: 'ACCT-010',
            status: QueueEntry::STATUS_WAITING,
            createdAt: Carbon::create(2026, 3, 9, 9, 40, 0, 'Asia/Manila')
        );

        $priorityEntry = $this->createQueueEntry(
            office: $office,
            queueNumber: 'ACCT-011',
            status: QueueEntry::STATUS_WAITING,
            createdAt: Carbon::create(2026, 3, 9, 9, 45, 0, 'Asia/Manila'),
            clientType: QueueEntry::TYPE_SENIOR_PREGNANT
        );

        $this->actingAs($user);

        Livewire::test(Dashboard::class, ['office' => $office])
            ->call('callNext')
            ->assertSee($priorityEntry->queue_number)
            ->assertSee('Now serving ACCT-011 at Window 1.');

        $this->assertDatabaseHas('queue_entries', [
            'id' => $priorityEntry->id,
            'status' => QueueEntry::STATUS_SERVING,
        ]);

        $this->assertDatabaseHas('queue_entries', [
            'id' => $regularEntry->id,
            'status' => QueueEntry::STATUS_WAITING,
        ]);
    }

    public function test_clear_transaction_removes_only_todays_waiting_line_entries(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 9, 9, 30, 0, 'Asia/Manila'));

        $office = $this->createOffice();
        $user = $this->createOfficeAdminUser($office);

        $oldWaiting = $this->createQueueEntry(
            office: $office,
            queueNumber: 'ACCT-001',
            status: QueueEntry::STATUS_WAITING,
            createdAt: Carbon::create(2026, 3, 8, 14, 40, 0, 'Asia/Manila')
        );

        $todayWaitingOne = $this->createQueueEntry(
            office: $office,
            queueNumber: 'ACCT-002',
            status: QueueEntry::STATUS_WAITING,
            createdAt: Carbon::create(2026, 3, 9, 9, 15, 0, 'Asia/Manila')
        );

        $todayWaitingTwo = $this->createQueueEntry(
            office: $office,
            queueNumber: 'ACCT-003',
            status: QueueEntry::STATUS_WAITING,
            createdAt: Carbon::create(2026, 3, 9, 9, 16, 0, 'Asia/Manila')
        );

        $todayServing = $this->createQueueEntry(
            office: $office,
            queueNumber: 'ACCT-004',
            status: QueueEntry::STATUS_SERVING,
            createdAt: Carbon::create(2026, 3, 9, 9, 17, 0, 'Asia/Manila')
        );

        $todayCompleted = $this->createQueueEntry(
            office: $office,
            queueNumber: 'ACCT-005',
            status: QueueEntry::STATUS_COMPLETED,
            createdAt: Carbon::create(2026, 3, 9, 9, 5, 0, 'Asia/Manila')
        );

        $oldCompleted = $this->createQueueEntry(
            office: $office,
            queueNumber: 'ACCT-006',
            status: QueueEntry::STATUS_COMPLETED,
            createdAt: Carbon::create(2026, 3, 8, 9, 5, 0, 'Asia/Manila')
        );

        $this->setServedAt($todayCompleted, Carbon::create(2026, 3, 9, 9, 25, 0, 'Asia/Manila'));
        $this->setServedAt($oldCompleted, Carbon::create(2026, 3, 8, 9, 25, 0, 'Asia/Manila'));

        $this->actingAs($user);

        Livewire::test(Dashboard::class, ['office' => $office])
            ->call('clearTransaction');

        $this->assertDatabaseMissing('queue_entries', ['id' => $todayWaitingOne->id]);
        $this->assertDatabaseMissing('queue_entries', ['id' => $todayWaitingTwo->id]);

        $this->assertDatabaseHas('queue_entries', [
            'id' => $oldWaiting->id,
            'status' => QueueEntry::STATUS_WAITING,
        ]);

        $this->assertDatabaseHas('queue_entries', [
            'id' => $todayServing->id,
            'status' => QueueEntry::STATUS_SERVING,
        ]);

        $this->assertDatabaseHas('queue_entries', [
            'id' => $todayCompleted->id,
        ]);

        $this->assertDatabaseMissing('queue_entries', [
            'id' => $todayCompleted->id,
            'recent_transaction_cleared_at' => null,
        ]);

        $this->assertDatabaseHas('queue_entries', [
            'id' => $oldCompleted->id,
            'recent_transaction_cleared_at' => null,
        ]);
    }

    public function test_generic_office_admin_dashboard_shows_sidebar_dashboard_and_quick_actions(): void
    {
        $office = $this->createOffice(
            name: 'Citizen Center',
            slug: 'citizen-center',
            prefix: 'CCEN',
            description: 'Citizen Center services'
        );
        $role = Role::create([
            'name' => 'Office Admin',
            'slug' => 'office_admin',
            'description' => 'Office-specific administrator',
        ]);
        $user = User::factory()->create([
            'role_id' => $role->id,
            'office_id' => $office->id,
        ]);

        $this->actingAs($user)
            ->get(route('office.dashboard', $office->slug))
            ->assertOk()
            ->assertSee('Menu')
            ->assertSee('Dashboard')
            ->assertSee('Reports')
            ->assertSee('Quick Actions')
            ->assertSee('Clear Waiting Line')
            ->assertSee('Reset Queue Number')
            ->assertSee('Citizen Center Queue Operations Desk')
            ->assertSee('Service Window Tabs')
            ->assertSee('Window 1')
            ->assertSee(route('office.window', ['office' => $office->slug, 'windowNumber' => 1]), false);
    }

    public function test_office_admin_can_reset_queue_numbering_for_today(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 9, 11, 0, 0, 'Asia/Manila'));

        $office = $this->createOffice();
        $office->update(['next_number' => 12]);

        $user = $this->createOfficeAdminUser($office);

        $yesterdayEntry = $this->createQueueEntry(
            office: $office,
            queueNumber: 'ACCT-009',
            status: QueueEntry::STATUS_WAITING,
            createdAt: Carbon::create(2026, 3, 8, 15, 0, 0, 'Asia/Manila')
        );

        $todayEntry = $this->createQueueEntry(
            office: $office,
            queueNumber: 'ACCT-010',
            status: QueueEntry::STATUS_WAITING,
            createdAt: Carbon::create(2026, 3, 9, 10, 45, 0, 'Asia/Manila')
        );

        $this->actingAs($user);

        Livewire::test(Dashboard::class, ['office' => $office])
            ->call('resetTickets')
            ->assertSee('Tickets reset. The next generated number will start from 001.');

        $this->assertDatabaseMissing('queue_entries', ['id' => $todayEntry->id]);
        $this->assertDatabaseHas('queue_entries', ['id' => $yesterdayEntry->id]);
        $this->assertDatabaseHas('offices', [
            'id' => $office->id,
            'next_number' => 1,
        ]);
    }

    public function test_menro_dashboard_shows_clear_waiting_line_quick_action(): void
    {
        $office = $this->createOffice(
            serviceWindowCount: count(Office::MENRO_DEFAULT_SERVICE_WINDOW_LABELS),
            attributes: [
                'name' => 'MENRO',
                'slug' => 'menro',
                'prefix' => 'MENRO',
                'description' => 'Municipal Environment and Natural Resources Office',
                'service_window_labels' => Office::MENRO_DEFAULT_SERVICE_WINDOW_LABELS,
            ]
        );

        $user = $this->createOfficeAdminUser($office);

        $this->actingAs($user);

        Livewire::test(Dashboard::class, ['office' => $office])
            ->assertSee('Quick Actions')
            ->assertSee('Clear Waiting Line');
    }

    public function test_menro_dashboard_shows_configured_window_tab_buttons(): void
    {
        $office = $this->createOffice(
            serviceWindowCount: count(Office::MENRO_DEFAULT_SERVICE_WINDOW_LABELS),
            name: 'MENRO',
            slug: 'menro',
            prefix: 'MENRO',
            description: 'Municipal Environment and Natural Resources Office',
            attributes: [
                'service_window_labels' => Office::MENRO_DEFAULT_SERVICE_WINDOW_LABELS,
            ]
        );

        $user = $this->createOfficeAdminUser($office);

        $this->actingAs($user)
            ->get(route('office.dashboard', $office->slug))
            ->assertOk()
            ->assertSee('Service Window Tabs')
            ->assertSee('Addressing Environmental Concerns')
            ->assertSee('Issuance of CLIVE Card')
            ->assertSee('Application for Environmental Clearance')
            ->assertSee(route('office.window', ['office' => $office->slug, 'windowNumber' => 2]), false);
    }

    public function test_bplo_dashboard_shows_window_tab_buttons(): void
    {
        $office = $this->createOffice(
            serviceWindowCount: count(Office::BPLO_DEFAULT_SERVICE_WINDOW_LABELS),
            name: 'Business Permits',
            slug: 'business-permits',
            prefix: 'BPLO',
            description: 'Business permit services',
            attributes: [
                'service_window_labels' => Office::BPLO_DEFAULT_SERVICE_WINDOW_LABELS,
            ]
        );

        $user = $this->createOfficeAdminUser($office);

        $this->actingAs($user)
            ->get(route('office.dashboard', $office->slug))
            ->assertOk()
            ->assertSee('Service Window Tabs')
            ->assertSee('Business Permit Application')
            ->assertSee('Request for Certifications')
            ->assertSee(route('office.window', ['office' => $office->slug, 'windowNumber' => 1]), false);
    }

    public function test_multi_window_office_dashboard_shows_window_tabs_for_each_window(): void
    {
        $office = $this->createOffice(
            serviceWindowCount: 3,
            name: 'Treasury',
            slug: 'treasury',
            prefix: 'TRSY',
            description: 'Treasury services'
        );

        $user = $this->createOfficeAdminUser($office);

        $this->actingAs($user)
            ->get(route('office.dashboard', $office->slug))
            ->assertOk()
            ->assertSee('Service Window Tabs')
            ->assertSee('Teller 1')
            ->assertSee('Teller 2')
            ->assertSee('Teller 3')
            ->assertSee(route('office.window', ['office' => $office->slug, 'windowNumber' => 3]), false);
    }

    public function test_office_window_route_renders_the_requested_window_tab(): void
    {
        $office = $this->createOffice(
            serviceWindowCount: 2,
            name: 'Citizen Center',
            slug: 'citizen-center',
            prefix: 'CCEN',
            description: 'Citizen Center services'
        );

        $user = $this->createOfficeAdminUser($office);

        $this->actingAs($user)
            ->get('/office/citizen-center/window1')
            ->assertOk()
            ->assertSee('Citizen Center Window 1')
            ->assertSee('Call Next')
            ->assertSee('Back to Operations Desk');
    }

    public function test_window_tab_call_next_assigns_the_ticket_to_the_requested_window(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 23, 10, 0, 0, 'Asia/Manila'));

        $office = $this->createOffice(
            serviceWindowCount: 2,
            name: 'Citizen Center',
            slug: 'citizen-center',
            prefix: 'CCEN',
            description: 'Citizen Center services'
        );

        $user = $this->createOfficeAdminUser($office);

        $waitingEntry = $this->createQueueEntry(
            office: $office,
            queueNumber: 'CCEN-001',
            status: QueueEntry::STATUS_WAITING,
            createdAt: Carbon::create(2026, 3, 23, 9, 45, 0, 'Asia/Manila')
        );

        $this->actingAs($user);

        Livewire::test(WindowDesk::class, ['office' => $office, 'windowNumber' => 1])
            ->call('callNext')
            ->assertSee('Now serving CCEN-001 at Window 1.')
            ->assertSee('CCEN-001');

        $this->assertDatabaseHas('queue_entries', [
            'id' => $waitingEntry->id,
            'status' => QueueEntry::STATUS_SERVING,
            'service_window_number' => 1,
        ]);
    }

    public function test_window_tab_shows_elapsed_service_time_for_the_active_ticket(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 4, 13, 10, 0, 0, 'Asia/Manila'));

        $office = $this->createOffice(
            serviceWindowCount: 1,
            name: 'MTO',
            slug: 'mto',
            prefix: 'MTO',
            description: 'Municipal Treasurer\'s Office'
        );

        $user = $this->createOfficeAdminUser($office);

        $entry = $this->createQueueEntry(
            office: $office,
            queueNumber: 'MTO-001',
            status: QueueEntry::STATUS_SERVING,
            createdAt: Carbon::create(2026, 4, 13, 9, 45, 0, 'Asia/Manila')
        );

        QueueEntry::whereKey($entry)->update([
            'service_window_number' => 1,
            'called_at' => Carbon::create(2026, 4, 13, 9, 50, 0, 'Asia/Manila')
                ->setTimezone((string) config('app.timezone', 'UTC')),
        ]);

        $expectedElapsedTime = $entry->fresh()->serviceDurationLabel();

        $this->actingAs($user);

        Livewire::test(WindowDesk::class, ['office' => $office, 'windowNumber' => 1])
            ->assertSee('Elapsed Time')
            ->assertSee($expectedElapsedTime)
            ->assertSee('Starts on call and stops once the transaction is completed.');
    }

    public function test_treasury_window_tab_calls_only_the_service_assigned_to_that_window(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 26, 10, 0, 0, 'Asia/Manila'));

        $office = $this->createOffice(
            serviceWindowCount: count(Office::TREASURY_DEFAULT_SERVICE_WINDOW_LABELS),
            name: 'Treasury',
            slug: 'treasury',
            prefix: 'TRSY',
            description: 'Treasury services',
            attributes: [
                'service_window_labels' => Office::TREASURY_DEFAULT_SERVICE_WINDOW_LABELS,
            ]
        );

        $user = $this->createOfficeAdminUser($office);

        $marketChargesEntry = $this->createQueueEntry(
            office: $office,
            queueNumber: 'TRSY-001',
            status: QueueEntry::STATUS_WAITING,
            createdAt: Carbon::create(2026, 3, 26, 9, 45, 0, 'Asia/Manila'),
            serviceKey: 'market_charges'
        );

        $releaseCashEntry = $this->createQueueEntry(
            office: $office,
            queueNumber: 'TRSY-002',
            status: QueueEntry::STATUS_WAITING,
            createdAt: Carbon::create(2026, 3, 26, 9, 46, 0, 'Asia/Manila'),
            serviceKey: 'release_of_disbursement_of_cash'
        );

        $this->actingAs($user);

        Livewire::test(WindowDesk::class, ['office' => $office, 'windowNumber' => 10])
            ->call('callNext')
            ->assertSee('Now serving TRSY-002 at Window 1.')
            ->assertSee('Release of Disbursement of Cash')
            ->assertDontSee('TRSY-001');

        $this->assertDatabaseHas('queue_entries', [
            'id' => $releaseCashEntry->id,
            'status' => QueueEntry::STATUS_SERVING,
            'service_window_number' => 10,
        ]);

        $this->assertDatabaseHas('queue_entries', [
            'id' => $marketChargesEntry->id,
            'status' => QueueEntry::STATUS_WAITING,
            'service_window_number' => null,
        ]);
    }

    public function test_mto_frontline_window_tabs_share_the_same_waiting_pool(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 4, 13, 10, 0, 0, 'Asia/Manila'));

        $office = $this->createOffice(
            serviceWindowCount: count(Office::TREASURY_DEFAULT_SERVICE_WINDOW_LABELS),
            name: 'MTO',
            slug: 'mto',
            prefix: 'MTO',
            description: 'Municipal Treasurer\'s Office',
            attributes: [
                'service_window_labels' => Office::TREASURY_DEFAULT_SERVICE_WINDOW_LABELS,
            ]
        );

        $user = $this->createOfficeAdminUser($office);

        $businessTaxesEntry = $this->createQueueEntry(
            office: $office,
            queueNumber: 'MTO-001',
            status: QueueEntry::STATUS_WAITING,
            createdAt: Carbon::create(2026, 4, 13, 9, 45, 0, 'Asia/Manila'),
            serviceKey: 'business_taxes_fees_charges'
        );

        $marriageLicenseEntry = $this->createQueueEntry(
            office: $office,
            queueNumber: 'MTO-002',
            status: QueueEntry::STATUS_WAITING,
            createdAt: Carbon::create(2026, 4, 13, 9, 46, 0, 'Asia/Manila'),
            serviceKey: 'marriage_license'
        );

        $marketChargesEntry = $this->createQueueEntry(
            office: $office,
            queueNumber: 'MTO-003',
            status: QueueEntry::STATUS_WAITING,
            createdAt: Carbon::create(2026, 4, 13, 9, 47, 0, 'Asia/Manila'),
            serviceKey: 'market_charges'
        );

        $releaseCashEntry = $this->createQueueEntry(
            office: $office,
            queueNumber: 'MTO-004',
            status: QueueEntry::STATUS_WAITING,
            createdAt: Carbon::create(2026, 4, 13, 9, 48, 0, 'Asia/Manila'),
            serviceKey: 'release_of_disbursement_of_cash'
        );

        $this->actingAs($user);

        Livewire::test(WindowDesk::class, ['office' => $office, 'windowNumber' => 5])
            ->assertSee('MTO-001')
            ->assertSee('MTO-002')
            ->assertSee('MTO-003')
            ->assertDontSee('MTO-004')
            ->call('callNext')
            ->assertSee('Now serving MTO-001 at Teller 5.')
            ->assertSee('Business Taxes, Fees and Charges');

        $this->assertDatabaseHas('queue_entries', [
            'id' => $businessTaxesEntry->id,
            'status' => QueueEntry::STATUS_SERVING,
            'service_window_number' => 5,
        ]);

        $this->assertDatabaseHas('queue_entries', [
            'id' => $marriageLicenseEntry->id,
            'status' => QueueEntry::STATUS_WAITING,
            'service_window_number' => null,
        ]);

        $this->assertDatabaseHas('queue_entries', [
            'id' => $marketChargesEntry->id,
            'status' => QueueEntry::STATUS_WAITING,
            'service_window_number' => null,
        ]);

        $this->assertDatabaseHas('queue_entries', [
            'id' => $releaseCashEntry->id,
            'status' => QueueEntry::STATUS_WAITING,
            'service_window_number' => null,
        ]);
    }

    public function test_mto_custom_frontline_tabs_share_the_same_waiting_pool(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 4, 13, 10, 0, 0, 'Asia/Manila'));

        $office = $this->createOffice(
            serviceWindowCount: 4,
            name: 'MTO',
            slug: 'mto',
            prefix: 'MTO',
            description: 'Municipal Treasurer\'s Office',
            attributes: [
                'service_window_labels' => [
                    1 => 'Business Taxes, Fees and Charges',
                    2 => 'Real Property Taxes',
                    3 => 'Marriage License',
                    4 => 'Market Charges',
                ],
            ]
        );

        $user = $this->createOfficeAdminUser($office);

        $businessTaxesEntry = $this->createQueueEntry(
            office: $office,
            queueNumber: 'MTO-001',
            status: QueueEntry::STATUS_WAITING,
            createdAt: Carbon::create(2026, 4, 13, 9, 45, 0, 'Asia/Manila'),
            serviceKey: 'service_window_1'
        );

        $this->actingAs($user);

        Livewire::test(WindowDesk::class, ['office' => $office, 'windowNumber' => 3])
            ->assertSee('MTO-001')
            ->assertSee('Business Taxes, Fees and Charges')
            ->call('callNext')
            ->assertSee('Now serving MTO-001 at Window 3.');

        $this->assertDatabaseHas('queue_entries', [
            'id' => $businessTaxesEntry->id,
            'status' => QueueEntry::STATUS_SERVING,
            'service_window_number' => 3,
        ]);
    }

    public function test_completing_a_window_transaction_saves_office_service_and_window_snapshots(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 26, 10, 5, 0, 'Asia/Manila'));

        $office = $this->createOffice(
            serviceWindowCount: count(Office::TREASURY_DEFAULT_SERVICE_WINDOW_LABELS),
            name: 'Treasury',
            slug: 'treasury',
            prefix: 'TRSY',
            description: 'Treasury services',
            attributes: [
                'service_window_labels' => Office::TREASURY_DEFAULT_SERVICE_WINDOW_LABELS,
            ]
        );

        $user = $this->createOfficeAdminUser($office);

        $releaseCashEntry = $this->createQueueEntry(
            office: $office,
            queueNumber: 'TRSY-010',
            status: QueueEntry::STATUS_WAITING,
            createdAt: Carbon::create(2026, 3, 26, 9, 55, 0, 'Asia/Manila'),
            serviceKey: 'release_of_disbursement_of_cash'
        );

        $this->actingAs($user);

        Livewire::test(WindowDesk::class, ['office' => $office, 'windowNumber' => 10])
            ->call('callNext')
            ->call('complete', $releaseCashEntry->id);

        $this->assertDatabaseHas('queue_entries', [
            'id' => $releaseCashEntry->id,
            'status' => QueueEntry::STATUS_COMPLETED,
            'office_name' => 'Treasury',
            'service_label' => 'Release of Disbursement of Cash',
            'service_window_number' => 10,
            'service_window_label' => 'Window 1',
        ]);
    }

    public function test_civil_registry_window_tab_calls_only_the_service_assigned_to_that_window(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 30, 10, 0, 0, 'Asia/Manila'));

        $office = $this->createOffice(
            serviceWindowCount: count(Office::CIVIL_REGISTRY_DEFAULT_SERVICE_WINDOW_LABELS),
            name: 'Civil Registry',
            slug: 'civil-registry',
            prefix: 'CR',
            description: 'Municipal Local Civil Registry Office',
            attributes: [
                'service_window_labels' => Office::CIVIL_REGISTRY_DEFAULT_SERVICE_WINDOW_LABELS,
            ]
        );

        $user = $this->createOfficeAdminUser($office);

        $birthEntry = $this->createQueueEntry(
            office: $office,
            queueNumber: 'CR-001',
            status: QueueEntry::STATUS_WAITING,
            createdAt: Carbon::create(2026, 3, 30, 9, 45, 0, 'Asia/Manila'),
            serviceKey: 'window_1_a'
        );

        $courtOrderEntry = $this->createQueueEntry(
            office: $office,
            queueNumber: 'CR-002',
            status: QueueEntry::STATUS_WAITING,
            createdAt: Carbon::create(2026, 3, 30, 9, 46, 0, 'Asia/Manila'),
            serviceKey: 'window_4_b'
        );

        $this->actingAs($user);

        Livewire::test(WindowDesk::class, ['office' => $office, 'windowNumber' => 6])
            ->call('callNext')
            ->assertSee('Now serving CR-002 at Window 4-B.')
            ->assertSee('Correction of Clerical Error')
            ->assertDontSee('CR-001');

        $this->assertDatabaseHas('queue_entries', [
            'id' => $courtOrderEntry->id,
            'status' => QueueEntry::STATUS_SERVING,
            'service_window_number' => 6,
        ]);

        $this->assertDatabaseHas('queue_entries', [
            'id' => $birthEntry->id,
            'status' => QueueEntry::STATUS_WAITING,
            'service_window_number' => null,
        ]);
    }

    public function test_bplo_window_tab_calls_only_the_service_assigned_to_that_window(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 31, 10, 0, 0, 'Asia/Manila'));

        $office = $this->createOffice(
            serviceWindowCount: count(Office::BPLO_DEFAULT_SERVICE_WINDOW_LABELS),
            name: 'Business Permits',
            slug: 'business-permits',
            prefix: 'BPLO',
            description: 'Business Permits and Licensing Office',
            attributes: [
                'service_window_labels' => Office::BPLO_DEFAULT_SERVICE_WINDOW_LABELS,
            ]
        );

        $user = $this->createOfficeAdminUser($office);

        $permitEntry = $this->createQueueEntry(
            office: $office,
            queueNumber: 'BPLO-001',
            status: QueueEntry::STATUS_WAITING,
            createdAt: Carbon::create(2026, 3, 31, 9, 45, 0, 'Asia/Manila'),
            serviceKey: 'business_permit_application'
        );

        $certificationEntry = $this->createQueueEntry(
            office: $office,
            queueNumber: 'BPLO-002',
            status: QueueEntry::STATUS_WAITING,
            createdAt: Carbon::create(2026, 3, 31, 9, 46, 0, 'Asia/Manila'),
            serviceKey: 'request_for_certifications'
        );

        $this->actingAs($user);

        Livewire::test(WindowDesk::class, ['office' => $office, 'windowNumber' => 2])
            ->call('callNext')
            ->assertSee('Now serving BPLO-002 at Window 2.')
            ->assertSee('Request for Certifications')
            ->assertDontSee('BPLO-001');

        $this->assertDatabaseHas('queue_entries', [
            'id' => $certificationEntry->id,
            'status' => QueueEntry::STATUS_SERVING,
            'service_window_number' => 2,
        ]);

        $this->assertDatabaseHas('queue_entries', [
            'id' => $permitEntry->id,
            'status' => QueueEntry::STATUS_WAITING,
            'service_window_number' => null,
        ]);
    }

    public function test_hrmo_window_tab_calls_only_the_service_assigned_to_that_window(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 4, 6, 10, 0, 0, 'Asia/Manila'));

        $office = $this->createOffice(
            serviceWindowCount: count(Office::HRMO_DEFAULT_SERVICE_WINDOW_LABELS),
            name: 'HRMO',
            slug: 'hrmo',
            prefix: 'HRMO',
            description: 'Human Resource Management Office',
            attributes: [
                'service_window_labels' => Office::HRMO_DEFAULT_SERVICE_WINDOW_LABELS,
            ]
        );

        $user = $this->createOfficeAdminUser($office);

        $recruitmentEntry = $this->createQueueEntry(
            office: $office,
            queueNumber: 'HRMO-001',
            status: QueueEntry::STATUS_WAITING,
            createdAt: Carbon::create(2026, 4, 6, 9, 45, 0, 'Asia/Manila'),
            serviceKey: 'recruitment_selection_services'
        );

        $artaEntry = $this->createQueueEntry(
            office: $office,
            queueNumber: 'HRMO-002',
            status: QueueEntry::STATUS_WAITING,
            createdAt: Carbon::create(2026, 4, 6, 9, 46, 0, 'Asia/Manila'),
            serviceKey: 'arta_identification_card'
        );

        $this->actingAs($user);

        Livewire::test(WindowDesk::class, ['office' => $office, 'windowNumber' => 4])
            ->call('callNext')
            ->assertSee('Now serving HRMO-002 at Window 4.')
            ->assertSee('Request for Anti-Red Tape Act (ARTA) Identification Card')
            ->assertDontSee('HRMO-001');

        $this->assertDatabaseHas('queue_entries', [
            'id' => $artaEntry->id,
            'status' => QueueEntry::STATUS_SERVING,
            'service_window_number' => 4,
        ]);

        $this->assertDatabaseHas('queue_entries', [
            'id' => $recruitmentEntry->id,
            'status' => QueueEntry::STATUS_WAITING,
            'service_window_number' => null,
        ]);
    }

    public function test_menro_window_tab_calls_only_the_service_assigned_to_that_window(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 31, 10, 15, 0, 'Asia/Manila'));

        $office = $this->createOffice(
            serviceWindowCount: count(Office::MENRO_DEFAULT_SERVICE_WINDOW_LABELS),
            name: 'MENRO',
            slug: 'menro',
            prefix: 'MENRO',
            description: 'Municipal Environment and Natural Resources Office',
            attributes: [
                'service_window_labels' => Office::MENRO_DEFAULT_SERVICE_WINDOW_LABELS,
            ]
        );

        $user = $this->createOfficeAdminUser($office);

        $concernEntry = $this->createQueueEntry(
            office: $office,
            queueNumber: 'MENRO-001',
            status: QueueEntry::STATUS_WAITING,
            createdAt: Carbon::create(2026, 3, 31, 9, 45, 0, 'Asia/Manila'),
            serviceKey: 'addressing_environmental_concerns'
        );

        $cliveEntry = $this->createQueueEntry(
            office: $office,
            queueNumber: 'MENRO-002',
            status: QueueEntry::STATUS_WAITING,
            createdAt: Carbon::create(2026, 3, 31, 9, 46, 0, 'Asia/Manila'),
            serviceKey: 'issuance_of_clive_card'
        );

        $this->actingAs($user);

        Livewire::test(WindowDesk::class, ['office' => $office, 'windowNumber' => 2])
            ->call('callNext')
            ->assertSee('Now serving MENRO-002 at Window 2.')
            ->assertSee('Issuance of CLIVE Card')
            ->assertDontSee('MENRO-001');

        $this->assertDatabaseHas('queue_entries', [
            'id' => $cliveEntry->id,
            'status' => QueueEntry::STATUS_SERVING,
            'service_window_number' => 2,
        ]);

        $this->assertDatabaseHas('queue_entries', [
            'id' => $concernEntry->id,
            'status' => QueueEntry::STATUS_WAITING,
            'service_window_number' => null,
        ]);
    }

    private function createOffice(
        int $serviceWindowCount = 1,
        string $name = 'Accounting Office',
        string $slug = 'accounting',
        string $prefix = 'ACCT',
        string $description = 'Accounting services',
        array $attributes = []
    ): Office
    {
        return Office::create(array_merge([
            'name' => $name,
            'slug' => $slug,
            'prefix' => $prefix,
            'description' => $description,
            'next_number' => 1,
            'service_window_count' => $serviceWindowCount,
            'tickets_accommodated_total' => 0,
            'is_active' => true,
        ], $attributes));
    }

    private function createOfficeAdminUser(Office $office): User
    {
        $role = Role::firstOrCreate(
            ['slug' => 'office_admin'],
            [
                'name' => 'Office Admin',
                'description' => 'Office-specific administrator',
            ]
        );

        return User::factory()->create([
            'role_id' => $role->id,
            'office_id' => $office->id,
        ]);
    }

    private function createQueueEntry(
        Office $office,
        string $queueNumber,
        string $status,
        Carbon $createdAt,
        string $clientType = QueueEntry::TYPE_REGULAR,
        ?string $serviceKey = null
    ): QueueEntry
    {
        $entry = QueueEntry::create([
            'office_id' => $office->id,
            'queue_number' => $queueNumber,
            'client_type' => $clientType,
            'service_key' => $serviceKey,
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
        ]);
    }
}
