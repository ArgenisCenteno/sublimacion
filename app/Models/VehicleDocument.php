<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleDocument extends Model
{
    public const BLOCKING_TYPES = [
        'ficha_tecnica',
        'permiso_circulacion',
    ];

    public const SLOT_TYPES = [
        'ficha_tecnica',
        'permiso_circulacion',
        'factura_compra',
        'informe_itv',
        'mandato_gestoria',
        'seguro',
        'otro',
    ];

    protected $fillable = [
        'vehicle_id',
        'type',
        'url',
        'disk',
        'size_bytes',
        'mime_type',
        'original_filename',
        'status',
        'uploaded_by_user_id',
        'downloaded_at',
        'downloaded_by_user_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'size_bytes' => 'integer',
            'downloaded_at' => 'datetime',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }

    public function downloadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'downloaded_by_user_id');
    }

    public function isPendiente(): bool
    {
        return $this->status === 'pendiente';
    }

    public function isDescargado(): bool
    {
        return $this->status === 'descargado';
    }

    public function isProcesado(): bool
    {
        return $this->status === 'procesado';
    }

    public function isBlocking(): bool
    {
        return in_array($this->type, self::BLOCKING_TYPES, true);
    }

    public function markAsDownloaded(User $user): void
    {
        $this->update([
            'status' => 'descargado',
            'downloaded_at' => now(),
            'downloaded_by_user_id' => $user->id,
        ]);
    }
}
