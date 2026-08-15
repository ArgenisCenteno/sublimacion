<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'provider_id',
        'uploaded_by_user_id',
        'invoice_number',
        'total_amount',
        'subtotal_amount',     // NUEVO
        'vat_amount',          // NUEVO
        'issued_at',
        'pdf_url',
        'albaran_url',         // NUEVO
        'disk',
        'original_filename',
        'ocr_raw_text',
        'ocr_status',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'subtotal_amount' => 'decimal:2', // NUEVO
            'vat_amount' => 'decimal:2', // NUEVO
            'issued_at' => 'date',
        ];
    }

    // ─── Relaciones ───────────────────────────────────────────────

    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }

    public function invoiceVehicles(): HasMany
    {
        return $this->hasMany(InvoiceVehicle::class);
    }

    public function vehicles(): BelongsToMany
    {
        return $this->belongsToMany(Vehicle::class, 'invoice_vehicles')
            ->withPivot('assigned_amount', 'assigned_by', 'notes')
            ->withTimestamps();
    }

    // ─── Scopes ───────────────────────────────────────────────────

    public function scopePendingOcr($query)
    {
        return $query->where('ocr_status', 'pendiente');
    }

    public function scopePendingAssignment($query)
    {
        return $query->where('status', 'pendiente');
    }

    // ─── Helpers ──────────────────────────────────────────────────

    public function isOcrPendiente(): bool
    {
        return $this->ocr_status === 'pendiente';
    }
    public function isOcrProcesado(): bool
    {
        return $this->ocr_status === 'procesado';
    }
    public function isOcrError(): bool
    {
        return $this->ocr_status === 'error';
    }

    public function getUnassignedAmountAttribute(): float
    {
        $assigned = $this->invoiceVehicles()->sum('assigned_amount');
        return (float) $this->total_amount - (float) $assigned;
    }
    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

   
}