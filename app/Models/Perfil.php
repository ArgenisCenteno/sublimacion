<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Perfil extends Model
{
    use SoftDeletes;

    protected $table = 'perfiles';
    protected $fillable = [
        'codigo',
        'nombre',
        'categoria',
        'precio',
        'activo',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
        'activo' => 'boolean',
    ];

    public function examenes(): BelongsToMany
    {
        return $this->belongsToMany(Examen::class, 'examen_perfil');
    }

    /**
     * Precio calculado sumando los exámenes individuales.
     * Útil para mostrar cuánto se ahorra el paciente
     * vs comprarlos por separado.
     */
    public function getPrecioIndividualAttribute(): float
    {
        return (float) $this->examenes->sum('precio');
    }

    public function getAhorroAttribute(): float
    {
        return max(0, $this->precio_individual - (float) $this->precio);
    }
}