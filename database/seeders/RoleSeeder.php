<?php

namespace Database\Seeders;

use App\Models\Role;
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
                'name' => 'Queue Master',
                'slug' => 'queue_master',
                'description' => 'Manages overall queue operations: generate numbers, QR codes, monitor all offices.',
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
    }
}
