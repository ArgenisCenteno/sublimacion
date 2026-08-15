<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkshopChecklistTemplate extends Model
{
    protected $fillable = [
        'type',
        'label',
        'sort_order',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'active' => 'boolean',
        ];
    }
}
