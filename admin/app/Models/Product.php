<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    protected $fillable = [
        'name',
        'barcode',
        'sku',
        'price',
        'requires_serial',
        'quantity',
        'low_stock_threshold',
        'sort_order',
        'icon',
        'product_category_id',
        'tyre_size',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public static function iconOptions(): array
    {
        return [
            '' => 'Default (package)',
            'package' => 'Package',
            'tyre' => 'Tyre',
            'wrench' => 'Wrench/Tool',
            'oil' => 'Oil',
            'battery' => 'Battery',
            'cog' => 'Service/Cog',
        ];
    }

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'requires_serial' => 'boolean',
        ];
    }

    public function getStockCountAttribute(): int
    {
        if ($this->requires_serial) {
            return (int) ($this->attributes['available_serials_count'] ?? $this->availableSerials()->count());
        }
        return (int) ($this->quantity ?? 0);
    }

    public function isLowStock(): bool
    {
        $threshold = (int) ($this->low_stock_threshold ?? 5);
        return $this->stock_count <= $threshold;
    }

    public function serials(): HasMany
    {
        return $this->hasMany(ProductSerial::class);
    }

    public function availableSerials(): HasMany
    {
        return $this->serials()->where('sold', false);
    }
}
