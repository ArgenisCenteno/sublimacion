<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Paciente extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tipo_documento', 'cedula', 'nombres', 'apellidos',
        'sexo', 'fecha_nacimiento',
        'telefono', 'email', 'direccion',
        'rep_nombres', 'rep_apellidos', 'rep_cedula',
        'rep_telefono', 'rep_parentesco',
        'medico_referente',
        'es_menor',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
    ];
    protected $appends = ['edad', 'es_menor', 'nombre_completo'];

    // Accessor — no guardar la edad, calcularla siempre
    public function getEdadAttribute(): int
    {
        return $this->fecha_nacimiento->age;
    }

    public function getEsMenorAttribute(): bool
    {
        return $this->edad < 18;
    }

    public function getNombreCompletoAttribute(): string
    {
        return "{$this->nombres} {$this->apellidos}";
    }

    public function ordenes()
    {
        return $this->hasMany(OrdenTrabajo::class);
    }
}
