<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankDeposit extends Model
{
    protected $fillable = [
        'bank_name',
        'amount_bs',
        'amount_usd',
        'reference',
        'deposited_at',
        'registered_by',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount_bs' => 'decimal:2',
            'amount_usd' => 'decimal:2',
            'deposited_at' => 'date',
        ];
    }

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
