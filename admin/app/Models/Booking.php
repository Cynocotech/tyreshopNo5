<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Booking extends Model
{
    protected $fillable = [
        'booking_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'vehicle_registration',
        'vehicle_make',
        'vehicle_model',
        'appointment_date',
        'appointment_time',
        'service_type',
        'total_amount',
        'canceled_at',
    ];

    protected function casts(): array
    {
        return [
            'appointment_date' => 'date',
            'total_amount' => 'decimal:2',
            'canceled_at' => 'datetime',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('canceled_at');
    }

    public function scopeCanceled(Builder $query): Builder
    {
        return $query->whereNotNull('canceled_at');
    }

    public function isCanceled(): bool
    {
        return $this->canceled_at !== null;
    }
}
