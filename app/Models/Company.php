<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'tax_id',
        'invoice_series',
        'logo_url',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    // ─── Relaciones ───────────────────────────────────────────────

    /** Usuarios que pertenecen a esta empresa */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /** Proveedores/talleres asignados a esta empresa */
    public function providers(): HasMany
    {
        return $this->hasMany(Provider::class);
    }

    /** Vehículos que pertenecen a esta empresa */
    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }
}
