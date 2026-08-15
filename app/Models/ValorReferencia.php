<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ValorReferencia extends Model
{
    protected $table = 'valores_referencia';
    protected $fillable = [
        'examen_id', 'sexo', 'edad_min', 'edad_max',
        'valor_min', 'valor_max', 'valor_texto', 'etiqueta',
    ];

    protected $casts = [
        'valor_min' => 'decimal:4',
        'valor_max' => 'decimal:4',
        'edad_min'  => 'integer',
        'edad_max'  => 'integer',
    ];

    public function examen(): BelongsTo
    {
        return $this->belongsTo(Examen::class);
    }
}
