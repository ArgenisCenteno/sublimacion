<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Location extends Model
{
    protected $fillable = [
        'vehicle_id',
        'latitude',
        'longitude',
        'accuracy_meters',
        'location_type',
        'provider_id',
        'pin_prefix',
        'device_info',
        'ip_address',
        'notes',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'latitude'       => 'float',
            'longitude'      => 'float',
            'accuracy_meters'=> 'float',
            'recorded_at'    => 'datetime',
        ];
    }

    // ─── Relaciones ───────────────────────────────────────────────

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /** Proveedor que generó el ping GPS (siempre accede con PIN) */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────

    /** ¿Este ping fue generado por una recepción en campa? (PIN 4XXX) */
    public function isRecepcion(): bool
    {
        return $this->location_type === 'recepcion_campa';
    }

    /** ¿Este ping fue una entrada a taller? (PIN 1XXX/2XXX/3XXX) */
    public function isEntradaTaller(): bool
    {
        return $this->location_type === 'entrada_taller';
    }

    /** ¿Este ping fue una salida de taller? */
    public function isSalidaTaller(): bool
    {
        return $this->location_type === 'salida_taller';
    }
}
