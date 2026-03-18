<?php

namespace Tests\Feature;

use App\Livewire\OfficeAdmin\BploOfficeMonitor;
use App\Livewire\OfficeAdmin\HrmoOfficeMonitor;
use App\Models\Office;
use App\Models\QueueEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class OfficeLiveMonitorTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_bplo_monitor_displays_only_the_latest_called_ticket(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 18, 11, 35, 0, 'Asia/Manila'));

        $office = $this->createOffice('Business Permits', 'bplo', 'BPLO', 4);

        $olderServing = $this->createServingEntry(
            office: $office,
            queueNumber: 'BPLO-001',
            windowNumber: 4,
            calledAt: Carbon::create(2026, 3, 18, 11, 31, 23, 'Asia/Manila')
        );

        $latestServing = $this->createServingEntry(
            office: $office,
            queueNumber: 'BPLO-002',
            windowNumber: 3,
            calledAt: Carbon::create(2026, 3, 18, 11, 32, 5, 'Asia/Manila')
        );

        $html = Livewire::test(BploOfficeMonitor::class, ['office' => $office])->html();

        $this->assertStringContainsString('1 Active', $html);
        $this->assertStringContainsString($latestServing->queue_number, $html);
        $this->assertStringContainsString('Window 3', $html);
        $this->assertStringNotContainsString($olderServing->queue_number, $html);
        $this->assertStringNotContainsString('Window 4', $html);
    }

    public function test_hrmo_monitor_displays_only_the_latest_called_ticket(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 3, 18, 11, 35, 0, 'Asia/Manila'));

        $office = $this->createOffice('HRMO', 'hrmo', 'HRMO', 2);

        $olderServing = $this->createServingEntry(
            office: $office,
            queueNumber: 'HRMO-001',
            windowNumber: 1,
            calledAt: Carbon::create(2026, 3, 18, 11, 30, 0, 'Asia/Manila')
        );

        $latestServing = $this->createServingEntry(
            office: $office,
            queueNumber: 'HRMO-002',
            windowNumber: 2,
            calledAt: Carbon::create(2026, 3, 18, 11, 33, 0, 'Asia/Manila')
        );

        $html = Livewire::test(HrmoOfficeMonitor::class, ['office' => $office])->html();

        $this->assertStringContainsString('1 Active', $html);
        $this->assertStringContainsString($latestServing->queue_number, $html);
        $this->assertStringContainsString('Window 2', $html);
        $this->assertStringNotContainsString($olderServing->queue_number, $html);
        $this->assertStringNotContainsString('Window 1', $html);
    }

    private function createOffice(string $name, string $slug, string $prefix, int $serviceWindowCount): Office
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
            'show_in_public_queue' => true,
        ]);
    }

    private function createServingEntry(Office $office, string $queueNumber, int $windowNumber, Carbon $calledAt): QueueEntry
    {
        $createdAt = $calledAt->copy()->subMinute();

        $entry = QueueEntry::create([
            'office_id' => $office->id,
            'queue_number' => $queueNumber,
            'status' => QueueEntry::STATUS_SERVING,
            'service_window_number' => $windowNumber,
        ]);

        QueueEntry::whereKey($entry)->update([
            'created_at' => $createdAt->copy()->setTimezone((string) config('app.timezone', 'UTC')),
            'updated_at' => $calledAt->copy()->setTimezone((string) config('app.timezone', 'UTC')),
            'called_at' => $calledAt->copy()->setTimezone((string) config('app.timezone', 'UTC')),
        ]);

        return $entry->fresh();
    }
}
