<?php

namespace Tests\Feature;

use App\Livewire\OfficeAdmin\AllOfficesMonitor;
use App\Models\Office;
use App\Models\QueueEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class AllOfficesMonitorTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_it_displays_only_the_featured_office_queue_on_the_tv_monitor(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 9, 10, 0, 0, 'Asia/Manila'));

        $hrmo = $this->createOffice('HRMO', 'hrmo', 'HRMO');
        $treasury = $this->createOffice('Treasury', 'treasury', 'TRSY');
        $mswdo = $this->createOffice('MSWDO', 'mswdo', 'MSWDO');

        $hrmoServing = $this->createQueueEntry(
            office: $hrmo,
            queueNumber: 'HRMO-001',
            status: QueueEntry::STATUS_SERVING,
            createdAt: Carbon::create(2026, 3, 9, 9, 10, 0, 'Asia/Manila')
        );

        $this->setCalledAt($hrmoServing, Carbon::create(2026, 3, 9, 9, 15, 0, 'Asia/Manila'));

        $this->createQueueEntry(
            office: $hrmo,
            queueNumber: 'HRMO-002',
            status: QueueEntry::STATUS_WAITING,
            createdAt: Carbon::create(2026, 3, 9, 9, 18, 0, 'Asia/Manila')
        );

        $this->createQueueEntry(
            office: $treasury,
            queueNumber: 'TRSY-001',
            status: QueueEntry::STATUS_WAITING,
            createdAt: Carbon::create(2026, 3, 9, 9, 20, 0, 'Asia/Manila')
        );

        $this->createQueueEntry(
            office: $mswdo,
            queueNumber: 'MSWDO-001',
            status: QueueEntry::STATUS_WAITING,
            createdAt: Carbon::create(2026, 3, 8, 9, 20, 0, 'Asia/Manila')
        );

        Livewire::test(AllOfficesMonitor::class)
            ->assertSee('HRMO')
            ->assertSee('HRMO-001')
            ->assertSee('HRMO-002')
            ->assertDontSee('Treasury')
            ->assertDontSee('TRSY-001')
            ->assertDontSee('MSWDO')
            ->assertDontSee('MSWDO-001');
    }

    public function test_it_shows_recent_transactions_for_the_featured_office_only(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 9, 10, 0, 0, 'Asia/Manila'));

        $hrmo = $this->createOffice('HRMO', 'hrmo', 'HRMO');
        $treasury = $this->createOffice('Treasury', 'treasury', 'TRSY');

        $hrmoServing = $this->createQueueEntry(
            office: $hrmo,
            queueNumber: 'HRMO-001',
            status: QueueEntry::STATUS_SERVING,
            createdAt: Carbon::create(2026, 3, 9, 8, 35, 0, 'Asia/Manila')
        );

        $treasuryWaiting = $this->createQueueEntry(
            office: $treasury,
            queueNumber: 'TRSY-001',
            status: QueueEntry::STATUS_WAITING,
            createdAt: Carbon::create(2026, 3, 9, 8, 40, 0, 'Asia/Manila')
        );

        $this->setCalledAt($hrmoServing, Carbon::create(2026, 3, 9, 9, 5, 0, 'Asia/Manila'));

        $hrmoCompleted = $this->createQueueEntry(
            office: $hrmo,
            queueNumber: 'HRMO-009',
            status: QueueEntry::STATUS_COMPLETED,
            createdAt: Carbon::create(2026, 3, 9, 8, 45, 0, 'Asia/Manila')
        );

        $treasurySkipped = $this->createQueueEntry(
            office: $treasury,
            queueNumber: 'TRSY-004',
            status: QueueEntry::STATUS_NOT_SERVED,
            createdAt: Carbon::create(2026, 3, 9, 8, 55, 0, 'Asia/Manila')
        );

        $this->setServedAt($hrmoCompleted, Carbon::create(2026, 3, 9, 9, 5, 0, 'Asia/Manila'));
        $this->setServedAt($treasurySkipped, Carbon::create(2026, 3, 9, 9, 10, 0, 'Asia/Manila'));

        $html = Livewire::test(AllOfficesMonitor::class)->html();

        $this->assertSame(1, substr_count($html, 'Recent Transactions Today'));
        $this->assertStringContainsString('HRMO', $html);
        $this->assertStringContainsString('HRMO-009', $html);
        $this->assertStringNotContainsString('TRSY-004', $html);
    }

    public function test_it_prioritizes_the_most_recently_called_office_at_the_top_of_the_monitor(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 9, 10, 0, 0, 'Asia/Manila'));

        $accounting = $this->createOffice('Accounting', 'accounting', 'ACCT');
        $treasury = $this->createOffice('Treasury', 'treasury', 'TRSY');

        $accountingServing = $this->createQueueEntry(
            office: $accounting,
            queueNumber: 'ACCT-001',
            status: QueueEntry::STATUS_SERVING,
            createdAt: Carbon::create(2026, 3, 9, 8, 45, 0, 'Asia/Manila')
        );

        $treasuryServing = $this->createQueueEntry(
            office: $treasury,
            queueNumber: 'TRSY-001',
            status: QueueEntry::STATUS_SERVING,
            createdAt: Carbon::create(2026, 3, 9, 8, 50, 0, 'Asia/Manila')
        );

        $this->setCalledAt($accountingServing, Carbon::create(2026, 3, 9, 9, 5, 0, 'Asia/Manila'));
        $this->setCalledAt($treasuryServing, Carbon::create(2026, 3, 9, 9, 10, 0, 'Asia/Manila'));

        Cache::put('office-queue-announcement:'.$accounting->id, [
            'id' => 'announcement-accounting',
            'type' => 'serving',
            'queue_number' => 'ACCT-001',
            'triggered_at' => Carbon::create(2026, 3, 9, 9, 30, 0, 'Asia/Manila')->toIso8601String(),
        ], now()->addMinutes(30));

        Cache::put('office-queue-announcement:'.$treasury->id, [
            'id' => 'announcement-treasury',
            'type' => 'serving',
            'queue_number' => 'TRSY-001',
            'triggered_at' => Carbon::create(2026, 3, 9, 9, 25, 0, 'Asia/Manila')->toIso8601String(),
        ], now()->addMinutes(30));

        $html = Livewire::test(AllOfficesMonitor::class)->html();

        $this->assertStringContainsString('Accounting', $html);
        $this->assertStringContainsString('ACCT-001', $html);
        $this->assertStringNotContainsString('Treasury', $html);
        $this->assertStringNotContainsString('TRSY-001', $html);
    }

    public function test_it_replaces_the_visible_office_when_a_newer_priority_office_exists(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 9, 10, 0, 0, 'Asia/Manila'));

        $accounting = $this->createOffice('Accounting', 'accounting', 'ACCT');
        $treasury = $this->createOffice('Treasury', 'treasury', 'TRSY');

        $accountingServing = $this->createQueueEntry(
            office: $accounting,
            queueNumber: 'ACCT-001',
            status: QueueEntry::STATUS_SERVING,
            createdAt: Carbon::create(2026, 3, 9, 8, 30, 0, 'Asia/Manila')
        );

        $this->setCalledAt($accountingServing, Carbon::create(2026, 3, 9, 8, 35, 0, 'Asia/Manila'));

        $this->createQueueEntry(
            office: $treasury,
            queueNumber: 'TRSY-001',
            status: QueueEntry::STATUS_WAITING,
            createdAt: Carbon::create(2026, 3, 9, 8, 40, 0, 'Asia/Manila')
        );

        $accountingRecent = $this->createQueueEntry(
            office: $accounting,
            queueNumber: 'ACCT-009',
            status: QueueEntry::STATUS_COMPLETED,
            createdAt: Carbon::create(2026, 3, 9, 8, 0, 0, 'Asia/Manila')
        );

        $treasuryRecent = $this->createQueueEntry(
            office: $treasury,
            queueNumber: 'TRSY-004',
            status: QueueEntry::STATUS_NOT_SERVED,
            createdAt: Carbon::create(2026, 3, 9, 8, 5, 0, 'Asia/Manila')
        );

        $this->setServedAt($accountingRecent, Carbon::create(2026, 3, 9, 8, 50, 0, 'Asia/Manila'));
        $this->setServedAt($treasuryRecent, Carbon::create(2026, 3, 9, 8, 55, 0, 'Asia/Manila'));

        $html = Livewire::test(AllOfficesMonitor::class)->html();

        $this->assertSame(1, substr_count($html, 'Recent Transactions Today'));
        $this->assertSame(1, substr_count($html, 'ACCT-009'));
        $this->assertSame(0, substr_count($html, 'TRSY-004'));
        $this->assertStringContainsString('Accounting', $html);
        $this->assertStringNotContainsString('Treasury', $html);
    }

    private function createOffice(string $name, string $slug, string $prefix): Office
    {
        return Office::create([
            'name' => $name,
            'slug' => $slug,
            'prefix' => $prefix,
            'description' => $name.' services',
            'next_number' => 1,
            'tickets_accommodated_total' => 0,
            'is_active' => true,
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

    private function setCalledAt(QueueEntry $entry, Carbon $calledAt): void
    {
        $timestamp = $calledAt->copy()->setTimezone((string) config('app.timezone', 'UTC'));

        QueueEntry::whereKey($entry)->update([
            'called_at' => $timestamp,
        ]);
    }

    private function setServedAt(QueueEntry $entry, Carbon $servedAt): void
    {
        $timestamp = $servedAt->copy()->setTimezone((string) config('app.timezone', 'UTC'));

        QueueEntry::whereKey($entry)->update([
            'served_at' => $timestamp,
        ]);
    }
}
