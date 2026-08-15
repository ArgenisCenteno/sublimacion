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

class ChapaFinishService
{
    public function finish(Provider $provider, Vehicle $vehicle, array $validated, Request $request): array
    {
        $disk = (string) config('services.ops.media_disk', 'public');

        return DB::transaction(function () use ($provider, $vehicle, $validated, $request, $disk) {
            $order = WorkshopOrder::query()
                ->where('vehicle_id', $vehicle->id)
                ->where('type', 'chapa')
                ->whereIn('status', ['en_proceso', 'incidencia'])
                ->latest('id')
                ->first();

            if (! $order) {
                throw ValidationException::withMessages([
                    'vehicle_id' => 'No encontramos una orden activa de chapa para este vehiculo.',
                ]);
            }

            $firstPhotoPath = null;

            foreach ($validated['photos'] as $index => $photo) {
                if (! $photo instanceof UploadedFile) {
                    continue;
                }

                $path = $photo->store('ops/chapa/exit/' . $vehicle->id, $disk);
                $firstPhotoPath ??= $path;

                VehiclePhoto::create([
                    'vehicle_id' => $vehicle->id,
                    'type' => 'salida_taller',
                    'stage' => 'taller_chapa',
                    'url' => $path,
                    'disk' => $disk,
                    'size_bytes' => $photo->getSize(),
                    'mime_type' => $photo->getMimeType(),
                    'provider_id' => $provider->id,
                    'is_mandatory' => false,
                    'is_approved' => false,
                ]);
            }

            $order->provider_id = $provider->id;
            $order->status = 'finalizado';
            $order->entry_at ??= now();
            $order->exit_at = now();
            $order->exit_photo_url = $firstPhotoPath;
            $order->exit_photo_disk = $disk;
            $order->notes = $validated['notes'] ?? $order->notes;
            $order->save();

            $plan = VehicleWorkflowPlan::query()->where('vehicle_id', $vehicle->id)->first();
            $nextStage = $plan?->nextStageAfter('chapa');

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
                $nextOrder->notes = $nextOrder->notes ?? 'Orden generada automaticamente al cerrar chapa.';
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
                    ? 'Reparacion finalizada correctamente. La siguiente etapa ya quedo preparada.'
                    : 'Reparacion finalizada correctamente. El vehiculo queda pendiente de recogida.',
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
