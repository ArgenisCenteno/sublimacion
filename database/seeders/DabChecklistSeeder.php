<?php

namespace Database\Seeders;

use App\Models\Provider;
use App\Models\Vehicle;
use App\Models\WorkshopChecklistItem;
use App\Models\WorkshopChecklistTemplate;
use App\Models\WorkshopOrder;
use Illuminate\Database\Seeder;

class DabChecklistSeeder extends Seeder
{
    /**
     * Seed an example active DAB order with checklist items from the master template.
     */
    public function run(): void
    {
        $vehicle = Vehicle::query()->where('plate', 'QR345ST')->first();
        $provider = Provider::query()->where('type', 'dab')->first();

        if (! $vehicle || ! $provider) {
            return;
        }

        $vehicle->forceFill([
            'physical_status' => 'taller_dab',
            'location_updated_at' => now(),
        ])->save();

        $order = WorkshopOrder::query()->updateOrCreate(
            [
                'vehicle_id' => $vehicle->id,
                'type' => 'dab',
                'status' => 'en_proceso',
            ],
            [
                'provider_id' => $provider->id,
                'entry_at' => now()->subHours(2),
                'notes' => 'Orden DAB de ejemplo creada por seeder.',
            ],
        );

        $templates = WorkshopChecklistTemplate::query()
            ->where('type', 'dab')
            ->where('active', true)
            ->orderBy('sort_order')
            ->get(['label', 'sort_order']);

        foreach ($templates as $template) {
            WorkshopChecklistItem::query()->updateOrCreate(
                [
                    'workshop_order_id' => $order->id,
                    'label' => $template->label,
                ],
                [
                    'sort_order' => $template->sort_order,
                    'passed' => null,
                    'has_incident' => false,
                    'notes' => null,
                    'photo_url' => null,
                    'photo_disk' => 'local',
                    'checked_at' => null,
                ],
            );
        }
    }
}
