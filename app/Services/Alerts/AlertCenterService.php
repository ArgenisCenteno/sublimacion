<?php

namespace App\Services\Alerts;

use App\Models\Alert;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\WorkshopOrder;
use Illuminate\Database\Eloquent\Collection;

class AlertCenterService
{
    public function sync(): void
    {
        $vehicles = Vehicle::query()
            ->with([
                'activeWorkshopOrder',
                'workshopOrders' => fn ($query) => $query->latest('id')->limit(3),
                'insurancePolicy',
            ])
            ->get();

        foreach ($vehicles as $vehicle) {
            $this->syncInsuranceActiveAlert($vehicle);
            $this->syncKeyMissingAlert($vehicle);
            $this->syncDocumentationIncompleteAlert($vehicle);
            $this->syncBottleneckAlert($vehicle);
            $this->syncTallerExitAlert($vehicle);
        }
    }

    public function configForType(string $type): array
    {
        return [
            'insurance_active' => [
                'title' => 'Vehiculo vendido con seguro activo',
                'segment' => 'critica',
                'role_target' => 'comercial',
                'action_label' => 'Desactivar seguro',
            ],
            'key_missing' => [
                'title' => 'Vehiculo vendido con una sola llave',
                'segment' => 'critica',
                'role_target' => 'operaciones',
                'action_label' => 'Pedir otra llave',
            ],
            'documentation_incomplete' => [
                'title' => 'Documentacion pendiente',
                'segment' => 'seguimiento',
                'role_target' => 'operaciones',
                'action_label' => 'Revisar documentos',
            ],
            'bottleneck' => [
                'title' => 'Cuello de botella',
                'segment' => 'seguimiento',
                'role_target' => 'operaciones',
                'action_label' => 'Revisar taller',
            ],
            'taller_exit' => [
                'title' => 'Salida de taller',
                'segment' => 'operativa',
                'role_target' => 'logistica',
                'action_label' => 'Coordinar recogida',
            ],
        ][$type] ?? [
            'title' => $type,
            'segment' => 'seguimiento',
            'role_target' => 'operaciones',
            'action_label' => 'Gestionar',
        ];
    }

    public function executePrimaryAction(Alert $alert, User $user): string
    {
        $alert->loadMissing('vehicle.insurancePolicy');

        return match ($alert->type) {
            'insurance_active' => $this->deactivateInsurance($alert, $user),
            'key_missing' => $this->requestSecondKey($alert, $user),
            'bottleneck' => $this->acknowledgeBottleneck($alert, $user),
            'taller_exit' => $this->coordinatePickup($alert, $user),
            default => $this->resolveAlert($alert, $user, 'Alerta gestionada manualmente.'),
        };
    }

    public function resolveAlert(Alert $alert, User $user, string $notes = ''): string
    {
        $alert->resolve($user, $notes);

        return 'Alerta resuelta correctamente.';
    }

    public function dismissAlert(Alert $alert, User $user, string $notes = ''): string
    {
        $alert->update([
            'status' => 'descartada',
            'resolved_by_user_id' => $user->id,
            'resolved_at' => now(),
            'resolution_notes' => $notes ?: 'Alerta descartada manualmente.',
        ]);

        return 'Alerta descartada correctamente.';
    }

    private function syncInsuranceActiveAlert(Vehicle $vehicle): void
    {
        if ($vehicle->hasInsuranceAlert()) {
            $this->ensurePendingAlert($vehicle, 'insurance_active', [
                'policy_number' => $vehicle->insurancePolicy?->policy_number,
                'insurance_status' => $vehicle->insurancePolicy?->status,
            ]);

            return;
        }

        $this->resolvePendingAlerts($vehicle->id, 'insurance_active', 'El seguro ya no se encuentra activo para este vehiculo.');
    }

    private function syncKeyMissingAlert(Vehicle $vehicle): void
    {
        if ($vehicle->hasKeyAlert()) {
            $latestAlert = $this->latestAlert($vehicle->id, 'key_missing');

            if ($latestAlert && in_array($latestAlert->status, ['resuelta', 'descartada'], true) && data_get($latestAlert->payload, 'second_key_requested')) {
                return;
            }

            $this->ensurePendingAlert($vehicle, 'key_missing', [
                'num_keys' => $vehicle->num_keys,
            ]);

            return;
        }

        $this->resolvePendingAlerts($vehicle->id, 'key_missing', 'El vehiculo ya no presenta incidencia por llaves.');
    }

    private function syncDocumentationIncompleteAlert(Vehicle $vehicle): void
    {
        if ($vehicle->hasDocumentationAlert()) {
            $this->ensurePendingAlert($vehicle, 'documentation_incomplete', [
                'missing_documents' => $vehicle->missingRequiredDocuments(),
                'commercial_status' => $vehicle->commercial_status,
            ]);

            return;
        }

        $this->resolvePendingAlerts($vehicle->id, 'documentation_incomplete', 'La documentacion del vehiculo ya esta al dia o todavia no hace falta revisarla.');
    }

    private function syncBottleneckAlert(Vehicle $vehicle): void
    {
        $activeOrder = $vehicle->activeWorkshopOrder;

        if ($activeOrder && $activeOrder->isBottleneck()) {
            $latestAlert = $this->latestAlert($vehicle->id, 'bottleneck');

            if ($latestAlert && in_array($latestAlert->status, ['resuelta', 'descartada'], true) && (int) data_get($latestAlert->payload, 'acknowledged_order_id') === $activeOrder->id) {
                return;
            }

            $this->ensurePendingAlert($vehicle, 'bottleneck', [
                'order_id' => $activeOrder->id,
                'days_in_workshop' => $activeOrder->days_in_workshop,
                'workshop_type' => $activeOrder->type,
            ]);

            return;
        }

        $this->resolvePendingAlerts($vehicle->id, 'bottleneck', 'El vehiculo ya no se encuentra bloqueado en taller.');
    }

    private function syncTallerExitAlert(Vehicle $vehicle): void
    {
        $latestOrder = $vehicle->workshopOrders
            ->first(fn (WorkshopOrder $order) => $order->type === 'limpieza' && $order->status === 'finalizado');

        if ($vehicle->isPendienteRecogida() && $latestOrder) {
            $latestAlert = $this->latestAlert($vehicle->id, 'taller_exit');

            if ($latestAlert && in_array($latestAlert->status, ['resuelta', 'descartada'], true) && (int) data_get($latestAlert->payload, 'order_id') === $latestOrder->id) {
                return;
            }

            $this->ensurePendingAlert($vehicle, 'taller_exit', [
                'order_id' => $latestOrder->id,
                'workshop_type' => $latestOrder->type,
                'finished_at' => optional($latestOrder->exit_at)?->toIso8601String(),
            ]);

            return;
        }

        $this->resolvePendingAlerts($vehicle->id, 'taller_exit', 'La recogida del vehiculo ya no esta pendiente.');
    }

    private function ensurePendingAlert(Vehicle $vehicle, string $type, array $payload = []): void
    {
        $existing = Alert::query()
            ->where('vehicle_id', $vehicle->id)
            ->where('type', $type)
            ->where('status', 'pendiente')
            ->first();

        if ($existing) {
            $existing->update([
                'priority' => Alert::PRIORITY_MAP[$type] ?? $existing->priority,
                'payload' => array_merge($existing->payload ?? [], $payload),
            ]);

            return;
        }

        Alert::fire($type, $vehicle->id, $payload);
    }

    private function resolvePendingAlerts(int $vehicleId, string $type, string $notes): void
    {
        Alert::query()
            ->where('vehicle_id', $vehicleId)
            ->where('type', $type)
            ->where('status', 'pendiente')
            ->get()
            ->each(function (Alert $alert) use ($notes) {
                $alert->update([
                    'status' => 'resuelta',
                    'resolved_at' => now(),
                    'resolution_notes' => $notes,
                ]);
            });
    }

    private function latestAlert(int $vehicleId, string $type): ?Alert
    {
        return Alert::query()
            ->where('vehicle_id', $vehicleId)
            ->where('type', $type)
            ->latest('id')
            ->first();
    }

    private function deactivateInsurance(Alert $alert, User $user): string
    {
        $vehicle = $alert->vehicle;

        if (! $vehicle) {
            return $this->resolveAlert($alert, $user, 'La alerta se resolvio sin vehiculo asociado.');
        }

        if ($vehicle->insurancePolicy) {
            $vehicle->insurancePolicy->requestDeactivation();
        }

        $vehicle->forceFill([
            'insurance_active' => false,
        ])->save();

        $alert->resolve($user, 'Se solicito la baja del seguro de flota.');

        return 'Se marco la baja del seguro de flota para este vehiculo.';
    }

    private function requestSecondKey(Alert $alert, User $user): string
    {
        $payload = $alert->payload ?? [];
        $payload['second_key_requested'] = true;
        $payload['requested_at'] = now()->toIso8601String();

        $alert->update([
            'payload' => $payload,
            'status' => 'resuelta',
            'resolved_by_user_id' => $user->id,
            'resolved_at' => now(),
            'resolution_notes' => 'Se registro la solicitud de una segunda llave.',
        ]);

        return 'Se registro la solicitud de la segunda llave.';
    }

    private function acknowledgeBottleneck(Alert $alert, User $user): string
    {
        $payload = $alert->payload ?? [];
        $payload['acknowledged_order_id'] = data_get($payload, 'order_id');
        $payload['acknowledged_at'] = now()->toIso8601String();

        $alert->update([
            'payload' => $payload,
            'status' => 'resuelta',
            'resolved_by_user_id' => $user->id,
            'resolved_at' => now(),
            'resolution_notes' => 'Cuello de botella revisado por operaciones.',
        ]);

        return 'Se marco el cuello de botella como revisado.';
    }

    private function coordinatePickup(Alert $alert, User $user): string
    {
        $payload = $alert->payload ?? [];
        $payload['pickup_coordinated'] = true;
        $payload['pickup_coordinated_at'] = now()->toIso8601String();

        $alert->update([
            'payload' => $payload,
            'status' => 'resuelta',
            'resolved_by_user_id' => $user->id,
            'resolved_at' => now(),
            'resolution_notes' => 'Recogida logistica coordinada.',
        ]);

        return 'Se marco la recogida logistica como coordinada.';
    }
}


