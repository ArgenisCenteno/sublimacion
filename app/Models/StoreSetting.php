<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class StoreSetting extends Model
{
    use LogsActivity;

    protected $fillable = [
        'exchange_rate_bs',
        'next_receipt_number',
        'store_name',
        'store_slogan',
        'logo_path',
        'color_primary',
        'color_secondary',
        'instagram_url',
        'facebook_url',
        'tiktok_url',
        'whatsapp_number',
        'contact_email',
        'contact_phone',
        'address',
        'business_hours',
        'pago_movil_bank',
        'pago_movil_phone',
        'pago_movil_cedula_rif',
        'pago_movil_holder_name',
        'transferencia_bank',
        'transferencia_account_number',
        'transferencia_cedula_rif',
        'transferencia_holder_name',
    ];

    protected function casts(): array
    {
        return [
            'exchange_rate_bs' => 'decimal:4',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'exchange_rate_bs',
                'store_name',
                'store_slogan',
                'logo_path',
                'color_primary',
                'color_secondary',
                'instagram_url',
                'facebook_url',
                'tiktok_url',
                'whatsapp_number',
                'contact_email',
                'contact_phone',
                'address',
                'business_hours',
                'pago_movil_bank',
                'pago_movil_phone',
                'pago_movil_cedula_rif',
                'pago_movil_holder_name',
                'transferencia_bank',
                'transferencia_account_number',
                'transferencia_cedula_rif',
                'transferencia_holder_name',
            ])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('configuración de tienda');
    }

    public static function current(): self
    {
        return static::firstOrCreate([], ['exchange_rate_bs' => 1]);
    }
}
