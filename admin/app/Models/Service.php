<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Service extends Model
{
    protected $fillable = [
        'slug',
        'value',
        'title',
        'icon',
        'price',
        'hero_mot_price',
        'price_label',
        'price_display',
        'service_category_id',
        'is_quote',
        'keywords',
        'sort_order',
        'combo_badge',
        'combo_subtitle',
        'combo_features',
        'combo_saving',
        'is_combo_hot',
        'combo_display_price',
        'features',
    ];

    protected function casts(): array
    {
        return [
            'keywords' => 'array',
            'features' => 'array',
            'combo_features' => 'array',
            'is_quote' => 'boolean',
            'is_combo_hot' => 'boolean',
            'price' => 'decimal:2',
            'hero_mot_price' => 'decimal:2',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'service_category_id');
    }
}
