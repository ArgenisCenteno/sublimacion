<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pago extends Model
{
    protected $fillable = [
        'orden_trabajo_id',
        'user_id',
        'divisa',
        'monto_divisa',
        'tasa_cambio',
        'monto_usd',
        'estado',
        'referencia',
        'observaciones',
        'anulado_by',
        'anulado_at',
    ];

    protected $casts = [
        'monto_divisa' => 'decimal:4',
        'tasa_cambio'  => 'decimal:4',
        'monto_usd'    => 'decimal:4',
        'anulado_at'   => 'datetime',
    ];

    public function orden(): BelongsTo
    {
        return $this->belongsTo(OrdenTrabajo::class, 'orden_trabajo_id');
    }

    public function cajero(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function anuladoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'anulado_by');
    }

    public function metodos(): HasMany
    {
        return $this->hasMany(PagoMetodo::class);
    }

    public function getDivisaLabelAttribute(): string
    {
        return ['USD' => 'USD ($)', 'EUR' => 'EUR (€)', 'BS' => 'Bs.'][$this->divisa] ?? $this->divisa;
    }

    public function getEsActivoAttribute(): bool
    {
        return $this->estado === 'activo';
    }
}