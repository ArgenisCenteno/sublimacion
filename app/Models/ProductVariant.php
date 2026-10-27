<?php

namespace App\Models;

use App\Notifications\LowStockAlert;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Notification;

class ProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'name',
        'price_override',
        'stock',
        'sku',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'price_override' => 'decimal:2',
            'active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::updated(function (ProductVariant $variant) {
            if (! $variant->wasChanged('stock') || $variant->stock === null) {
                return;
            }

            $original = (int) $variant->getOriginal('stock');

            if ($original > Product::LOW_STOCK_THRESHOLD && $variant->stock <= Product::LOW_STOCK_THRESHOLD) {
                $name = "{$variant->product->name} - {$variant->name}";
                Notification::send(User::role(['Admin', 'Empleado'])->get(), new LowStockAlert($name, (int) $variant->stock));
            }
        });
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
