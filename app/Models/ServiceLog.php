<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceLog extends Model
{
    protected $fillable = [
        'unit_id',
        'service_hm',
        'service_date',
        'service_type',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'service_hm' => 'decimal:2',
            'service_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::saved(function (self $serviceLog): void {
            $serviceLog->unit()->update([
                'last_service_hm' => $serviceLog->service_hm,
                'last_service_at' => $serviceLog->service_date,
            ]);
        });
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
}
