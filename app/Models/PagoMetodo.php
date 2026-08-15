<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PagoMetodo extends Model
{
    protected $fillable = [
        'pago_id',
        'metodo',
        'monto_divisa',
        'monto_usd',
        'referencia',
    ];

    protected $casts = [
        'monto_divisa' => 'decimal:4',
        'monto_usd'    => 'decimal:4',
    ];

    public function pago(): BelongsTo
    {
        return $this->belongsTo(Pago::class);
    }

    public function getMetodoLabelAttribute(): string
    {
        return [
            'efectivo'       => 'Efectivo',
            'transferencia'  => 'Transferencia',
            'punto_de_venta' => 'Punto de venta',
            'zelle'          => 'Zelle',
            'pago_movil'     => 'Pago móvil',
            'otro'           => 'Otro',
        ][$this->metodo] ?? $this->metodo;
    }
}