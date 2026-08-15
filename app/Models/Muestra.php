<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Muestra extends Model
{
    protected $fillable = [
        'orden_trabajo_id',
        'tipo',
        'tubo',
        'estado',
        'tomada_por',
        'tomada_at',
        'motivo_rechazo',
        'observaciones',
    ];

    protected $casts = [
        'tomada_at' => 'datetime',
    ];

    protected $appends = ['estado_label'];

    public function orden(): BelongsTo
    {
        return $this->belongsTo(OrdenTrabajo::class, 'orden_trabajo_id');
    }

    public function tomadaPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tomada_por');
    }

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(
            OrdenExamen::class,
            'muestra_orden_examen',
            'muestra_id',
            'orden_examen_id'
        );
    }

    public function getEstadoLabelAttribute(): string
    {
        return [
            'pendiente'  => 'Pendiente',
            'tomada'     => 'Tomada',
            'en_proceso' => 'En proceso',
            'procesada'  => 'Procesada',
            'rechazada'  => 'Rechazada',
        ][$this->estado] ?? $this->estado;
    }

    /**
     * Registra la toma de muestra y actualiza los ítems asociados.
     */
    public function registrarToma(int $userId): void
    {
        $this->update([
            'estado'     => 'tomada',
            'tomada_por' => $userId,
            'tomada_at'  => now(),
        ]);

        // Avanza los ítems de esta muestra a muestra_tomada
        $this->items()->update(['estado' => 'muestra_tomada']);

        // Sincroniza el estado global de la orden
        $this->orden->sincronizarEstado();
    }

    /**
     * Rechaza la muestra y limpia los ítems relacionados.
     */
    public function rechazar(string $motivo): void
    {
        $this->update([
            'estado'           => 'rechazada',
            'motivo_rechazo'   => $motivo,
        ]);

        // Vuelve los ítems a pendiente para que se retome
        $this->items()->update(['estado' => 'pendiente']);

        $this->orden->sincronizarEstado();
    }
}