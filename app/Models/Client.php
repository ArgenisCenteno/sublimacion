<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'type',
        'name',
        'nif',
        'email',
        'phone',
        'phone_secondary',
        'address',
        'city',
        'postal_code',
        'province',
        'country',
        'company_name',
        'contact_person',
        'notes',
    ];

    // ─── Relaciones ───────────────────────────────────────────────

    public function saleContracts(): HasMany
    {
        return $this->hasMany(SaleContract::class);
    }

    public function rentingContracts(): HasMany
    {
        return $this->hasMany(RentingContract::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────

    public function isFisica(): bool   { return $this->type === 'fisica'; }
    public function isJuridica(): bool { return $this->type === 'juridica'; }

    /** Nombre para mostrar según tipo de cliente */
    public function getDisplayNameAttribute(): string
    {
        return $this->isJuridica() && $this->company_name
            ? $this->company_name
            : $this->name;
    }
}
