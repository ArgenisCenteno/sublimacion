<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Damage extends Model
{
    protected $fillable = [
        'vehicle_id',
        'x_coord',
        'y_coord',
        'body_part',
        'photo_url',
        'disk',
        'description',
        'severity',
        'provider_id',
        'stage',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'x_coord' => 'float',
            'y_coord' => 'float',
        ];
    }

    // ─── Relaciones ───────────────────────────────────────────────

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /** Proveedor que reportó el daño al escanear con su PIN */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────

    public function isPendiente(): bool    { return $this->status === 'pendiente'; }
    public function isEnReparacion(): bool { return $this->status === 'en_reparacion'; }
    public function isResuelto(): bool     { return $this->status === 'resuelto'; }

    public function isLeve(): bool         { return $this->severity === 'leve'; }
    public function isGrave(): bool        { return $this->severity === 'grave'; }
}
