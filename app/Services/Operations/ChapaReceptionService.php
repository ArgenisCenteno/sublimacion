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
use Illuminate\Validation\ValidationException;

class ChapaReceptionService
{
    public function confirm(Provider $provider, Vehicle $vehicle, array $validated, UploadedFile $photo, Request $request): array
    {
        $latitude = (float) $validated['latitude'];
        $longitude = (float) $validated['longitude'];

        if (config('services.ops.enforce_geofence', true) && ! $provider->isWithinGeofence($latitude, $longitude)) {
            throw ValidationException::withMessages([
                'location' => 'La ubicacion actual esta fuera del radio permitido para este taller.',
            ]);
        }

        $disk = (string) config('services.ops.media_disk', 'public');
        $photoPath = $photo->store('ops/chapa/reception/' . $vehicle->id, $disk);

        return DB::transaction(function () use ($provider, $vehicle, $validated, $request, $disk, $photoPath, $photo) {
            $vehicle->forceFill([
                'physical_status' => 'taller_chapa',
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'location_updated_at' => now(),
            ])->save();

            $location = Location::create([
                'vehicle_id' => $vehicle->id,
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'accuracy_meters' => $validated['accuracy_meters'] ?? null,
                'location_type' => 'entrada_taller',
                'provider_id' => $provider->id,
                'pin_prefix' => $provider->pin_prefix,
                'device_info' => $request->userAgent(),
                'ip_address' => $request->ip(),
                'notes' => $validated['notes'] ?? null,
                'recorded_at' => now(),
            ]);

            $order = WorkshopOrder::query()
                ->where('vehicle_id', $vehicle->id)
                ->where('type', 'chapa')
                ->whereIn('status', ['pendiente', 'en_proceso', 'incidencia'])
                ->latest('id')
                ->first();

            if (! $order) {
                throw ValidationException::withMessages([
                    'vehicle_id' => 'No hay una orden pendiente de chapa para este vehiculo.',
                ]);
            }

            $order->provider_id = $provider->id;
            if ($order->status === 'pendiente') {
                $order->status = 'en_proceso';
            }
            $order->entry_at ??= now();
            $order->notes = $validated['notes'] ?? $order->notes;
            $order->save();

            $vehiclePhoto = VehiclePhoto::create([
                'vehicle_id' => $vehicle->id,
                'type' => 'frontal',
                'stage' => 'taller_chapa',
                'url' => $photoPath,
                'disk' => $disk,
                'size_bytes' => $photo->getSize(),
                'mime_type' => $photo->getMimeType(),
                'provider_id' => $provider->id,
                'is_mandatory' => false,
                'is_approved' => false,
            ]);

            return [
                'message' => 'Recepcion confirmada correctamente.',
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
                    'entry_at' => optional($order->entry_at)->toIso8601String(),
                ],
                'location' => [
                    'id' => $location->id,
                    'latitude' => (float) $location->latitude,
                    'longitude' => (float) $location->longitude,
                ],
                'photo' => [
                    'id' => $vehiclePhoto->id,
                    'url' => $vehiclePhoto->url,
                    'disk' => $vehiclePhoto->disk,
                ],
            ];
        });
    }
}
