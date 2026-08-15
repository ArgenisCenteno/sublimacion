<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Provider extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'company_id',
        'name',
        'type',
        'pin',
        'pin_prefix',
        'latitude',
        'longitude',
        'geofence_radius_meters',
        'email',
        'phone',
        'tax_id',
        'active',
    ];

    protected $hidden = [
        'pin', // Nunca exponer el PIN hasheado en responses
    ];

    protected function casts(): array
    {
        return [
            'latitude'               => 'float',
            'longitude'              => 'float',
            'geofence_radius_meters' => 'integer',
            'active'                 => 'boolean',
        ];
    }

    // ─── Relaciones ───────────────────────────────────────────────

    /** Empresa a la que pertenece este proveedor */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /** Vehículos suministrados por este proveedor */
    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    // workshopOrders(), invoices() → se añadirán en bloques posteriores

    // ─── Helpers ──────────────────────────────────────────────────

    public function isChapa(): bool              { return $this->type === 'chapa'; }
    public function isDab(): bool                { return $this->type === 'dab'; }
    public function isLimpieza(): bool           { return $this->type === 'limpieza'; }
    public function isProveedorVehiculos(): bool { return $this->type === 'proveedor_vehiculos'; }

    /**
     * Comprueba si unas coordenadas están dentro del geofence del taller.
     * Regla de negocio: distancia máxima 750m (configurable por proveedor).
     */
    public function isWithinGeofence(float $lat, float $lng): bool
    {
        if (is_null($this->latitude) || is_null($this->longitude)) {
            return true; // Sin coordenadas registradas → no bloquear
        }

        $earthRadius = 6371000; // metros
        $dLat = deg2rad($lat - $this->latitude);
        $dLng = deg2rad($lng - $this->longitude);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($this->latitude)) * cos(deg2rad($lat)) * sin($dLng / 2) ** 2;

        $distance = $earthRadius * 2 * asin(sqrt($a));

        return $distance <= $this->geofence_radius_meters;
    }
}
