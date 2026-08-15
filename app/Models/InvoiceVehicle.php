<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InvoiceVehicle extends Model
{
    protected $fillable = [
        'invoice_id',
        'vehicle_id',
        'assigned_amount',
        'assigned_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'assigned_amount' => 'decimal:2',
        ];
    }

    // ─── Relaciones ───────────────────────────────────────────────

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function cost_entries(): HasMany
    {
        return $this->hasMany(CostEntry::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────

    public function isOcr(): bool
    {
        return $this->assigned_by === 'ocr';
    }
    public function isManual(): bool
    {
        return $this->assigned_by === 'manual';
    }
}
