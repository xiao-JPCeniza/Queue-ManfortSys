<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Super Admin',
                'slug' => 'super_admin',
                'description' => 'Full system access. Can manage all offices, users, and queue settings.',
            ],
            [
                'name' => 'Office Admin',
                'slug' => 'office_admin',
                'description' => 'Manages queue for assigned office only: call next, complete, monitor.',
            ],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(['slug' => $role['slug']], $role);
        }

        $obsoleteRoleId = Role::query()
            ->where('slug', 'queue_master')
            ->value('id');

        if ($obsoleteRoleId !== null) {
            User::query()->where('role_id', $obsoleteRoleId)->delete();
            Role::query()->whereKey($obsoleteRoleId)->delete();
        }
    }
}
