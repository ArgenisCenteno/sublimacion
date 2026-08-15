<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TasaCambio extends Model
{
    protected $table = 'tasas_cambio';
    protected $fillable = ['divisa', 'tasa', 'updated_by'];

    protected $casts = ['tasa' => 'decimal:4'];

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function vigente(string $divisa): ?float
    {
        if ($divisa === 'USD') return 1.0;
        return static::where('divisa', $divisa)->value('tasa');
    }
}