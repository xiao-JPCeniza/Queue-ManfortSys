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
            'office_name' => 'Citizen Center',
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
            ->assertSee('Choose the service you need to visit.')
            ->call('selectPendingService', 'market_charges')
            ->assertSee('Selected Service')
            ->assertSee('Market Charges')
            ->call('confirmOfficeSelection', QueueEntry::TYPE_PWD)
            ->assertSee('TRSY-001');

        $this->assertDatabaseHas('queue_entries', [
            'office_id' => $office->id,
            'queue_number' => 'TRSY-001',
            'client_type' => QueueEntry::TYPE_PWD,
            'service_key' => 'market_charges',
            'service_label' => 'Market Charges',
        ]);
    }

    public function test_civil_registry_requires_a_service_selection_before_generating_a_ticket(): void
    {
        $office = $this->createOffice('Civil Registry', 'civil-registry', 'CR', true, [
            'description' => 'Municipal Local Civil Registry Office',
            'service_window_count' => count(Office::CIVIL_REGISTRY_DEFAULT_SERVICE_WINDOW_LABELS),
            'service_window_labels' => Office::CIVIL_REGISTRY_DEFAULT_SERVICE_WINDOW_LABELS,
        ]);

        Livewire::test(ClientDashboard::class)
            ->call('promptOfficeSelection', $office->id)
            ->assertSee('Birth Registration')
            ->assertSee('Choose the service you need to visit.')
            ->call('selectPendingService', 'window_3')
            ->assertSee('Selected Service')
            ->assertSee('Death Registration')
            ->call('confirmOfficeSelection', QueueEntry::TYPE_PREGNANT)
            ->assertSee('MCR-001');

        $this->assertDatabaseHas('queue_entries', [
            'office_id' => $office->id,
            'queue_number' => 'MCR-001',
            'client_type' => QueueEntry::TYPE_PREGNANT,
            'service_key' => 'window_3',
            'service_label' => 'Death Registration',
        ]);
    }

    public function test_bplo_requires_a_service_selection_before_generating_a_ticket(): void
    {
        $office = $this->createOffice('Business Permits', 'business-permits', 'BPLO', true, [
            'description' => 'Business Permits and Licensing Office',
            'service_window_count' => count(Office::BPLO_DEFAULT_SERVICE_WINDOW_LABELS),
            'service_window_labels' => Office::BPLO_DEFAULT_SERVICE_WINDOW_LABELS,
        ]);

        Livewire::test(ClientDashboard::class)
            ->call('promptOfficeSelection', $office->id)
            ->assertSee('Business Permit Application (New & Renewal)')
            ->assertSee('Choose the service you need to visit.')
            ->call('selectPendingService', 'request_for_certifications')
            ->assertSee('Selected Service')
            ->assertSee('Request for Certifications')
            ->call('confirmOfficeSelection', QueueEntry::TYPE_SENIOR_CITIZEN)
            ->assertSee('BPLO-001');

        $this->assertDatabaseHas('queue_entries', [
            'office_id' => $office->id,
            'queue_number' => 'BPLO-001',
            'client_type' => QueueEntry::TYPE_SENIOR_CITIZEN,
            'service_key' => 'request_for_certifications',
            'service_label' => 'Request for Certifications',
        ]);
    }

    public function test_hrmo_requires_a_service_selection_before_generating_a_ticket(): void
    {
        $office = $this->createOffice('HRMO', 'hrmo', 'HRMO', true, [
            'description' => 'Human Resource Management Office',
            'service_window_count' => count(Office::HRMO_DEFAULT_SERVICE_WINDOW_LABELS),
            'service_window_labels' => Office::HRMO_DEFAULT_SERVICE_WINDOW_LABELS,
        ]);

        Livewire::test(ClientDashboard::class)
            ->call('promptOfficeSelection', $office->id)
            ->assertSee('Request for Recruitment and Selection Services')
            ->assertSee('Choose the service you need to visit.')
            ->call('selectPendingService', 'arta_identification_card')
            ->assertSee('Selected Service')
            ->assertSee('Request for Anti-Red Tape Act (ARTA) Identification Card')
            ->call('confirmOfficeSelection', QueueEntry::TYPE_SENIOR_CITIZEN)
            ->assertSee('HRMO-001');

        $this->assertDatabaseHas('queue_entries', [
            'office_id' => $office->id,
            'queue_number' => 'HRMO-001',
            'client_type' => QueueEntry::TYPE_SENIOR_CITIZEN,
            'service_key' => 'arta_identification_card',
            'service_label' => 'Request for Anti-Red Tape Act (ARTA) Identification Card',
        ]);
    }

    public function test_menro_requires_a_service_selection_before_generating_a_ticket(): void
    {
        $office = $this->createOffice('MENRO', 'menro', 'MENRO', true, [
            'description' => 'Municipal Environment and Natural Resources Office',
            'service_window_count' => count(Office::MENRO_DEFAULT_SERVICE_WINDOW_LABELS),
            'service_window_labels' => Office::MENRO_DEFAULT_SERVICE_WINDOW_LABELS,
        ]);

        Livewire::test(ClientDashboard::class)
            ->call('promptOfficeSelection', $office->id)
            ->assertSee('Addressing Environmental Concerns')
            ->assertSee('Choose the service you need to visit.')
            ->call('selectPendingService', 'issuance_of_clive_card')
            ->assertSee('Selected Service')
            ->assertSee('Issuance of CLIVE Card')
            ->call('confirmOfficeSelection', QueueEntry::TYPE_PWD)
            ->assertSee('MENRO-001');

        $this->assertDatabaseHas('queue_entries', [
            'office_id' => $office->id,
            'queue_number' => 'MENRO-001',
            'client_type' => QueueEntry::TYPE_PWD,
            'service_key' => 'issuance_of_clive_card',
            'service_label' => 'Issuance of CLIVE Card',
        ]);
    }

    public function test_treasury_with_reduced_window_count_uses_active_window_labels_on_the_queue_page(): void
    {
        $office = $this->createOffice('MTO', 'mto', 'MTO', true, [
            'description' => 'Municipal Treasurer\'s Office',
            'service_window_count' => 3,
            'service_window_labels' => [
                1 => 'Business Taxes, Fees and Charges',
                2 => 'Teller 2',
                3 => 'Marriage License',
            ],
        ]);

        Livewire::test(ClientDashboard::class)
            ->call('promptOfficeSelection', $office->id)
            ->assertSee('Business Taxes, Fees and Charges')
            ->assertSee('Teller 2')
            ->assertSee('Marriage License')
            ->assertDontSee('Real Property Taxes')
            ->call('selectPendingService', 'service_window_3')
            ->assertSee('Selected Service')
            ->assertSee('Marriage License')
            ->call('confirmOfficeSelection', QueueEntry::TYPE_REGULAR)
            ->assertSee('MTO-001');

        $this->assertDatabaseHas('queue_entries', [
            'office_id' => $office->id,
            'queue_number' => 'MTO-001',
            'client_type' => QueueEntry::TYPE_REGULAR,
            'service_key' => 'service_window_3',
            'service_label' => 'Marriage License',
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
