<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alert extends Model
{
    protected $fillable = [
        'vehicle_id',
        'type',
        'priority',
        'status',
        'payload',
        'resolved_by_user_id',
        'resolved_at',
        'resolution_notes',
    ];

    protected function casts(): array
    {
        return [
            'payload'     => 'array',
            'resolved_at' => 'datetime',
        ];
    }

    // ─── Prioridad por tipo — tabla de verdad del negocio ─────────
    const PRIORITY_MAP = [
        'insurance_active'   => 'alta',
        'key_missing'        => 'alta',
        'documentation_incomplete' => 'media',
        'geofence_breach'    => 'alta',
        'bottleneck'         => 'media',
        'renting_expiry'     => 'media',
        'sale_reopened'      => 'media',
        'motorflash_blocked' => 'media',
        'taller_exit'        => 'baja',
    ];

    // ─── Relaciones ───────────────────────────────────────────────

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by_user_id');
    }

    // ─── Scopes — queries del Dashboard ──────────────────────────

    /** Todas las alertas activas ordenadas por prioridad (usa al_status_priority_idx) */
    public function scopePending($query)
    {
        return $query->where('status', 'pendiente')
                     ->orderByRaw("CASE priority WHEN 'alta' THEN 1 WHEN 'media' THEN 2 ELSE 3 END");
    }

    public function scopeAlta($query)
    {
        return $query->where('priority', 'alta')->where('status', 'pendiente');
    }

    public function scopeForVehicle($query, int $vehicleId)
    {
        return $query->where('vehicle_id', $vehicleId)->where('status', 'pendiente');
    }

    // ─── Helpers de Estado ────────────────────────────────────────

    public function isPendiente(): bool  { return $this->status === 'pendiente'; }
    public function isResuelta(): bool   { return $this->status === 'resuelta'; }
    public function isDescartada(): bool { return $this->status === 'descartada'; }
    public function isAlta(): bool       { return $this->priority === 'alta'; }
    public function isMedia(): bool      { return $this->priority === 'media'; }

    // ─── Reglas de Negocio ────────────────────────────────────────

    /**
     * Resuelve la alerta con trazabilidad.
     * El botón de acción del Dashboard llama a este método.
     */
    public function resolve(User $user, string $notes = ''): void
    {
        $this->update([
            'status'              => 'resuelta',
            'resolved_by_user_id' => $user->id,
            'resolved_at'         => now(),
            'resolution_notes'    => $notes,
        ]);
    }

    /**
     * Fábrica estática — crea una alerta evitando duplicados.
     * El motor de alertas siempre usa este método en lugar de create() directamente.
     * Usa el índice al_dedup_idx para la comprobación.
     */
    public static function fire(
        string $type,
        ?int $vehicleId = null,
        array $payload = []
    ): static {
        return static::firstOrCreate(
            [
                'vehicle_id' => $vehicleId,
                'type'       => $type,
                'status'     => 'pendiente',
            ],
            [
                'priority' => static::PRIORITY_MAP[$type] ?? 'media',
                'payload'  => $payload,
            ]
        );
    }
}
