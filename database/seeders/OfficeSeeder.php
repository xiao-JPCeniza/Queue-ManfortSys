<?php

namespace Database\Seeders;

use App\Models\Office;
use Illuminate\Database\Seeder;

class OfficeSeeder extends Seeder
{
    public function run(): void
    {
        $offices = [
            // Core offices (existing)
            ['name' => 'HRMO', 'slug' => 'hrmo', 'prefix' => 'HRMO', 'description' => 'Human Resource Management Office'],
            ['name' => 'Treasury', 'slug' => 'treasury', 'prefix' => 'TRSY', 'description' => 'Municipal Treasurer\'s Office'],
            ['name' => 'Accounting', 'slug' => 'accounting', 'prefix' => 'ACCT', 'description' => 'Municipal Accounting Office'],
            ['name' => 'Civil Registry', 'slug' => 'civil-registry', 'prefix' => 'CR', 'description' => 'Local Civil Registry Office'],
            ['name' => 'Business Permits', 'slug' => 'business-permits', 'prefix' => 'BPLO', 'description' => 'Business Permits and Licensing Office'],
            // From OfficeDesignationSeeder (MISO360) – additional offices
            ['name' => 'Assessor\'s Office', 'slug' => 'assessors-office', 'prefix' => 'ASSR', 'description' => 'Municipal Assessor\'s Office'],
            ['name' => 'MHO', 'slug' => 'mho', 'prefix' => 'MHO', 'description' => 'Municipal Health Office'],
            ['name' => 'MSWDO', 'slug' => 'mswdo', 'prefix' => 'MSWDO', 'description' => 'Municipal Social Welfare and Development Office'],
            ['name' => 'OBO', 'slug' => 'obo', 'prefix' => 'OBO', 'description' => 'Office of the Building Official'],
       ];

        foreach ($offices as $office) {
            $officeModel = Office::firstOrNew(['slug' => $office['slug']]);

            $officeModel->fill([
                'name' => $office['name'],
                'prefix' => $office['prefix'],
                'description' => $office['description'],
                'is_active' => true,
                'show_in_public_queue' => in_array($office['slug'], Office::MUNICIPALITY_QUEUE_SERVICE_SLUGS, true),
            ]);

            if (!$officeModel->exists) {
                $officeModel->next_number = 1;
            }

            $officeModel->save();
        }
    }
}
