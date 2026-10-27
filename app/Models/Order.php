<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'canal',
        'status',
        'delivery_type',
        'shipping_carrier',
        'shipping_address',
        'shipping_city',
        'customer_name',
        'customer_phone',
        'customer_email',
        'customer_cedula_rif',
        'payment_method',
        'subtotal_usd',
        'shipping_cost_usd',
        'total_usd',
        'notes',
        'tracking_number',
    ];

    protected function casts(): array
    {
        return [
            'subtotal_usd' => 'decimal:2',
            'shipping_cost_usd' => 'decimal:2',
            'total_usd' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistory(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class)->orderBy('created_at');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function amountPaid(): float
    {
        return (float) $this->payments()->where('status', 'verificado')->sum('amount_usd');
    }

    public function balanceDue(): float
    {
        return max(0, (float) $this->total_usd - $this->amountPaid());
    }

    /**
     * Recalcula el estado de pago del pedido segun los pagos verificados.
     * No pisa estados manuales de logistica (en_preparacion/enviado/entregado/cancelado).
     */
    public function recalculatePaymentStatus(?int $changedBy = null): void
    {
        if (! in_array($this->status, ['pendiente_pago', 'pago_parcial', 'pago_confirmado'], true)) {
            return;
        }

        $paid = $this->amountPaid();

        $newStatus = match (true) {
            $paid >= (float) $this->total_usd => 'pago_confirmado',
            $paid > 0 => 'pago_parcial',
            default => 'pendiente_pago',
        };

        if ($newStatus !== $this->status) {
            $this->update(['status' => $newStatus]);

            $this->statusHistory()->create([
                'status' => $newStatus,
                'changed_by' => $changedBy,
                'note' => 'Recalculado segun pagos verificados.',
            ]);
        }
    }
}
