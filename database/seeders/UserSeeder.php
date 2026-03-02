<?php

namespace Database\Seeders;

use App\Models\Office;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $superAdminRole = Role::where('slug', 'super_admin')->first();
        $queueMasterRole = Role::where('slug', 'queue_master')->first();
        $officeAdminRole = Role::where('slug', 'office_admin')->first();

        User::firstOrCreate(
            ['email' => 'admin@manolofortich.gov.ph'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role_id' => $superAdminRole->id,
                'office_id' => null,
            ]
        );

        User::firstOrCreate(
            ['email' => 'queuemaster@manolofortich.gov.ph'],
            [
                'name' => 'Queue Master',
                'password' => Hash::make('password'),
                'role_id' => $queueMasterRole->id,
                'office_id' => null,
            ]
        );

        $miso = Office::where('slug', 'miso')->first();
        if ($miso && $officeAdminRole) {
            User::firstOrCreate(
                ['email' => 'miso@manolofortich.gov.ph'],
                [
                    'name' => 'MISO Office Admin',
                    'password' => Hash::make('password'),
                    'role_id' => $officeAdminRole->id,
                    'office_id' => $miso->id,
                ]
            );
        }

        $hrmo = Office::where('slug', 'hrmo')->first();
        if ($hrmo && $officeAdminRole) {
            User::firstOrCreate(
                ['email' => 'hrmo@manolofortich.gov.ph'],
                [
                    'name' => 'HRMO Office Admin',
                    'password' => Hash::make('password'),
                    'role_id' => $officeAdminRole->id,
                    'office_id' => $hrmo->id,
                ]
            );
        }
    }
}
