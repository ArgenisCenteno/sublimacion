<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InsurancePolicy extends Model
{
    protected $fillable = [
        'vehicle_id',
        'policy_number',
        'occident_policy_id',
        'status',
        'activated_at',
        'deactivated_at',
        'activated_by_provider_id',
        'api_response',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'activated_at'   => 'datetime',
            'deactivated_at' => 'datetime',
            'api_response'   => 'array',
        ];
    }

    // ─── Relaciones ───────────────────────────────────────────────

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /** Proveedor (PIN 4XXX) que marcó el check de seguro en recepción */
    public function activatedByProvider(): BelongsTo
    {
        return $this->belongsTo(Provider::class, 'activated_by_provider_id');
    }

    // ─── Helpers ──────────────────────────────────────────────────

    public function isPendienteAlta(): bool  { return $this->status === 'pendiente_alta'; }
    public function isActiva(): bool         { return $this->status === 'activa'; }
    public function isPendienteBaja(): bool  { return $this->status === 'pendiente_baja'; }
    public function isBaja(): bool           { return $this->status === 'baja'; }

    /** Solicita la baja en Occident — trigger: vehículo vendido */
    public function requestDeactivation(): void
    {
        $this->update([
            'status'         => 'pendiente_baja',
            'deactivated_at' => now(),
        ]);
    }
}
