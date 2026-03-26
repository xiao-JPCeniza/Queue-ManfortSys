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
            ->assertSet('selectedOfficeSlug', 'citizen-center');

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
            ->assertSee('Priority')
            ->call('confirmOfficeSelection', QueueEntry::TYPE_SENIOR_PREGNANT)
            ->assertSee('Citizen Center')
            ->assertSee('CCEN-001')
            ->assertSee('Priority');

        $this->assertDatabaseHas('queue_entries', [
            'office_id' => $office->id,
            'queue_number' => 'CCEN-001',
            'client_type' => QueueEntry::TYPE_SENIOR_PREGNANT,
        ]);
    }

    public function test_treasury_requires_a_service_selection_before_generating_a_ticket(): void
    {
        $office = $this->createOffice('Treasury', 'treasury', 'TRSY', true, [
            'description' => 'Municipal Treasurer\'s Office',
            'service_window_count' => count(Office::TREASURY_DEFAULT_SERVICE_WINDOW_LABELS),
            'service_window_labels' => Office::TREASURY_DEFAULT_SERVICE_WINDOW_LABELS,
        ]);

        Livewire::test(ClientDashboard::class)
            ->call('promptOfficeSelection', $office->id)
            ->assertSee('Business Taxes, Fees and Charges')
            ->assertSee('Choose the MTO service first')
            ->call('selectPendingService', 'market_charges')
            ->assertSee('Selected Service')
            ->assertSee('Market Charges')
            ->call('confirmOfficeSelection', QueueEntry::TYPE_PWD)
            ->assertSee('TRSY-001')
            ->assertSee('Market Charges')
            ->assertSee('Teller 6-7');

        $this->assertDatabaseHas('queue_entries', [
            'office_id' => $office->id,
            'queue_number' => 'TRSY-001',
            'client_type' => QueueEntry::TYPE_PWD,
            'service_key' => 'market_charges',
        ]);
    }

    private function createOffice(string $name, string $slug, string $prefix, bool $showInPublicQueue, array $attributes = []): Office
    {
        return Office::create(array_merge([
            'name' => $name,
            'slug' => $slug,
            'prefix' => $prefix,
            'description' => $name.' services',
            'next_number' => 1,
            'tickets_accommodated_total' => 0,
            'is_active' => true,
            'show_in_public_queue' => $showInPublicQueue,
        ], $attributes));
    }
}
