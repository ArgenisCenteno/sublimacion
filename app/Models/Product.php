<?php

namespace App\Models;

use App\Notifications\LowStockAlert;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

class Product extends Model
{
    use LogsActivity;

    public const LOW_STOCK_THRESHOLD = 5;

    protected $fillable = [
        'category_id',
        'type',
        'name',
        'slug',
        'description',
        'price_usd',
        'sku',
        'stock',
        'active',
        'featured',
    ];

    protected function casts(): array
    {
        return [
            'price_usd' => 'decimal:2',
            'active' => 'boolean',
            'featured' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'price_usd', 'stock', 'active', 'category_id'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('producto');
    }

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (empty($product->slug)) {
                $product->slug = static::generateUniqueSlug($product->name);
            }
        });

        static::updated(function (Product $product) {
            if (! $product->wasChanged('stock') || $product->stock === null) {
                return;
            }

            $original = (int) $product->getOriginal('stock');

            if ($original > self::LOW_STOCK_THRESHOLD && $product->stock <= self::LOW_STOCK_THRESHOLD) {
                Notification::send(User::role(['Admin', 'Empleado'])->get(), new LowStockAlert($product->name, (int) $product->stock));
            }
        });
    }

    public static function generateUniqueSlug(string $name): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $i = 2;

        while (static::where('slug', $slug)->exists()) {
            $slug = "{$original}-{$i}";
            $i++;
        }

        return $slug;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }
}
