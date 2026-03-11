<?php

namespace Tests\Feature;

use App\Livewire\ClientDashboard;
use App\Models\Office;
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
