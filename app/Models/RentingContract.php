<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class RentingContract extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'vehicle_id',
        'client_id',
        'created_by_user_id',
        'monthly_fee',
        'concepts',
        'start_date',
        'end_date',
        'status',
        'billing_day',
        'last_billed_at',
        'alert_sent_30_days',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'monthly_fee'         => 'decimal:2',
            'concepts'            => 'array',
            'start_date'          => 'date',
            'end_date'            => 'date',
            'last_billed_at'      => 'date',
            'billing_day'         => 'integer',
            'alert_sent_30_days'  => 'boolean',
        ];
    }

    // ─── Relaciones ───────────────────────────────────────────────

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    /** Facturas mensuales generadas por el Cron Job — Bloque 7 */
    public function recurringInvoices(): HasMany
    {
        return $this->hasMany(RecurringInvoice::class)->orderByDesc('created_at');
    }

    // ─── Scopes ───────────────────────────────────────────────────

    /** Contratos activos — usa índice rc_vehicle_status_idx */
    public function scopeActive($query)
    {
        return $query->where('status', 'activo');
    }

    /** Contratos próximos a vencer (< 30 días) — usa índice end_date */
    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->where('status', 'activo')
                     ->where('end_date', '<=', now()->addDays($days))
                     ->where('end_date', '>=', now());
    }

    /** Contratos que deben facturar hoy — usa índice last_billed_at */
    public function scopeDueForBilling($query)
    {
        return $query->where('status', 'activo')
                     ->where('billing_day', now()->day)
                     ->where(function ($q) {
                         $q->whereNull('last_billed_at')
                           ->orWhereRaw("last_billed_at < date('now', 'start of month')");
                     });
    }

    // ─── Helpers de Estado ────────────────────────────────────────

    public function isActivo(): bool   { return $this->status === 'activo'; }
    public function isVencido(): bool  { return $this->status === 'vencido'; }
    public function isRenovado(): bool { return $this->status === 'renovado'; }

    // ─── Reglas de Negocio ────────────────────────────────────────

    /** Días restantes hasta el fin del contrato */
    public function getDaysUntilExpiryAttribute(): int
    {
        return (int) now()->diffInDays($this->end_date, false);
    }

    /** ¿Está a menos de 30 días de vencer? → trigger alerta Dashboard */
    public function isExpiringSoon(): bool
    {
        return $this->isActivo()
            && $this->days_until_expiry >= 0
            && $this->days_until_expiry <= 30;
    }

    /** ¿Toca facturar hoy? */
    public function isDueForBillingToday(): bool
    {
        if (! $this->isActivo()) return false;
        if (now()->day !== $this->billing_day) return false;
        if (! $this->last_billed_at) return true;

        return $this->last_billed_at->lt(now()->startOfMonth());
    }
}
