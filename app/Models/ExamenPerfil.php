<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ExamenPerfil extends Pivot
{
    protected $table = 'examen_perfil';

    protected $fillable = [
        'examen_id',
        'perfil_id',
        'orden_display', // si lo agregas a la migración
    ];

    public function examenes(): BelongsToMany
    {
        return $this->belongsToMany(Examen::class, 'examen_perfil')
            ->using(ExamenPerfil::class)
            ->withPivot('orden_display')
            ->orderByPivot('orden_display');
    }
}