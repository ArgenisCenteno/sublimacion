<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleWorkflowPlan extends Model
{
    protected $fillable = [
        'vehicle_id',
        'requires_chapa',
        'requires_dab',
        'requires_limpieza',
        'created_by_provider_id',
    ];

    protected function casts(): array
    {
        return [
            'requires_chapa' => 'boolean',
            'requires_dab' => 'boolean',
            'requires_limpieza' => 'boolean',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function createdByProvider(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'created_by_provider_id');
    }

    public function selectedStages(): array
    {
        return array_values(array_filter([
            $this->requires_chapa ? 'chapa' : null,
            $this->requires_dab ? 'dab' : null,
            $this->requires_limpieza ? 'limpieza' : null,
        ]));
    }

    public function firstStage(): ?string
    {
        return $this->selectedStages()[0] ?? null;
    }

    public function nextStageAfter(string $currentStage): ?string
    {
        $stages = $this->selectedStages();
        $stageOrder = [
            'chapa' => 10,
            'dab' => 20,
            'limpieza' => 30,
        ];

        $currentWeight = $stageOrder[$currentStage] ?? 0;

        foreach ($stages as $stage) {
            if (($stageOrder[$stage] ?? 0) > $currentWeight) {
                return $stage;
            }
        }

        return null;
    }

    public function hasAnyStage(): bool
    {
        return $this->requires_chapa || $this->requires_dab || $this->requires_limpieza;
    }
}
