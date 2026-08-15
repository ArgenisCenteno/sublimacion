<?php

namespace App\Services\Operations;

use App\Models\Location;
use App\Models\Provider;
use App\Models\Vehicle;
use App\Models\WorkshopChecklistItem;
use App\Models\WorkshopChecklistTemplate;
use App\Models\WorkshopOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DabReceptionService
{
    public function confirm(Provider $provider, Vehicle $vehicle, array $validated, Request $request): array
    {
        $latitude = (float) $validated['latitude'];
        $longitude = (float) $validated['longitude'];

        if (config('services.ops.enforce_geofence', true) && ! $provider->isWithinGeofence($latitude, $longitude)) {
            throw ValidationException::withMessages([
                'location' => 'La ubicacion actual esta fuera del radio permitido para este taller.',
            ]);
        }

        return DB::transaction(function () use ($provider, $vehicle, $validated, $request) {
            $vehicle->forceFill([
                'physical_status' => 'taller_dab',
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
                ->where('type', 'dab')
                ->whereIn('status', ['pendiente', 'en_proceso', 'incidencia'])
                ->latest('id')
                ->first();

            if (! $order) {
                throw ValidationException::withMessages([
                    'vehicle_id' => 'No hay una orden pendiente de DAB para este vehiculo.',
                ]);
            }

            $order->provider_id = $provider->id;
            if ($order->status === 'pendiente') {
                $order->status = 'en_proceso';
            }
            $order->entry_at ??= now();
            $order->notes = $validated['notes'] ?? $order->notes;
            $order->save();

            $this->seedChecklistItems($order);

            return [
                'message' => 'Recepcion DAB confirmada correctamente.',
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
                'checklist_items' => $this->serializeChecklistItems($order),
            ];
        });
    }

    private function seedChecklistItems(WorkshopOrder $order): void
    {
        if ($order->checklistItems()->exists()) {
            return;
        }

        $templates = WorkshopChecklistTemplate::query()
            ->where('type', 'dab')
            ->where('active', true)
            ->orderBy('sort_order')
            ->get(['label', 'sort_order']);

        foreach ($templates as $template) {
            WorkshopChecklistItem::create([
                'workshop_order_id' => $order->id,
                'label' => $template->label,
                'sort_order' => $template->sort_order,
                'passed' => null,
                'has_incident' => false,
                'notes' => null,
                'photo_url' => null,
                'photo_disk' => 'local',
                'checked_at' => null,
            ]);
        }
    }

    private function serializeChecklistItems(WorkshopOrder $order): array
    {
        return $order->checklistItems()
            ->get()
            ->map(fn (WorkshopChecklistItem $item) => [
                'id' => $item->id,
                'label' => $item->label,
                'sort_order' => $item->sort_order,
                'passed' => $item->passed,
                'has_incident' => $item->has_incident,
                'notes' => $item->notes,
            ])
            ->all();
    }
}
