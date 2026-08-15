<?php

namespace App\Services\Operations;

use App\Models\Location;
use App\Models\Provider;
use App\Models\Vehicle;
use App\Models\VehicleWorkflowPlan;
use App\Models\WorkshopOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DabFinishService
{
    public function finish(Provider $provider, Vehicle $vehicle, array $validated, Request $request): array
    {
        return DB::transaction(function () use ($provider, $vehicle, $validated, $request) {
            $order = WorkshopOrder::query()
                ->where('vehicle_id', $vehicle->id)
                ->where('type', 'dab')
                ->whereIn('status', ['en_proceso', 'incidencia'])
                ->latest('id')
                ->first();

            if (! $order) {
                throw ValidationException::withMessages([
                    'vehicle_id' => 'No encontramos una orden DAB activa para este vehiculo.',
                ]);
            }

            if (! $order->canBeFinished()) {
                throw ValidationException::withMessages([
                    'checklist' => 'Debes revisar todos los puntos antes de finalizar DAB.',
                ]);
            }

            $order->provider_id = $provider->id;
            $order->status = 'finalizado';
            $order->entry_at ??= now();
            $order->exit_at = now();
            $order->notes = $validated['notes'] ?? $order->notes;
            $order->save();

            $plan = VehicleWorkflowPlan::query()->where('vehicle_id', $vehicle->id)->first();
            $nextStage = $plan?->nextStageAfter('dab');

            $nextOrder = null;

            if ($nextStage) {
                $nextOrder = WorkshopOrder::query()
                    ->where('vehicle_id', $vehicle->id)
                    ->where('type', $nextStage)
                    ->whereIn('status', ['pendiente', 'en_proceso', 'incidencia'])
                    ->latest('id')
                    ->first();

                if (! $nextOrder) {
                    $nextOrder = new WorkshopOrder([
                        'vehicle_id' => $vehicle->id,
                        'type' => $nextStage,
                    ]);
                }

                $nextOrder->provider_id = null;
                $nextOrder->status = 'pendiente';
                $nextOrder->notes = $nextOrder->notes ?? 'Orden generada automaticamente al cerrar DAB.';
                $nextOrder->save();
            }

            $vehicle->forceFill([
                'physical_status' => $nextStage ? 'en_campa' : 'pendiente_recogida',
                'latitude' => $validated['latitude'] ?? $vehicle->latitude,
                'longitude' => $validated['longitude'] ?? $vehicle->longitude,
                'location_updated_at' => now(),
            ])->save();

            $location = null;

            if (isset($validated['latitude'], $validated['longitude'])) {
                $location = Location::create([
                    'vehicle_id' => $vehicle->id,
                    'latitude' => $validated['latitude'],
                    'longitude' => $validated['longitude'],
                    'accuracy_meters' => $validated['accuracy_meters'] ?? null,
                    'location_type' => 'salida_taller',
                    'provider_id' => $provider->id,
                    'pin_prefix' => $provider->pin_prefix,
                    'device_info' => $request->userAgent(),
                    'ip_address' => $request->ip(),
                    'notes' => $validated['notes'] ?? null,
                    'recorded_at' => now(),
                ]);
            }

            return [
                'message' => $nextStage
                    ? 'Revision DAB finalizada correctamente. La siguiente etapa ya quedo preparada.'
                    : 'Revision DAB finalizada correctamente. El vehiculo queda pendiente de recogida.',
                'vehicle' => [
                    'id' => $vehicle->id,
                    'plate' => $vehicle->plate,
                    'brand' => $vehicle->brand,
                    'model' => $vehicle->model,
                    'physical_status' => $vehicle->physical_status,
                ],
                'workshop_order' => [
                    'id' => $order->id,
                    'status' => $order->status,
                    'exit_at' => optional($order->exit_at)->toIso8601String(),
                ],
                'next_workshop_order' => $nextOrder ? [
                    'type' => $nextOrder->type,
                    'status' => $nextOrder->status,
                ] : null,
                'location' => $location ? [
                    'id' => $location->id,
                    'latitude' => (float) $location->latitude,
                    'longitude' => (float) $location->longitude,
                ] : null,
            ];
        });
    }
}
