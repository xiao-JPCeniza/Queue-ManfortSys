<?php

namespace Tests\Feature;

use App\Livewire\ClientDashboard;
use App\Models\Office;
use App\Models\QueueEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ClientDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_queue_lists_only_active_public_queue_offices(): void
    {
        $this->createOffice('HRMO', 'hrmo', 'HRMO', true);
        $this->createOffice('Citizen Center', 'citizen-center', 'CCEN', true);
        $this->createOffice('OBO', 'obo', 'OBO', false);

        Livewire::test(ClientDashboard::class)
            ->assertSeeInOrder(['HRMO', 'Citizen Center'])
            ->assertDontSee('OBO');
    }

    public function test_public_queue_refresh_removes_deleted_offices_and_clears_an_invalid_filter(): void
    {
        $this->createOffice('HRMO', 'hrmo', 'HRMO', true);
        $office = $this->createOffice('Citizen Center', 'citizen-center', 'CCEN', true);

        Livewire::test(ClientDashboard::class)
            ->set('selectedOfficeSlug', 'citizen-center')
            ->assertSee('Citizen Center')
            ->assertDontSee('HRMO');

        $office->delete();

        Livewire::test(ClientDashboard::class)
            ->set('selectedOfficeSlug', 'citizen-center')
            ->call('$refresh')
            ->assertSet('selectedOfficeSlug', '')
            ->assertSee('HRMO')
            ->assertDontSee('Citizen Center');
    }

    public function test_selecting_a_new_public_queue_office_generates_a_ticket_with_its_prefix(): void
    {
        $office = $this->createOffice('Citizen Center', 'citizen-center', 'CCEN', true);

        Livewire::test(ClientDashboard::class)
            ->call('selectOffice', $office->id)
            ->assertSee('Citizen Center')
            ->assertSee('CCEN-001');

        $this->assertDatabaseHas('queue_entries', [
            'office_id' => $office->id,
            'queue_number' => 'CCEN-001',
            'client_type' => QueueEntry::TYPE_REGULAR,
        ]);
    }

    public function test_confirming_the_senior_pregnant_option_generates_a_priority_ticket(): void
    {
        $office = $this->createOffice('Citizen Center', 'citizen-center', 'CCEN', true);

        Livewire::test(ClientDashboard::class)
            ->call('promptOfficeSelection', $office->id)
            ->assertSee('Ticket Option')
            ->assertSee('Senior / Pregnant')
            ->call('confirmOfficeSelection', QueueEntry::TYPE_SENIOR_PREGNANT)
            ->assertSee('Citizen Center')
            ->assertSee('CCEN-001')
            ->assertSee('Senior / Pregnant');

        $this->assertDatabaseHas('queue_entries', [
            'office_id' => $office->id,
            'queue_number' => 'CCEN-001',
            'client_type' => QueueEntry::TYPE_SENIOR_PREGNANT,
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
