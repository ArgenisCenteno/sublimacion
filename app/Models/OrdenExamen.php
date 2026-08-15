<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrdenExamen extends Model
{
    protected $table = 'orden_examen';

    protected $fillable = [
        'orden_trabajo_id',
        'examen_id',
        'precio',
        'estado',
        'resultado_valor',
        'resultado_texto',
        'resultado_unidad',
        'fuera_de_rango',
        'indicador',
        'procesado_por',
        'procesado_at',
    ];

    protected $casts = [
        'precio'          => 'decimal:2',
        'resultado_valor' => 'decimal:4',
        'fuera_de_rango'  => 'boolean',
        'procesado_at'    => 'datetime',
    ];

    public function orden(): BelongsTo
    {
        return $this->belongsTo(OrdenTrabajo::class, 'orden_trabajo_id');
    }

    public function examen(): BelongsTo
    {
        return $this->belongsTo(Examen::class);
    }

    public function procesadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'procesado_por');
    }

    /**
     * Evalúa si el resultado está fuera del rango de referencia
     * para el paciente de la orden y actualiza los flags.
     */
    public function evaluarRango(): void
    {
        $paciente = $this->orden->paciente;
        $referencia = $this->examen->referenciaParaPaciente(
            $paciente->edad,
            $paciente->sexo
        );

        if (!$referencia || $this->resultado_valor === null) {
            $this->update(['fuera_de_rango' => false, 'indicador' => null]);
            return;
        }

        $valor = (float) $this->resultado_valor;
        $min   = $referencia->valor_min !== null ? (float) $referencia->valor_min : null;
        $max   = $referencia->valor_max !== null ? (float) $referencia->valor_max : null;

        $indicador    = 'normal';
        $fueraDeRango = false;

        if ($min !== null && $valor < $min) {
            $indicador    = $valor < ($min * 0.8) ? 'critico' : 'bajo';
            $fueraDeRango = true;
        } elseif ($max !== null && $valor > $max) {
            $indicador    = $valor > ($max * 1.2) ? 'critico' : 'alto';
            $fueraDeRango = true;
        }

        $this->update([
            'fuera_de_rango' => $fueraDeRango,
            'indicador'      => $indicador,
        ]);
    }
}