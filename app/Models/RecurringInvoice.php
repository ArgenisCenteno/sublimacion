<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecurringInvoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'renting_contract_id',
        'vehicle_id',
        'client_id',
        'invoice_number',
        'amount',
        'concepts',
        'period_start',
        'period_end',
        'pdf_url',
        'disk',
        'status',
        'generated_at',
        'downloaded_at',
    ];

    protected function casts(): array
    {
        return [
            'amount'        => 'decimal:2',
            'concepts'      => 'array',
            'period_start'  => 'date',
            'period_end'    => 'date',
            'generated_at'  => 'datetime',
            'downloaded_at' => 'datetime',
        ];
    }

    // ─── Relaciones ───────────────────────────────────────────────

    public function rentingContract(): BelongsTo
    {
        return $this->belongsTo(RentingContract::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────

    public function isGenerada(): bool  { return $this->status === 'generada'; }
    public function isDescargada(): bool { return $this->status === 'descargada'; }
    public function isCobrada(): bool   { return $this->status === 'cobrada'; }
    public function isAnulada(): bool   { return $this->status === 'anulada'; }

    /** Marca la factura como descargada por la gestoría */
    public function markAsDownloaded(): void
    {
        $this->update([
            'status'        => 'descargada',
            'downloaded_at' => now(),
        ]);
    }
}
