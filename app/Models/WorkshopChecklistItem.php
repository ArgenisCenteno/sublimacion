<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkshopChecklistItem extends Model
{
    protected $fillable = [
        'workshop_order_id',
        'label',
        'sort_order',
        'passed',
        'has_incident',
        'notes',
        'photo_url',
        'photo_disk',
        'checked_at',
    ];

    protected function casts(): array
    {
        return [
            'passed'       => 'boolean',
            'has_incident' => 'boolean',
            'sort_order'   => 'integer',
            'checked_at'   => 'datetime',
        ];
    }

    // ─── Relaciones ───────────────────────────────────────────────

    public function workshopOrder(): BelongsTo
    {
        return $this->belongsTo(WorkshopOrder::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────

    /** ¿Aún sin revisar? */
    public function isPending(): bool  { return is_null($this->passed); }

    /** ¿Revisado y correcto? */
    public function isOk(): bool       { return $this->passed === true && ! $this->has_incident; }

    /** ¿Revisado con fallo? → obliga a abrir incidencia */
    public function hasFailed(): bool  { return $this->passed === false || $this->has_incident; }
}
