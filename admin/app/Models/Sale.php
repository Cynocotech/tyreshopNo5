<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Sale extends Model
{
    protected $fillable = ['reference', 'total', 'user_id', 'completed_at', 'payment_method', 'amount_tendered', 'payment_reference', 'customer_name', 'customer_email', 'customer_phone', 'customer_address', 'customer_vrn', 'booking_id'];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'amount_tendered' => 'decimal:2',
            'completed_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    public static function generateRef(): string
    {
        return 'S' . date('Ymd') . '-' . strtoupper(Str::random(6));
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
