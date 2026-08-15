<?php

namespace App\Services\Operations;

use App\Models\Provider;
use App\Models\Vehicle;
use App\Models\WorkshopChecklistItem;
use App\Models\WorkshopOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DabChecklistService
{
    public function update(Provider $provider, Vehicle $vehicle, array $validated): array
    {
        return DB::transaction(function () use ($provider, $vehicle, $validated) {
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

            $item = $order->checklistItems()->whereKey($validated['checklist_item_id'])->first();

            if (! $item) {
                throw ValidationException::withMessages([
                    'checklist_item_id' => 'El item del checklist no pertenece a la orden activa.',
                ]);
            }

            $passed = (bool) $validated['passed'];
            $hasIncident = ! $passed;

            $item->forceFill([
                'passed' => $passed,
                'has_incident' => $hasIncident,
                'notes' => $validated['notes'] ?? null,
                'checked_at' => now(),
            ])->save();

            $order->provider_id = $provider->id;
            $order->status = 'en_proceso';
            $order->save();

            return [
                'message' => $hasIncident
                    ? 'Item revisado. La observacion quedo registrada.'
                    : 'Item revisado correctamente.',
                'workshop_order' => [
                    'id' => $order->id,
                    'status' => $order->status,
                ],
                'checklist_item' => [
                    'id' => $item->id,
                    'label' => $item->label,
                    'sort_order' => $item->sort_order,
                    'passed' => $item->passed,
                    'has_incident' => $item->has_incident,
                    'notes' => $item->notes,
                ],
                'checklist_items' => $order->checklistItems()
                    ->get()
                    ->map(fn (WorkshopChecklistItem $checklistItem) => [
                        'id' => $checklistItem->id,
                        'label' => $checklistItem->label,
                        'sort_order' => $checklistItem->sort_order,
                        'passed' => $checklistItem->passed,
                        'has_incident' => $checklistItem->has_incident,
                        'notes' => $checklistItem->notes,
                    ])
                    ->all(),
            ];
        });
    }
}
