<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CostEntry extends Model
{
    protected $fillable = [
        'vehicle_id',
        'invoice_vehicle_id',
        'type',
        'amount',
        'concept',
        'created_by_user_id',
        'entry_date',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount'     => 'decimal:2',
            'entry_date' => 'date',
        ];
    }

    // ─── Relaciones ───────────────────────────────────────────────

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /** Línea de factura que originó este coste (si vino de OCR) */
    public function invoiceVehicle(): BelongsTo
    {
        return $this->belongsTo(InvoiceVehicle::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    // ─── Helpers ──────────────────────────────────────────────────

    public function isPvi(): bool        { return $this->type === 'pvi'; }
    public function isFeeGestion(): bool { return $this->type === 'fee_gestion'; }
}
