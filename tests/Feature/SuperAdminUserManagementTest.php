<?php

namespace Tests\Feature;

use App\Models\Office;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperAdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_management_only_lists_users_from_public_queue_offices(): void
    {
        [$superAdminRole, $officeAdminRole] = $this->createRoles();

        $superAdmin = User::factory()->create([
            'role_id' => $superAdminRole->id,
            'office_id' => null,
        ]);

        $hrmo = $this->createOffice('HRMO', 'hrmo', 'HRMO', true);
        $publicOffice = $this->createOffice('Citizen Center', 'citizen-center', 'CCEN', true);
        $hiddenOffice = $this->createOffice('OBO', 'obo', 'OBO', false);

        User::factory()->create([
            'name' => 'Citizen Center Admin',
            'role_id' => $officeAdminRole->id,
            'office_id' => $publicOffice->id,
        ]);

        User::factory()->create([
            'name' => 'OBO Admin',
            'role_id' => $officeAdminRole->id,
            'office_id' => $hiddenOffice->id,
        ]);

        User::factory()->create([
            'name' => 'HRMO Admin',
            'role_id' => $officeAdminRole->id,
            'office_id' => $hrmo->id,
        ]);

        $this->actingAs($superAdmin)
            ->get(route('super-admin.user-management'))
            ->assertOk()
            ->assertSee('Citizen Center Admin')
            ->assertSee('HRMO Admin')
            ->assertDontSee('OBO Admin');
    }

    private function createRoles(): array
    {
        return [
            Role::create([
                'name' => 'Super Admin',
                'slug' => 'super_admin',
                'description' => 'System-wide administrator',
            ]),
            Role::create([
                'name' => 'Office Admin',
                'slug' => 'office_admin',
                'description' => 'Office queue administrator',
            ]),
        ];
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
