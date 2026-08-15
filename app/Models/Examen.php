<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Examen extends Model
{
    use SoftDeletes;
    protected $table = 'examenes';

    protected $fillable = [
        'codigo',
        'nombre',
        'nombre_corto',
        'categoria',
        'muestra',
        'tubo',
        'tiempo_resultado',
        'unidad',
        'metodo',
        'precio',
        'activo',
        'instrucciones',
        'subcategoria',
        'orden_col',
        'orden_fila',
    ];

    protected $casts = [
        'precio'           => 'decimal:2',
        'activo'           => 'boolean',
        'tiempo_resultado' => 'integer',
    ];

    public function valoresReferencia(): HasMany
    {
        return $this->hasMany(ValorReferencia::class);
    }

    public function perfiles(): BelongsToMany
    {
        return $this->belongsToMany(Perfil::class, 'examen_perfil');
    }

    public function referenciaParaPaciente(int $edad, string $sexo): ?ValorReferencia
    {
        return $this->valoresReferencia
            ->filter(fn (ValorReferencia $v) =>
                $edad >= $v->edad_min &&
                $edad <= $v->edad_max &&
                ($v->sexo === 'ambos' || $v->sexo === $sexo)
            )
            ->sortBy('edad_min')
            ->first();
    }
}