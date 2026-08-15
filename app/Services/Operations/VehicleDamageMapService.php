<?php

namespace App\Services\Operations;

use App\Models\Damage;
use App\Models\Provider;
use App\Models\Vehicle;
use App\Models\VehiclePhoto;
use App\Models\WorkshopOrder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VehicleDamageMapService
{
    public function report(Provider $provider, Vehicle $vehicle, array $validated, string $stage): array
    {
        return DB::transaction(function () use ($provider, $vehicle, $validated, $stage) {
            $disk = (string) config('services.ops.media_disk', 'public');

            $damage = Damage::query()->create([
                'vehicle_id' => $vehicle->id,
                'x_coord' => $validated['x_coord'],
                'y_coord' => $validated['y_coord'],
                'body_part' => $validated['body_part'],
                'photo_url' => null,
                'disk' => $disk,
                'description' => $this->buildDescription($validated['body_part']),
                'severity' => $validated['severity'],
                'provider_id' => $provider->id,
                'stage' => $stage,
                'status' => 'pendiente',
                'notes' => $validated['notes'] ?? null,
            ]);

            foreach (($validated['photos'] ?? []) as $index => $photo) {
                if (! $photo instanceof UploadedFile) {
                    continue;
                }

                $path = $photo->store($this->photoDirectoryForStage($stage, $vehicle->id), $disk);

                if ($index === 0) {
                    $damage->forceFill([
                        'photo_url' => $path,
                        'disk' => $disk,
                    ])->save();
                }

                VehiclePhoto::create([
                    'vehicle_id' => $vehicle->id,
                    'type' => 'dano',
                    'stage' => $stage,
                    'url' => $path,
                    'disk' => $disk,
                    'size_bytes' => $photo->getSize(),
                    'mime_type' => $photo->getMimeType(),
                    'provider_id' => $provider->id,
                    'is_mandatory' => false,
                    'is_approved' => false,
                ]);
            }

            $this->syncWorkshopOrderNotes($vehicle, $stage, $validated['notes'] ?? null);

            return [
                'message' => 'La marca se guardo correctamente.',
                'damage' => $this->serializeDamage($damage->fresh(['provider'])),
            ];
        });
    }

    public function listForVehicle(Vehicle $vehicle): array
    {
        return $vehicle->damages()
            ->with('provider:id,name')
            ->orderByRaw("CASE status WHEN 'pendiente' THEN 1 WHEN 'en_reparacion' THEN 2 ELSE 3 END")
            ->latest('created_at')
            ->get()
            ->map(fn (Damage $damage) => $this->serializeDamage($damage))
            ->all();
    }

    private function syncWorkshopOrderNotes(Vehicle $vehicle, string $stage, ?string $notes): void
    {
        $orderType = match ($stage) {
            'taller_chapa' => 'chapa',
            'taller_dab' => 'dab',
            'taller_limpieza' => 'limpieza',
            default => null,
        };

        if (! $orderType) {
            return;
        }

        $order = WorkshopOrder::query()
            ->where('vehicle_id', $vehicle->id)
            ->where('type', $orderType)
            ->whereIn('status', ['pendiente', 'en_proceso', 'incidencia'])
            ->latest('id')
            ->first();

        if (! $order) {
            return;
        }

        if ($notes && trim($notes) !== '') {
            $order->notes = trim($notes);
            $order->save();
        }
    }

    private function buildDescription(string $bodyPart): string
    {
        return 'Marca en ' . $this->bodyPartLabel($bodyPart);
    }

    private function photoDirectoryForStage(string $stage, int $vehicleId): string
    {
        return match ($stage) {
            'taller_chapa' => "ops/chapa/damages/{$vehicleId}",
            'taller_dab' => "ops/dab/damages/{$vehicleId}",
            'taller_limpieza' => "ops/limpieza/damages/{$vehicleId}",
            default => "ops/recepcion/damages/{$vehicleId}",
        };
    }

    private function bodyPartLabel(string $bodyPart): string
    {
        return match ($bodyPart) {
            'front_bumper' => 'parachoques delantero',
            'hood' => 'capo',
            'windshield' => 'parabrisas',
            'roof' => 'techo',
            'rear_glass' => 'luna trasera',
            'trunk' => 'porton trasero',
            'rear_bumper' => 'parachoques trasero',
            'front_left_fender' => 'guardabarros delantero izquierdo',
            'front_right_fender' => 'guardabarros delantero derecho',
            'front_left_wheel' => 'rueda delantera izquierda',
            'front_right_wheel' => 'rueda delantera derecha',
            'left_front_door' => 'puerta delantera izquierda',
            'right_front_door' => 'puerta delantera derecha',
            'left_rear_door' => 'puerta trasera izquierda',
            'right_rear_door' => 'puerta trasera derecha',
            'rear_left_wheel' => 'rueda trasera izquierda',
            'rear_right_wheel' => 'rueda trasera derecha',
            'rear_left_panel' => 'lateral trasero izquierdo',
            'rear_right_panel' => 'lateral trasero derecho',
            default => str_replace('_', ' ', $bodyPart),
        };
    }

    private function serializeDamage(Damage $damage): array
    {
        return [
            'id' => $damage->id,
            'x_coord' => (float) $damage->x_coord,
            'y_coord' => (float) $damage->y_coord,
            'body_part' => $damage->body_part,
            'body_part_label' => $damage->body_part ? $this->bodyPartLabel($damage->body_part) : 'Zona sin definir',
            'description' => $damage->description,
            'severity' => $damage->severity,
            'status' => $damage->status,
            'notes' => $damage->notes,
            'stage' => $damage->stage,
            'provider' => $damage->provider?->name,
            'created_at' => optional($damage->created_at)->format('d/m H:i'),
        ];
    }
}
