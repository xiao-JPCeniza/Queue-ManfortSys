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

        if ($superAdminRole) {
            User::updateOrCreate(
                ['email' => 'admin@manolofortich.gov.ph'],
                [
                    'name' => 'Super Admin',
                    'password' => Hash::make('password'),
                    'role_id' => $superAdminRole->id,
                    'office_id' => null,
                ]
            );
        }

        if ($queueMasterRole) {
            User::updateOrCreate(
                ['email' => 'queuemaster@manolofortich.gov.ph'],
                [
                    'name' => 'Queue Master',
                    'password' => Hash::make('password'),
                    'role_id' => $queueMasterRole->id,
                    'office_id' => null,
                ]
            );
        }

        if (! $officeAdminRole) {
            return;
        }

        $officeAdminAccounts = [
            [
                'slugs' => ['accounting', 'acct'],
                'email' => 'acct@manolofortich.gov.ph',
                'name' => 'Accounting Office Admin',
            ],
            [
                'slugs' => ['assessors-office', 'assr'],
                'email' => 'assr@manolofortich.gov.ph',
                'name' => 'Assessors Office Admin',
            ],
            [
                'slugs' => ['business-permits', 'bplo'],
                'email' => 'bplo@manolofortich.gov.ph',
                'name' => 'BPLO Office Admin',
            ],
            [
                'slugs' => ['civil-registry', 'cr'],
                'email' => 'cr@manolofortich.gov.ph',
                'name' => 'Civil Registry Office Admin',
            ],
            [
                'slugs' => ['hrmo'],
                'email' => 'hrmo@manolofortich.gov.ph',
                'name' => 'HRMO Office Admin',
            ],
            [
                'slugs' => ['mho'],
                'email' => 'mho@manolofortich.gov.ph',
                'name' => 'MHO Office Admin',
            ],
            [
                'slugs' => ['mswdo'],
                'email' => 'mswdo@manolofortich.gov.ph',
                'name' => 'MSWDO Office Admin',
            ],
            [
                'slugs' => ['treasury', 'trsy'],
                'email' => 'trsy@manolofortich.gov.ph',
                'name' => 'Treasury Office Admin',
            ],
            [
                'slugs' => ['menro'],
                'email' => 'menro@manolofortich.gov.ph',
                'name' => 'MENRO Office Admin',
            ],
        ];

        foreach ($officeAdminAccounts as $account) {
            $office = Office::whereIn('slug', $account['slugs'])->first();

            if (! $office) {
                continue;
            }

            User::updateOrCreate(
                ['email' => $account['email']],
                [
                    'name' => $account['name'],
                    'password' => Hash::make('password'),
                    'role_id' => $officeAdminRole->id,
                    'office_id' => $office->id,
                ]
            );
        }
    }
}
