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
            ['name' => 'MISO', 'slug' => 'miso', 'prefix' => 'MISO', 'description' => 'Management Information Systems Office'],
            ['name' => 'MENRO', 'slug' => 'menro', 'prefix' => 'MENRO', 'description' => 'Municipal Environment and Natural Resources Office'],
            ['name' => 'MAO', 'slug' => 'mao', 'prefix' => 'MAO', 'description' => 'Municipal Agriculture Office'],
            ['name' => 'LDRRMO', 'slug' => 'ldrrmo', 'prefix' => 'LDRRMO', 'description' => 'Local Disaster Risk Reduction and Management Office'],
            ['name' => 'HRMO', 'slug' => 'hrmo', 'prefix' => 'HRMO', 'description' => 'Human Resource Management Office'],
            ['name' => 'Mayor\'s Office', 'slug' => 'mayors-office', 'prefix' => 'MAYOR', 'description' => 'Office of the Municipal Mayor'],
            ['name' => 'Treasury', 'slug' => 'treasury', 'prefix' => 'TRSY', 'description' => 'Municipal Treasurer\'s Office'],
            ['name' => 'Accounting', 'slug' => 'accounting', 'prefix' => 'ACCT', 'description' => 'Municipal Accounting Office'],
            ['name' => 'Budget', 'slug' => 'budget', 'prefix' => 'BUDG', 'description' => 'Municipal Budget Office'],
            ['name' => 'Civil Registry', 'slug' => 'civil-registry', 'prefix' => 'CR', 'description' => 'Local Civil Registry Office'],
            ['name' => 'Business Permits', 'slug' => 'business-permits', 'prefix' => 'BPLO', 'description' => 'Business Permits and Licensing Office'],
            ['name' => 'Engineering', 'slug' => 'engineering', 'prefix' => 'ENGR', 'description' => 'Municipal Engineering Office'],
            // From OfficeDesignationSeeder (MISO360) – additional offices
            ['name' => 'GSO', 'slug' => 'gso', 'prefix' => 'GSO', 'description' => 'General Services Office'],
            ['name' => 'Municipal Administrator\'s Office', 'slug' => 'municipal-administrators-office', 'prefix' => 'ADMIN', 'description' => 'Municipal Administrator\'s Office'],
            ['name' => 'Assessor\'s Office', 'slug' => 'assessors-office', 'prefix' => 'ASSR', 'description' => 'Municipal Assessor\'s Office'],
            ['name' => 'MHO', 'slug' => 'mho', 'prefix' => 'MHO', 'description' => 'Municipal Health Office'],
            ['name' => 'ICT Unit', 'slug' => 'ict-unit', 'prefix' => 'ICT', 'description' => 'Municipal Information and Communications Technology Office (MICTO/ICT Unit)'],
            ['name' => 'Legal Office', 'slug' => 'legal-office', 'prefix' => 'LEGAL', 'description' => 'Municipal Legal Office'],
            ['name' => 'Municipal Library', 'slug' => 'municipal-library', 'prefix' => 'LIB', 'description' => 'Municipal Library'],
            ['name' => 'MPDO', 'slug' => 'mpdo', 'prefix' => 'MPDO', 'description' => 'Municipal Planning and Development Office'],
            ['name' => 'PNP Liaison', 'slug' => 'pnp-liaison', 'prefix' => 'PNP', 'description' => 'Municipal Police Station (PNP Liaison)'],
            ['name' => 'Slaughter Division', 'slug' => 'slaughter-division', 'prefix' => 'SLTR', 'description' => 'Municipal Slaughter Division'],
            ['name' => 'MSWDO', 'slug' => 'mswdo', 'prefix' => 'MSWDO', 'description' => 'Municipal Social Welfare and Development Office'],
            ['name' => 'Negosyo Center', 'slug' => 'negosyo-center', 'prefix' => 'NEGO', 'description' => 'Negosyo Center'],
            ['name' => 'OBO', 'slug' => 'obo', 'prefix' => 'OBO', 'description' => 'Office of the Building Official'],
            ['name' => 'Vice Mayor\'s Office', 'slug' => 'vice-mayors-office', 'prefix' => 'VMAYOR', 'description' => 'Office of the Municipal Vice Mayor'],
            ['name' => 'Sangguniang Bayan', 'slug' => 'sangguniang-bayan', 'prefix' => 'SB', 'description' => 'Office of the Sangguniang Bayan / Municipal Council'],
            ['name' => 'DILG', 'slug' => 'dilg', 'prefix' => 'DILG', 'description' => 'Office of the Municipal Department of Interior and Local Government'],
            ['name' => 'Internal Audit', 'slug' => 'internal-audit', 'prefix' => 'AUDIT', 'description' => 'Office of the Municipal Internal Audit Service'],
            ['name' => 'Motorpool Division', 'slug' => 'motorpool-division', 'prefix' => 'MTRPL', 'description' => 'Office of Municipal Motorpool Division'],
            ['name' => 'Tourism Office', 'slug' => 'tourism-office', 'prefix' => 'TRSM', 'description' => 'Office of Municipal Tourism'],
            ['name' => 'Public Market Office', 'slug' => 'public-market-office', 'prefix' => 'MKT', 'description' => 'Office of the Municipal Market / Public Market Office'],
            ['name' => 'PESO', 'slug' => 'peso', 'prefix' => 'PESO', 'description' => 'Office of the Public Employment Service'],
            ['name' => 'NCIP', 'slug' => 'ncip', 'prefix' => 'NCIP', 'description' => 'Office of the Municipal National Commission on Indigenous People'],
            ['name' => 'PDAO', 'slug' => 'pdao', 'prefix' => 'PDAO', 'description' => 'Office of the Municipal Persons with Disability Affairs'],
            ['name' => 'Procurement Division', 'slug' => 'procurement-division', 'prefix' => 'PROC', 'description' => 'Office of the Municipal Procurement Division'],
            ['name' => 'OSCA', 'slug' => 'osca', 'prefix' => 'OSCA', 'description' => 'Office of the Municipal Senior Citizens Association'],
            ['name' => 'BFP Liaison', 'slug' => 'bfp-liaison', 'prefix' => 'BFP', 'description' => 'Bureau of Fire Protection (BFP) Liaison'],
            ['name' => 'Special Education Fund', 'slug' => 'special-education-fund', 'prefix' => 'SEF', 'description' => 'Special Education Fund Division'],
            ['name' => 'Sports Development', 'slug' => 'sports-development', 'prefix' => 'SPRT', 'description' => 'Sports Development Division'],
        ];

        foreach ($offices as $office) {
            $officeModel = Office::firstOrNew(['slug' => $office['slug']]);

            $officeModel->fill([
                'name' => $office['name'],
                'prefix' => $office['prefix'],
                'description' => $office['description'],
                'is_active' => true,
            ]);

            if (!$officeModel->exists) {
                $officeModel->next_number = 1;
            }

            $officeModel->save();
        }
    }
}
