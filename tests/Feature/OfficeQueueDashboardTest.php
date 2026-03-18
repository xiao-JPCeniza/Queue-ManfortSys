<?php

namespace Tests\Feature;

use App\Livewire\OfficeAdmin\Dashboard;
use App\Models\Office;
use App\Models\QueueEntry;
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
        $user = User::factory()->create(['office_id' => $office->id]);

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
        $user = User::factory()->create(['office_id' => $office->id]);

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

    public function test_call_next_can_assign_the_next_ticket_to_another_available_window(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 9, 10, 0, 0, 'Asia/Manila'));

        $office = $this->createOffice(serviceWindowCount: 2);
        $user = User::factory()->create(['office_id' => $office->id]);

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
        $user = User::factory()->create(['office_id' => $office->id]);

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
        $user = User::factory()->create(['office_id' => $office->id]);

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
            ->assertSee('Senior / Pregnant');

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
        $user = User::factory()->create(['office_id' => $office->id]);

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

    private function createOffice(int $serviceWindowCount = 1): Office
    {
        return Office::create([
            'name' => 'Accounting Office',
            'slug' => 'accounting',
            'prefix' => 'ACCT',
            'description' => 'Accounting services',
            'next_number' => 1,
            'service_window_count' => $serviceWindowCount,
            'tickets_accommodated_total' => 0,
            'is_active' => true,
        ]);
    }

    private function createQueueEntry(
        Office $office,
        string $queueNumber,
        string $status,
        Carbon $createdAt,
        string $clientType = QueueEntry::TYPE_REGULAR
    ): QueueEntry
    {
        $entry = QueueEntry::create([
            'office_id' => $office->id,
            'queue_number' => $queueNumber,
            'client_type' => $clientType,
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
