<?php

namespace Database\Seeders;

use App\Models\Office;
use Illuminate\Database\Seeder;

class OfficeSeeder extends Seeder
{
    public function run(): void
    {
        $offices = [
            ['name' => 'MISO', 'slug' => 'miso', 'prefix' => 'MISO', 'description' => 'Management Information Systems Office'],
            ['name' => 'LDRRMO', 'slug' => 'ldrrmo', 'prefix' => 'LDRRMO', 'description' => 'Local Disaster Risk Reduction and Management Office'],
            ['name' => 'HRMO', 'slug' => 'hrmo', 'prefix' => 'HRMO', 'description' => 'Human Resource Management Office'],
            ['name' => 'Mayor\'s Office', 'slug' => 'mayors-office', 'prefix' => 'MAYOR', 'description' => 'Office of the Mayor'],
            ['name' => 'Treasury', 'slug' => 'treasury', 'prefix' => 'TRSY', 'description' => 'Municipal Treasury Office'],
            ['name' => 'Accounting', 'slug' => 'accounting', 'prefix' => 'ACCT', 'description' => 'Municipal Accounting Office'],
            ['name' => 'Budget', 'slug' => 'budget', 'prefix' => 'BUDG', 'description' => 'Municipal Budget Office'],
            ['name' => 'Civil Registry', 'slug' => 'civil-registry', 'prefix' => 'CR', 'description' => 'Local Civil Registry Office'],
            ['name' => 'Business Permits', 'slug' => 'business-permits', 'prefix' => 'BPLO', 'description' => 'Business Permits and Licensing Office'],
            ['name' => 'Engineering', 'slug' => 'engineering', 'prefix' => 'ENGR', 'description' => 'Municipal Engineering Office'],
        ];

        foreach ($offices as $office) {
            Office::firstOrCreate(
                ['slug' => $office['slug']],
                array_merge($office, ['next_number' => 1, 'is_active' => true])
            );
        }
    }
}
