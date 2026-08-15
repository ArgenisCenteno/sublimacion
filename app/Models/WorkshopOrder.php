<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorkshopOrder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'vehicle_id',
        'provider_id',
        'type',
        'status',
        'entry_at',
        'exit_at',
        'exit_photo_url',
        'exit_photo_disk',      // FIX #4: faltaba en $fillable
        'created_by_user_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'entry_at' => 'datetime',
            'exit_at'  => 'datetime',
        ];
    }

    // ─── Relaciones ───────────────────────────────────────────────

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    /** nullable: provider_id puede ser NULL si el proveedor fue eliminado */
    public function provider(): BelongsTo
    {
        return $this->belongsTo(Provider::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function checklistItems(): HasMany
    {
        return $this->hasMany(WorkshopChecklistItem::class)->orderBy('sort_order');
    }

    /** Ítems del checklist con incidencia detectada */
    public function incidents(): HasMany
    {
        return $this->hasMany(WorkshopChecklistItem::class)->where('has_incident', true);
    }

    // ─── Scopes para el Dashboard ─────────────────────────────────

    /** Órdenes activas (usa índice wo_vehicle_status_idx) */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['pendiente', 'en_proceso', 'incidencia']);
    }

    /** Órdenes con cuello de botella — más de 15 días en taller (usa índice entry_at) */
    public function scopeBottleneck($query, int $days = 15)
    {
        return $query->where('entry_at', '<=', now()->subDays($days))
                     ->where('status', 'en_proceso');
    }

    /** Órdenes agrupadas por tipo (usa índice wo_status_type_idx) */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    // ─── Helpers de Estado ────────────────────────────────────────

    public function isPendiente(): bool   { return $this->status === 'pendiente'; }
    public function isEnProceso(): bool   { return $this->status === 'en_proceso'; }
    public function isIncidencia(): bool  { return $this->status === 'incidencia'; }
    public function isFinalizado(): bool  { return $this->status === 'finalizado'; }

    public function isChapa(): bool       { return $this->type === 'chapa'; }
    public function isDab(): bool         { return $this->type === 'dab'; }
    public function isLimpieza(): bool    { return $this->type === 'limpieza'; }

    // ─── Reglas de Negocio ────────────────────────────────────────

    /** Días en taller — si > 15 dispara alerta naranja */
    public function getDaysInWorkshopAttribute(): int
    {
        if (! $this->entry_at) return 0;
        $end = $this->exit_at ?? now();
        return (int) $this->entry_at->diffInDays($end);
    }

    public function isBottleneck(): bool
    {
        return $this->isEnProceso() && $this->days_in_workshop > 15;
    }

    /**
     * Chapa/Limpieza: necesita foto de salida.
     * DAB: todos los ítems revisados y sin incidencias abiertas.
     */
    public function canBeFinished(): bool
    {
        if ($this->isDab()) {
            return $this->checklistItems()->whereNull('passed')->doesntExist();
        }

        return ! empty($this->exit_photo_url);
    }
}
