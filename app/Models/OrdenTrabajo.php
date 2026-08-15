<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrdenTrabajo extends Model
{
    use SoftDeletes;

    protected $table = 'orden_trabajos';

    protected $fillable = [
        'codigo',
        'paciente_id',
        'medico_id',
        'user_id',
        'estado',
        'estado_pago',
        'subtotal',
        'descuento',
        'total',
        'observaciones',
        'fecha_entrega_estimada',
        'fecha_entregado',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'descuento' => 'decimal:2',
        'total' => 'decimal:2',
        'fecha_entrega_estimada' => 'datetime',
        'fecha_entregado' => 'datetime',
    ];

    protected $appends = ['estado_label', 'estado_pago_label'];

    // ─── Relaciones ───────────────────────────────────────────

    public function paciente(): BelongsTo
    {
        return $this->belongsTo(Paciente::class);
    }

    public function medico(): BelongsTo
    {
        return $this->belongsTo(Medico::class);
    }

    public function recepcionista(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrdenExamen::class);
    }

    // ─── Accessors ────────────────────────────────────────────

    public function getEstadoLabelAttribute(): string
    {

        return [
            'pendiente' => 'Pendiente',
            'en_proceso' => 'En proceso',
            'listo' => 'Listo',
            'entregado' => 'Entregado',
            'anulado' => 'Anulado',
        ][$this->estado] ?? ($this->estado ?? 'Sin estado');
    }

    public function getEstadoPagoLabelAttribute(): string
    {

        return [
            'pendiente' => 'Pendiente',
            'pagado' => 'Pagado',
            'exonerado' => 'Exonerado',
        ][$this->estado_pago] ?? 'Sin Estado';
    }

    // ─── Helpers ──────────────────────────────────────────────

    /**
     * Genera el próximo código correlativo del año actual.
     * Formato: LAB-2025-00001
     */
    public static function generarCodigo(): string
    {
        $anio = now()->year;
        $ultimo = static::whereYear('created_at', $anio)->count() + 1;

        return 'LAB-' . $anio . '-' . str_pad($ultimo, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Recalcula subtotal, aplica descuento y actualiza total.
     */
    public function recalcularTotales(): void
    {
        $subtotal = $this->items()->sum('precio');

        $this->update([
            'subtotal' => $subtotal,
            'total' => max(0, $subtotal - $this->descuento),
        ]);
    }

    /**
     * Actualiza el estado global según los estados de los ítems.
     * Llamar cada vez que un ítem cambia de estado.
     */
    public function sincronizarEstado(): void
    {
        $items = $this->items;

        if ($items->isEmpty())
            return;

        $todoListo = $items->every(fn($i) => $i->estado === 'listo');
        $algunoEnProc = $items->contains(fn($i) => in_array($i->estado, ['en_proceso', 'muestra_tomada']));

        $nuevoEstado = match (true) {
            $todoListo => 'listo',
            $algunoEnProc => 'en_proceso',
            default => 'pendiente',
        };

        if ($this->estado !== $nuevoEstado) {
            $this->update(['estado' => $nuevoEstado]);
        }
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(\App\Models\Pago::class, 'orden_trabajo_id');
    }

    // 2. Monto total pagado en USD (suma de todos los pagos)
    public function getMontoPagadoUsdAttribute(): float
    {
        return (float) $this->pagos()->where('estado', 'activo')->sum('monto_usd');
    }

    // 3. Saldo pendiente en USD
    public function getSaldoPendienteUsdAttribute(): float
    {
        return max(0, (float) $this->total - $this->monto_pagado_usd);
    }

    // 4. Recalcula estado_pago según pagos reales. Llamar tras store/destroy de Pago.
    public function recalcularEstadoPago(): void
    {
        $pagado = $this->monto_pagado_usd;

        $estado = match (true) {
            $pagado <= 0 => 'pendiente',
            $pagado < (float) $this->total => 'parcial',
            default => 'pagado',
        };

        $this->update(['estado_pago' => $estado]);
    }

    public function examenes(): HasMany
    {
        return $this->hasMany(OrdenExamen::class);
    }

    // En OrdenTrabajo.php
    public function muestras(): HasMany
    {
        return $this->hasMany(Muestra::class, 'orden_trabajo_id');
    }

}