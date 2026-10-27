<?php

namespace App\Models;

use App\Notifications\ReceiptIssued;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Receipt extends Model
{
    protected $appends = ['formatted_number'];

    protected $fillable = [
        'number',
        'order_id',
        'payment_id',
        'customer_name',
        'customer_email',
        'customer_cedula_rif',
        'amount_usd',
        'amount_bs',
        'exchange_rate',
        'payment_method',
        'reference',
        'issued_at',
        'voided_at',
        'voided_by',
        'void_reason',
    ];

    protected function casts(): array
    {
        return [
            'amount_usd' => 'decimal:2',
            'amount_bs' => 'decimal:2',
            'exchange_rate' => 'decimal:4',
            'issued_at' => 'datetime',
            'voided_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function voidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    public function getFormattedNumberAttribute(): string
    {
        return sprintf('REC-%06d', $this->number);
    }

    public static function issueFor(Payment $payment): self
    {
        return DB::transaction(function () use ($payment) {
            $settings = StoreSetting::query()->lockForUpdate()->first() ?? StoreSetting::current();

            $number = $settings->next_receipt_number;
            $settings->increment('next_receipt_number');

            $order = $payment->order;

            $receipt = static::create([
                'number' => $number,
                'order_id' => $order->id,
                'payment_id' => $payment->id,
                'customer_name' => $order->customer_name,
                'customer_email' => $order->customer_email,
                'customer_cedula_rif' => $order->customer_cedula_rif,
                'amount_usd' => $payment->amount_usd,
                'amount_bs' => $payment->amount_bs,
                'exchange_rate' => $payment->exchange_rate,
                'payment_method' => $payment->method,
                'reference' => $payment->reference,
                'issued_at' => now(),
            ]);

            $order->user?->notify(new ReceiptIssued($receipt));

            return $receipt;
        });
    }
}
