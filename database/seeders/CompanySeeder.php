<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Company::updateOrCreate([
            'tax_id' => 'B12345678',
        ], [
            'name' => 'DKM0',
            'invoice_series' => 'DKM-',
            'logo_url' => null,
            'active' => true,
        ]);

        Company::updateOrCreate([
            'tax_id' => 'B87654321',
        ], [
            'name' => 'By Renting',
            'invoice_series' => 'BYR-',
            'logo_url' => null,
            'active' => true,
        ]);
    }
}
