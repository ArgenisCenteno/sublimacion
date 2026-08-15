<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SaleContract extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'vehicle_id',
        'client_id',
        'user_id',
        'pvp',
        'payment_method',
        'status',
        'signed_at',
        'reopened_at',
        'reopened_by_user_id',
        'reopen_reason',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'pvp'         => 'decimal:2',
            'signed_at'   => 'datetime',
            'reopened_at' => 'datetime',
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

    /** Comercial que gestionó la venta */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Quién reabrió el expediente (solo Gerencia) */
    public function reopenedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reopened_by_user_id');
    }

    // ─── Scopes ───────────────────────────────────────────────────

    /** Contrato activo del vehículo — usa índice sc_vehicle_status_idx */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['reservado', 'vendido']);
    }

    public function scopeVendido($query)
    {
        return $query->where('status', 'vendido');
    }

    // ─── Helpers de Estado ────────────────────────────────────────

    public function isReservado(): bool { return $this->status === 'reservado'; }
    public function isVendido(): bool   { return $this->status === 'vendido'; }
    public function isReabierto(): bool { return $this->status === 'reabierto'; }
    public function isCancelado(): bool { return $this->status === 'cancelado'; }

    // ─── Reglas de Negocio ────────────────────────────────────────

    /**
     * Reabre el expediente — solo Gerencia.
     * Crea un nuevo SaleContract en el siguiente ciclo de venta.
     * Decisión B confirmada: este contrato queda como historial.
     */
    public function reopen(User $user, string $reason): void
    {
        $this->update([
            'status'              => 'reabierto',
            'reopened_at'         => now(),
            'reopened_by_user_id' => $user->id,
            'reopen_reason'       => $reason,
        ]);

        // Devuelve el vehículo a disponible
        $this->vehicle->update([
            'commercial_status' => 'disponible',
        ]);
    }
}
