<?php

namespace Tests\Feature;

use App\Livewire\ClientDashboard;
use App\Livewire\OfficeAdmin\AllOfficesMonitor;
use App\Livewire\OfficeAdmin\WindowDesk;
use App\Models\Office;
use App\Models\QueueEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Livewire\Livewire;
use Tests\TestCase;

class ServiceWindowSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_public_queue_service_choices_follow_the_saved_window_count(): void
    {
        $office = Office::create([
            'name' => 'HRMO',
            'slug' => 'hrmo',
            'prefix' => 'HRMO',
            'description' => 'Human Resource Management Office',
            'next_number' => 1,
            'service_window_count' => 2,
            'service_window_labels' => Office::HRMO_DEFAULT_SERVICE_WINDOW_LABELS,
            'tickets_accommodated_total' => 0,
            'is_active' => true,
            'show_in_public_queue' => true,
        ]);

        Livewire::test(ClientDashboard::class)
            ->call('promptOfficeSelection', $office->id)
            ->assertSee('Request for Recruitment and Selection Services')
            ->assertSee('Request for Certifications and Service Record')
            ->assertDontSee('Request for Valid Identification Card')
            ->assertDontSee('Request for Anti-Red Tape Act (ARTA) Identification Card');
    }

    public function test_public_live_monitor_uses_the_saved_window_labels(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 4, 6, 10, 0, 0, 'Asia/Manila'));

        $office = Office::create([
            'name' => 'Citizen Center',
            'slug' => 'citizen-center',
            'prefix' => 'CCEN',
            'description' => 'Citizen Center services',
            'next_number' => 1,
            'service_window_count' => 2,
            'service_window_labels' => [
                1 => 'Front Desk',
                2 => 'Release Desk',
            ],
            'tickets_accommodated_total' => 0,
            'is_active' => true,
            'show_in_public_queue' => true,
        ]);

        $entry = QueueEntry::create([
            'office_id' => $office->id,
            'queue_number' => 'CCEN-001',
            'status' => QueueEntry::STATUS_SERVING,
            'service_window_number' => 1,
        ]);

        $entry->update([
            'called_at' => Carbon::create(2026, 4, 6, 9, 45, 0, 'Asia/Manila')
                ->setTimezone((string) config('app.timezone', 'UTC')),
        ]);

        Livewire::test(AllOfficesMonitor::class)
            ->assertSee('Front Desk')
            ->assertSee('Release Desk')
            ->assertSee('CCEN-001');
    }

    public function test_public_queue_shows_added_window_labels_for_generic_multi_window_offices(): void
    {
        $office = Office::create([
            'name' => 'Citizen Center',
            'slug' => 'citizen-center',
            'prefix' => 'CCEN',
            'description' => 'Citizen Center services',
            'next_number' => 1,
            'service_window_count' => 3,
            'service_window_labels' => [
                1 => 'Assessment Desk',
                2 => 'Verification Desk',
                3 => 'Release Counter',
            ],
            'tickets_accommodated_total' => 0,
            'is_active' => true,
            'show_in_public_queue' => true,
        ]);

        Livewire::test(ClientDashboard::class)
            ->call('promptOfficeSelection', $office->id)
            ->assertSee('Assessment Desk')
            ->assertSee('Verification Desk')
            ->assertSee('Release Counter')
            ->call('selectPendingService', 'service_window_3')
            ->call('confirmOfficeSelection', QueueEntry::TYPE_REGULAR)
            ->assertSee('CCEN-001');

        $this->assertDatabaseHas('queue_entries', [
            'office_id' => $office->id,
            'queue_number' => 'CCEN-001',
            'service_key' => 'service_window_3',
            'service_label' => 'Release Counter',
        ]);
    }

    public function test_public_queue_shows_added_windows_beyond_the_preconfigured_office_defaults(): void
    {
        $office = Office::create([
            'name' => 'HRMO',
            'slug' => 'hrmo',
            'prefix' => 'HRMO',
            'description' => 'Human Resource Management Office',
            'next_number' => 1,
            'service_window_count' => 5,
            'service_window_labels' => [
                1 => 'Recruitment and Selection Services',
                2 => 'Certifications and Service Record',
                3 => 'Valid Identification Card',
                4 => 'ARTA Identification Card',
                5 => 'Releasing Counter',
            ],
            'tickets_accommodated_total' => 0,
            'is_active' => true,
            'show_in_public_queue' => true,
        ]);

        Livewire::test(ClientDashboard::class)
            ->call('promptOfficeSelection', $office->id)
            ->assertSee('Request for Recruitment and Selection Services')
            ->assertSee('ARTA Identification Card')
            ->assertSee('Releasing Counter')
            ->call('selectPendingService', 'service_window_5')
            ->call('confirmOfficeSelection', QueueEntry::TYPE_PWD)
            ->assertSee('HRMO-001');

        $this->assertDatabaseHas('queue_entries', [
            'office_id' => $office->id,
            'queue_number' => 'HRMO-001',
            'service_key' => 'service_window_5',
            'service_label' => 'Releasing Counter',
        ]);
    }

    public function test_added_window_selection_routes_the_ticket_to_the_matching_window_desk(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 4, 6, 10, 0, 0, 'Asia/Manila'));

        $office = Office::create([
            'name' => 'Citizen Center',
            'slug' => 'citizen-center',
            'prefix' => 'CCEN',
            'description' => 'Citizen Center services',
            'next_number' => 1,
            'service_window_count' => 3,
            'service_window_labels' => [
                1 => 'Assessment Desk',
                2 => 'Verification Desk',
                3 => 'Release Counter',
            ],
            'tickets_accommodated_total' => 0,
            'is_active' => true,
            'show_in_public_queue' => true,
        ]);

        $assessmentEntry = QueueEntry::create([
            'office_id' => $office->id,
            'queue_number' => 'CCEN-001',
            'status' => QueueEntry::STATUS_WAITING,
            'service_key' => 'service_window_1',
        ]);

        $releaseEntry = QueueEntry::create([
            'office_id' => $office->id,
            'queue_number' => 'CCEN-002',
            'status' => QueueEntry::STATUS_WAITING,
            'service_key' => 'service_window_3',
        ]);

        Livewire::test(WindowDesk::class, ['office' => $office, 'windowNumber' => 3])
            ->call('callNext')
            ->assertSee('Now serving CCEN-002 at Release Counter.')
            ->assertDontSee('CCEN-001');

        $this->assertDatabaseHas('queue_entries', [
            'id' => $releaseEntry->id,
            'status' => QueueEntry::STATUS_SERVING,
            'service_window_number' => 3,
        ]);

        $this->assertDatabaseHas('queue_entries', [
            'id' => $assessmentEntry->id,
            'status' => QueueEntry::STATUS_WAITING,
            'service_window_number' => null,
        ]);
    }
}
