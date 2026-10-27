<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashShift extends Model
{
    protected $fillable = [
        'user_id',
        'opened_at',
        'closed_at',
        'opening_cash_usd',
        'opening_cash_bs',
        'counted_cash_usd',
        'counted_cash_bs',
        'expected_cash_usd',
        'expected_cash_bs',
        'difference_usd',
        'difference_bs',
        'notes',
        'status',
        'reopened_at',
        'reopened_by',
        'reopen_reason',
    ];

    protected function casts(): array
    {
        return [
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
            'reopened_at' => 'datetime',
            'opening_cash_usd' => 'decimal:2',
            'opening_cash_bs' => 'decimal:2',
            'counted_cash_usd' => 'decimal:2',
            'counted_cash_bs' => 'decimal:2',
            'expected_cash_usd' => 'decimal:2',
            'expected_cash_bs' => 'decimal:2',
            'difference_usd' => 'decimal:2',
            'difference_bs' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reopenedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reopened_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Fondo inicial + pagos en efectivo de este turno, separado por moneda.
     */
    public function expectedCash(): array
    {
        $cashPayments = $this->payments()
            ->where('method', 'efectivo')
            ->where('status', 'verificado')
            ->get();

        $usd = (float) $this->opening_cash_usd + (float) $cashPayments->where('currency', 'USD')->sum('amount_usd');
        $bs = (float) $this->opening_cash_bs + (float) $cashPayments->where('currency', 'BS')->sum('amount_bs');

        return ['usd' => $usd, 'bs' => $bs];
    }

    public function totalsByMethod(): array
    {
        return $this->payments()
            ->where('status', 'verificado')
            ->selectRaw('method, sum(amount_usd) as total_usd, count(*) as count')
            ->groupBy('method')
            ->get()
            ->keyBy('method')
            ->toArray();
    }

    public function close(float $countedUsd, float $countedBs, ?string $notes = null): void
    {
        $expected = $this->expectedCash();

        $this->update([
            'counted_cash_usd' => $countedUsd,
            'counted_cash_bs' => $countedBs,
            'expected_cash_usd' => $expected['usd'],
            'expected_cash_bs' => $expected['bs'],
            'difference_usd' => $countedUsd - $expected['usd'],
            'difference_bs' => $countedBs - $expected['bs'],
            'notes' => $notes,
            'status' => 'cerrado',
            'closed_at' => now(),
        ]);
    }
}
