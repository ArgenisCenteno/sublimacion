<?php

namespace App\Services\Operations;

use App\Models\Location;
use App\Models\Provider;
use App\Models\Vehicle;
use App\Models\VehiclePhoto;
use App\Models\WorkshopOrder;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class LimpiezaFinishService
{
    public function finish(Provider $provider, Vehicle $vehicle, array $validated, Request $request): array
    {
        $disk = (string) config('services.ops.media_disk', 'public');

        return DB::transaction(function () use ($provider, $vehicle, $validated, $request, $disk) {
            $order = WorkshopOrder::query()
                ->where('vehicle_id', $vehicle->id)
                ->where('type', 'limpieza')
                ->whereIn('status', ['pendiente', 'en_proceso', 'incidencia'])
                ->latest('id')
                ->first();

            if (! $order) {
                $order = new WorkshopOrder([
                    'vehicle_id' => $vehicle->id,
                    'type' => 'limpieza',
                ]);
            }

            $firstPhotoPath = null;

            foreach ($validated['photos'] as $photo) {
                if (! $photo instanceof UploadedFile) {
                    continue;
                }

                $path = $photo->store('ops/limpieza/final/' . $vehicle->id, $disk);
                $firstPhotoPath ??= $path;

                VehiclePhoto::create([
                    'vehicle_id' => $vehicle->id,
                    'type' => 'comercial',
                    'stage' => 'taller_limpieza',
                    'url' => $path,
                    'disk' => $disk,
                    'size_bytes' => $photo->getSize(),
                    'mime_type' => $photo->getMimeType(),
                    'provider_id' => $provider->id,
                    'is_mandatory' => false,
                    'is_approved' => true,
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

            $vehicle->forceFill([
                'physical_status' => 'pendiente_recogida',
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
                'message' => 'Limpieza finalizada correctamente.',
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
                'location' => $location ? [
                    'id' => $location->id,
                    'latitude' => (float) $location->latitude,
                    'longitude' => (float) $location->longitude,
                ] : null,
            ];
        });
    }
}
