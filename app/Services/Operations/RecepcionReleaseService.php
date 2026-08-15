<?php

namespace App\Services\Operations;

use App\Models\Location;
use App\Models\Provider;
use App\Models\Vehicle;
use App\Models\VehiclePhoto;
use App\Models\VehicleWorkflowPlan;
use App\Models\WorkshopOrder;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RecepcionReleaseService
{
    public function __construct(
        private readonly FleetInsuranceActivationService $fleetInsuranceActivationService,
    ) {
    }

    public function release(Provider $provider, Vehicle $vehicle, array $validated, Request $request): array
    {
        $latitude = (float) $validated['latitude'];
        $longitude = (float) $validated['longitude'];
        $disk = (string) config('services.ops.media_disk', 'public');

        if (config('services.ops.enforce_geofence', true) && ! $provider->isWithinGeofence($latitude, $longitude)) {
            throw ValidationException::withMessages([
                'location' => 'La ubicacion actual esta fuera del radio permitido para esta campa.',
            ]);
        }

        return DB::transaction(function () use ($provider, $vehicle, $validated, $request, $disk) {
            $isInitialReception = $vehicle->physical_status === 'en_transito';
            $isFinalReception = $vehicle->physical_status === 'pendiente_recogida';
            $plan = null;

            if (! $isInitialReception && ! $isFinalReception) {
                throw ValidationException::withMessages([
                    'vehicle_id' => 'Este vehiculo no puede procesarse en campa desde su estado actual.',
                ]);
            }

            if ($isInitialReception && empty($validated['photos'])) {
                throw ValidationException::withMessages([
                    'photos' => 'Debes adjuntar al menos una foto del vehiculo antes de liberarlo.',
                ]);
            }

            $vehicle->forceFill([
                'physical_status' => $isInitialReception ? 'en_campa' : 'disponible',
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'location_updated_at' => now(),
                'notes' => $validated['notes'] ?? $vehicle->notes,
            ])->save();

            $location = Location::create([
                'vehicle_id' => $vehicle->id,
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'accuracy_meters' => $validated['accuracy_meters'] ?? null,
                'location_type' => 'recepcion_campa',
                'provider_id' => $provider->id,
                'pin_prefix' => $provider->pin_prefix,
                'device_info' => $request->userAgent(),
                'ip_address' => $request->ip(),
                'notes' => $validated['notes'] ?? null,
                'recorded_at' => now(),
            ]);

            if ($isInitialReception) {
                $selectedStages = $validated['workflow_plan'] ?? [];
                $plan = VehicleWorkflowPlan::query()->firstOrNew([
                    'vehicle_id' => $vehicle->id,
                ]);

                $plan->fill([
                    'requires_chapa' => in_array('chapa', $selectedStages, true),
                    'requires_dab' => in_array('dab', $selectedStages, true),
                    'requires_limpieza' => in_array('limpieza', $selectedStages, true),
                    'created_by_provider_id' => $provider->id,
                ]);
                $plan->save();

                $firstStage = $plan->firstStage();

                if ($firstStage) {
                    $nextOrder = WorkshopOrder::query()
                        ->where('vehicle_id', $vehicle->id)
                        ->where('type', $firstStage)
                        ->whereIn('status', ['pendiente', 'en_proceso', 'incidencia'])
                        ->latest('id')
                        ->first();

                    if (! $nextOrder) {
                        $nextOrder = new WorkshopOrder([
                            'vehicle_id' => $vehicle->id,
                            'type' => $firstStage,
                        ]);
                    }

                    $nextOrder->provider_id = null;
                    $nextOrder->status = 'pendiente';
                    $nextOrder->notes = $validated['notes'] ?? $nextOrder->notes;
                    $nextOrder->save();
                }
            }

            $photoCount = 0;
            $insuranceActivation = null;

            if ($isInitialReception) {
                foreach (($validated['photos'] ?? []) as $photo) {
                    if (! $photo instanceof UploadedFile) {
                        continue;
                    }

                    $path = $photo->store('ops/recepcion/release/' . $vehicle->id, $disk);

                    VehiclePhoto::create([
                        'vehicle_id' => $vehicle->id,
                        'type' => 'salida_taller',
                        'stage' => 'recepcion',
                        'url' => $path,
                        'disk' => $disk,
                        'size_bytes' => $photo->getSize(),
                        'mime_type' => $photo->getMimeType(),
                        'provider_id' => $provider->id,
                        'is_mandatory' => false,
                        'is_approved' => false,
                    ]);

                    $photoCount++;
                }

                if ($plan && $plan->firstStage()) {
                    $insuranceActivation = $this->fleetInsuranceActivationService->activateForVehicle($vehicle);
                }
            }

            return [
                'message' => $isInitialReception
                    ? ($plan && $plan->firstStage()
                        ? 'Vehiculo liberado correctamente y listo para continuar con su flujo de talleres.'
                        : 'Vehiculo recepcionado en campa sin etapas de taller. El seguro de flota no se activara.')
                    : 'Vehiculo recepcionado en campa y marcado como disponible.',
                'workflow_stage' => $isInitialReception ? 'initial_reception' : 'final_reception',
                'vehicle' => [
                    'id' => $vehicle->id,
                    'plate' => $vehicle->plate,
                    'brand' => $vehicle->brand,
                    'model' => $vehicle->model,
                    'version' => $vehicle->version,
                    'color' => $vehicle->color,
                    'physical_status' => $vehicle->physical_status,
                    'commercial_status' => $vehicle->commercial_status,
                ],
                'location' => [
                    'id' => $location->id,
                    'latitude' => (float) $location->latitude,
                    'longitude' => (float) $location->longitude,
                ],
                'next_workshop_order' => $isInitialReception && $plan && $plan->firstStage() ? [
                    'type' => $plan->firstStage(),
                    'status' => 'pendiente',
                ] : null,
                'workflow_plan' => $isInitialReception ? [
                    'chapa' => $plan->requires_chapa,
                    'dab' => $plan->requires_dab,
                    'limpieza' => $plan->requires_limpieza,
                ] : null,
                'photo_count' => $photoCount,
                'insurance' => $insuranceActivation,
                'insurance_skipped' => $isInitialReception && $plan && ! $plan->firstStage(),
            ];
        });
    }
}
