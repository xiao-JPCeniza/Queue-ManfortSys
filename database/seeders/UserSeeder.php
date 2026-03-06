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

        $acct = Office::whereIn('slug', ['accounting', 'acct'])->first();
        if ($acct && $officeAdminRole) {
            User::firstOrCreate(
                ['email' => 'acct@manolofortich.gov.ph'],
                [
                    'name' => 'Accounting Office Admin',
                    'password' => Hash::make('password'),
                    'role_id' => $officeAdminRole->id,
                    'office_id' => $acct->id,
                ]
            );
        }

        $assr = Office::whereIn('slug', ['assessors-office', 'assr'])->first();
        if ($assr && $officeAdminRole) {
            User::firstOrCreate(
                ['email' => 'assr@manolofortich.gov.ph'],
                [
                    'name' => 'Assessors Office Admin',
                    'password' => Hash::make('password'),
                    'role_id' => $officeAdminRole->id,
                    'office_id' => $assr->id,
                ]
            );
        }

        $bplo = Office::whereIn('slug', ['business-permits', 'bplo'])->first();
        if ($bplo && $officeAdminRole) {
            User::firstOrCreate(
                ['email' => 'bplo@manolofortich.gov.ph'],
                [
                    'name' => 'BPLO Office Admin',
                    'password' => Hash::make('password'),
                    'role_id' => $officeAdminRole->id,
                    'office_id' => $bplo->id,
                ]
            );
        }

        $cr = Office::whereIn('slug', ['civil-registry', 'cr'])->first();
        if ($cr && $officeAdminRole) {
            User::firstOrCreate(
                ['email' => 'cr@manolofortich.gov.ph'],
                [
                    'name' => 'Civil Registry Office Admin',
                    'password' => Hash::make('password'),
                    'role_id' => $officeAdminRole->id,
                    'office_id' => $cr->id,
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

        $mho = Office::where('slug', 'mho')->first();
        if ($mho && $officeAdminRole) {
            User::firstOrCreate(
                ['email' => 'mho@manolofortich.gov.ph'],
                [
                    'name' => 'MHO Office Admin',
                    'password' => Hash::make('password'),
                    'role_id' => $officeAdminRole->id,
                    'office_id' => $mho->id,
                ]
            );
        }

        $mswdo = Office::where('slug', 'mswdo')->first();
        if ($mswdo && $officeAdminRole) {
            User::firstOrCreate(
                ['email' => 'mswdo@manolofortich.gov.ph'],
                [
                    'name' => 'MSWDO Office Admin',
                    'password' => Hash::make('password'),
                    'role_id' => $officeAdminRole->id,
                    'office_id' => $mswdo->id,
                ]
            );
        }

        $trsy = Office::whereIn('slug', ['treasury', 'trsy'])->first();
        if ($trsy && $officeAdminRole) {
            User::firstOrCreate(
                ['email' => 'trsy@manolofortich.gov.ph'],
                [
                    'name' => 'Treasury Office Admin',
                    'password' => Hash::make('password'),
                    'role_id' => $officeAdminRole->id,
                    'office_id' => $trsy->id,
                ]
            );
        }
    }
}
