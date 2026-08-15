<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehiclePhoto extends Model
{
    protected $fillable = [
        'vehicle_id',
        'type',
        'stage',
        'url',
        'disk',
        'size_bytes',
        'mime_type',
        'provider_id',
        'is_mandatory',
        'is_approved',
    ];

    protected function casts(): array
    {
        return [
            'is_mandatory' => 'boolean',
            'is_approved'  => 'boolean',
            'size_bytes'   => 'integer',
        ];
    }

    // ─── Tipos obligatorios en recepción ──────────────────────────
    const MANDATORY_TYPES = [
        'frontal',
        'trasera',
        'lateral_izq',
        'lateral_der',
        'interior_km',
    ];

    // ─── Relaciones ───────────────────────────────────────────────

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────

    public function isMandatory(): bool  { return $this->is_mandatory; }
    public function isApproved(): bool   { return $this->is_approved; }
    public function isComercial(): bool  { return $this->type === 'comercial'; }
    public function isDano(): bool       { return $this->type === 'dano'; }
}
