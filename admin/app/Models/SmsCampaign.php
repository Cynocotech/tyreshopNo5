<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsCampaign extends Model
{
    protected $fillable = [
        'name',
        'message',
        'source',
        'total_recipients',
        'sent_count',
        'failed_count',
    ];

    protected function casts(): array
    {
        return [
            'total_recipients' => 'integer',
            'sent_count' => 'integer',
            'failed_count' => 'integer',
        ];
    }

    public function getSuccessRateAttribute(): ?float
    {
        if ($this->total_recipients === 0) {
            return null;
        }
        return round(($this->sent_count / $this->total_recipients) * 100, 1);
    }
}
