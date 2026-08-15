<?php

namespace App\Services\Notifications;

use App\Models\Alert;
use App\Models\Damage;
use App\Models\User;
use App\Models\Vehicle;
use App\Services\Alerts\AlertCenterService;
use Illuminate\Support\Collection;

class NotificationFeedService
{
    public function __construct(
        private readonly AlertCenterService $alertCenterService
    ) {
    }

    public function forUser(?User $user): array
    {
        if (! $user) {
            return [
                'count' => 0,
                'items' => [],
            ];
        }

        $items = collect()
            ->merge($this->alertReminderItems($user))
            ->merge($this->pendingPickupItems($user))
            ->merge($this->damageItems($user))
            ->merge($this->availableVehicleItems($user))
            ->sortByDesc('timestamp')
            ->take(8)
            ->values()
            ->map(fn (array $item) => $this->serializeItem($item))
            ->all();

        return [
            'count' => count($items),
            'items' => $items,
        ];
    }

    private function alertReminderItems(User $user): Collection
    {
        if (! $user->can('alerts.view')) {
            return collect();
        }

        return Alert::query()
            ->where('status', 'pendiente')
            ->with('vehicle:id,plate,brand,model')
            ->latest('created_at')
            ->limit(4)
            ->get()
            ->map(function (Alert $alert) {
                $config = $this->alertCenterService->configForType($alert->type);
                $vehicleLabel = $alert->vehicle
                    ? trim("{$alert->vehicle->brand} {$alert->vehicle->model}")
                    : 'Vehiculo sin asignar';

                return [
                    'id' => "alert-{$alert->id}",
                    'kind' => 'alert',
                    'tone' => $this->toneForPriority($alert->priority),
                    'label' => 'Alerta',
                    'title' => $config['title'],
                    'message' => $alert->vehicle
                        ? "{$alert->vehicle->plate} · {$vehicleLabel}"
                        : $vehicleLabel,
                    'time' => optional($alert->created_at)?->locale('es')->diffForHumans(),
                    'timestamp' => optional($alert->created_at)?->timestamp ?? 0,
                    'href' => route('alerts'),
                ];
            });
    }

    private function pendingPickupItems(User $user): Collection
    {
        if (! $user->can('vehicles.view')) {
            return collect();
        }

        return Vehicle::query()
            ->where('physical_status', 'pendiente_recogida')
            ->latest('updated_at')
            ->limit(3)
            ->get(['id', 'plate', 'brand', 'model', 'updated_at'])
            ->map(function (Vehicle $vehicle) {
                return [
                    'id' => "pickup-{$vehicle->id}",
                    'kind' => 'pickup',
                    'tone' => 'amber',
                    'label' => 'Recogida',
                    'title' => "{$vehicle->plate} pendiente de recogida",
                    'message' => trim("{$vehicle->brand} {$vehicle->model}") . ' ya puede volver a campa.',
                    'time' => optional($vehicle->updated_at)?->locale('es')->diffForHumans(),
                    'timestamp' => optional($vehicle->updated_at)?->timestamp ?? 0,
                    'href' => route('vehicles.show', ['vehicle' => $vehicle, 'tab' => 'history']),
                ];
            });
    }

    private function damageItems(User $user): Collection
    {
        if (! $user->can('vehicles.view')) {
            return collect();
        }

        return Damage::query()
            ->with([
                'vehicle:id,plate,brand,model',
                'provider:id,name',
            ])
            ->latest('created_at')
            ->limit(4)
            ->get()
            ->map(function (Damage $damage) {
                $vehicle = $damage->vehicle;

                if (! $vehicle) {
                    return null;
                }

                $bodyPart = $damage->body_part ?: 'zona sin especificar';

                return [
                    'id' => "damage-{$damage->id}",
                    'kind' => 'damage',
                    'tone' => 'rose',
                    'label' => 'Incidencia',
                    'title' => "Incidencia marcada en {$vehicle->plate}",
                    'message' => "{$bodyPart} · {$damage->severity}" . ($damage->provider?->name ? " · {$damage->provider->name}" : ''),
                    'time' => optional($damage->created_at)?->locale('es')->diffForHumans(),
                    'timestamp' => optional($damage->created_at)?->timestamp ?? 0,
                    'href' => route('vehicles.show', ['vehicle' => $vehicle, 'tab' => 'history']),
                ];
            })
            ->filter();
    }

    private function availableVehicleItems(User $user): Collection
    {
        if (! $user->can('vehicles.view')) {
            return collect();
        }

        return Vehicle::query()
            ->where('physical_status', 'disponible')
            ->where('updated_at', '>=', now()->subDays(2))
            ->latest('updated_at')
            ->limit(3)
            ->get(['id', 'plate', 'brand', 'model', 'updated_at'])
            ->map(function (Vehicle $vehicle) {
                return [
                    'id' => "available-{$vehicle->id}",
                    'kind' => 'available',
                    'tone' => 'emerald',
                    'label' => 'Disponible',
                    'title' => "{$vehicle->plate} ya esta disponible",
                    'message' => trim("{$vehicle->brand} {$vehicle->model}") . ' quedo listo para seguir el flujo comercial.',
                    'time' => optional($vehicle->updated_at)?->locale('es')->diffForHumans(),
                    'timestamp' => optional($vehicle->updated_at)?->timestamp ?? 0,
                    'href' => route('vehicles.show', ['vehicle' => $vehicle]),
                ];
            });
    }

    private function serializeItem(array $item): array
    {
        unset($item['timestamp']);

        return $item;
    }

    private function toneForPriority(string $priority): string
    {
        return match ($priority) {
            'alta' => 'rose',
            'media' => 'amber',
            default => 'sky',
        };
    }
}
