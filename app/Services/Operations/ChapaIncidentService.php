<?php

namespace App\Services\Operations;

use App\Models\Damage;
use App\Models\Provider;
use App\Models\Vehicle;
use App\Models\VehiclePhoto;
use App\Models\WorkshopOrder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class ChapaIncidentService
{
    public function report(Provider $provider, Vehicle $vehicle, array $validated): array
    {
        $disk = (string) config('services.ops.media_disk', 'public');

        return DB::transaction(function () use ($provider, $vehicle, $validated, $disk) {
            $order = WorkshopOrder::query()
                ->where('vehicle_id', $vehicle->id)
                ->where('type', 'chapa')
                ->whereIn('status', ['pendiente', 'en_proceso', 'incidencia'])
                ->latest('id')
                ->first();

            if ($order && ! empty($validated['notes'])) {
                $order->notes = $validated['notes'];
                $order->save();
            }

            $damage = Damage::create([
                'vehicle_id' => $vehicle->id,
                'x_coord' => 0.5000,
                'y_coord' => 0.5000,
                'photo_url' => null,
                'disk' => $disk,
                'description' => $validated['description'],
                'severity' => $validated['severity'],
                'provider_id' => $provider->id,
                'stage' => 'taller_chapa',
                'status' => 'pendiente',
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach ($validated['photos'] ?? [] as $index => $photo) {
                if (! $photo instanceof UploadedFile) {
                    continue;
                }

                $path = $photo->store('ops/chapa/incidents/' . $vehicle->id, $disk);

                if ($index === 0) {
                    $damage->forceFill([
                        'photo_url' => $path,
                        'disk' => $disk,
                    ])->save();
                }

                VehiclePhoto::create([
                    'vehicle_id' => $vehicle->id,
                    'type' => 'dano',
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

            return [
                'message' => 'Incidencia registrada correctamente.',
                'damage' => [
                    'id' => $damage->id,
                    'description' => $damage->description,
                    'severity' => $damage->severity,
                    'status' => $damage->status,
                ],
            ];
        });
    }
}
