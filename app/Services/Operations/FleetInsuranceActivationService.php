<?php

namespace App\Services\Operations;

use App\Models\Vehicle;

class FleetInsuranceActivationService
{
    public function activateForVehicle(Vehicle $vehicle): array
    {
        // Aqui ira la llamada a la API del seguro de flota.
        // Ejemplo futuro:
        // $response = Http::post(config('services.fleet_insurance.base_url') . '/policies/activate', [
        //     'vehicle_id' => $vehicle->id,
        //     'plate' => $vehicle->plate,
        //     'vin' => $vehicle->vin,
        // ]);
        //
        // return $response->json();

        return [
            'triggered' => false,
            'status' => 'pending_integration',
            'message' => 'Integracion con el seguro de flota pendiente.',
        ];
    }
}
