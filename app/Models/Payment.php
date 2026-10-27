<?php

namespace App\Models;

use App\Notifications\PaymentVerified;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Payment extends Model
{
    use LogsActivity;

    protected $fillable = [
        'order_id',
        'bank_deposit_id',
        'cash_shift_id',
        'amount_usd',
        'amount_bs',
        'exchange_rate',
        'currency',
        'method',
        'reference',
        'receipt_path',
        'paypal_order_id',
        'paypal_transaction_id',
        'status',
        'submitted_by',
        'verified_by',
        'verified_at',
        'rejection_reason',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'amount_usd' => 'decimal:2',
            'amount_bs' => 'decimal:2',
            'exchange_rate' => 'decimal:4',
            'verified_at' => 'datetime',
            'paid_at' => 'date',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['status', 'amount_usd', 'method', 'currency'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('pago');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function bankDeposit(): BelongsTo
    {
        return $this->belongsTo(BankDeposit::class);
    }

    public function cashShift(): BelongsTo
    {
        return $this->belongsTo(CashShift::class);
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function receipt(): HasOne
    {
        return $this->hasOne(Receipt::class);
    }

    /**
     * Calcula amount_usd/amount_bs/exchange_rate a partir de un monto entrado en una moneda dada.
     * PayPal no aplica conversion (cobra directo en USD).
     */
    public static function computeAmounts(string $method, ?string $currency, float $amount): array
    {
        if ($method === 'paypal') {
            return ['amount_usd' => $amount, 'amount_bs' => null, 'exchange_rate' => null];
        }

        $rate = (float) StoreSetting::current()->exchange_rate_bs;

        if ($currency === 'BS') {
            $amountBs = $amount;
            $amountUsd = $rate > 0 ? round($amountBs / $rate, 2) : 0;
        } else {
            $amountUsd = $amount;
            $amountBs = $rate > 0 ? round($amountUsd * $rate, 2) : null;
        }

        return ['amount_usd' => $amountUsd, 'amount_bs' => $amountBs, 'exchange_rate' => $rate];
    }

    /**
     * Marca el pago como verificado, recalcula el estado del pedido y emite su recibo.
     * Punto unico usado por la verificacion manual y por los flujos de PayPal (captura/webhook).
     */
    public function markVerified(?int $verifiedBy = null): Receipt
    {
        $this->update([
            'status' => 'verificado',
            'verified_by' => $verifiedBy,
            'verified_at' => now(),
        ]);

        $this->order->recalculatePaymentStatus($verifiedBy);

        $this->order->user?->notify(new PaymentVerified($this));

        return Receipt::issueFor($this->fresh());
    }
}
