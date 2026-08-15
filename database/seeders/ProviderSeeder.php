<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Provider;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class ProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $company = Company::where('tax_id', 'B12345678')->first();
        $otherCompany = Company::where('tax_id', 'B87654321')->first();

        if (! $company || ! $otherCompany) {
            return;
        }

        $providers = [
            [
                'company_id' => $company->id,
                'name' => 'Taller Chapa & Pintura',
                'type' => 'chapa',
                'plain_pin' => '1001',
                'pin_prefix' => '1XXX',
                'latitude' => 40.4168,
                'longitude' => -3.7038,
                'geofence_radius_meters' => 750,
                'email' => 'chapa@example.com',
                'phone' => '+34 600 000 001',
                'tax_id' => 'B00000001',
                'active' => true,
            ],
            [
                'company_id' => $company->id,
                'name' => 'Centro Revision DAB',
                'type' => 'dab',
                'plain_pin' => '2001',
                'pin_prefix' => '2XXX',
                'latitude' => 40.4174,
                'longitude' => -3.7030,
                'geofence_radius_meters' => 750,
                'email' => 'dab@example.com',
                'phone' => '+34 600 000 002',
                'tax_id' => 'B00000002',
                'active' => true,
            ],
            [
                'company_id' => $company->id,
                'name' => 'Limpieza Final Express',
                'type' => 'limpieza',
                'plain_pin' => '3001',
                'pin_prefix' => '3XXX',
                'latitude' => 40.4180,
                'longitude' => -3.7022,
                'geofence_radius_meters' => 750,
                'email' => 'limpieza@example.com',
                'phone' => '+34 600 000 003',
                'tax_id' => 'B00000003',
                'active' => true,
            ],
            [
                'company_id' => $company->id,
                'name' => 'Recepcion Campa Central',
                'type' => 'recepcion',
                'plain_pin' => '4001',
                'pin_prefix' => '4XXX',
                'latitude' => 40.4159,
                'longitude' => -3.7046,
                'geofence_radius_meters' => 750,
                'email' => 'recepcion@example.com',
                'phone' => '+34 600 000 004',
                'tax_id' => 'B00000004',
                'active' => true,
            ],
            [
                'company_id' => $company->id,
                'name' => 'DKM0 Operaciones',
                'type' => 'recepcion',
                'plain_pin' => '5001',
                'pin_prefix' => '5XXX',
                'latitude' => 40.4162,
                'longitude' => -3.7040,
                'geofence_radius_meters' => 750,
                'email' => 'operaciones@dkm0.example.com',
                'phone' => '+34 600 000 006',
                'tax_id' => 'B00000006',
                'active' => true,
            ],
            [
                'company_id' => $otherCompany->id,
                'name' => 'Proveedor de Vehiculos S.A.',
                'type' => 'proveedor_vehiculos',
                'plain_pin' => null,
                'pin_prefix' => null,
                'latitude' => 40.4168,
                'longitude' => -3.7038,
                'geofence_radius_meters' => 750,
                'email' => 'ventas@example.com',
                'phone' => '+34 600 000 005',
                'tax_id' => 'B00000005',
                'active' => true,
            ],
        ];

        foreach ($providers as $providerData) {
            $plainPin = $providerData['plain_pin'];
            unset($providerData['plain_pin']);

            $providerData['pin'] = $plainPin ? Hash::make($plainPin) : null;

            Provider::updateOrCreate(
                [
                    'company_id' => $providerData['company_id'],
                    'name' => $providerData['name'],
                ],
                $providerData,
            );
        }
    }
}
