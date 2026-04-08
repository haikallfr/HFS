<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Unit extends Model
{
    protected $fillable = [
        'site_id',
        'code',
        'name',
        'category',
        'brand',
        'model',
        'standard_fuel_ratio',
        'current_hm',
        'last_service_hm',
        'last_service_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'standard_fuel_ratio' => 'decimal:2',
            'current_hm' => 'decimal:2',
            'last_service_hm' => 'decimal:2',
            'last_service_at' => 'datetime',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function hourMeterLogs(): HasMany
    {
        return $this->hasMany(HourMeterLog::class);
    }

    public function serviceLogs(): HasMany
    {
        return $this->hasMany(ServiceLog::class);
    }

    public function latestHourMeterLog(): HasOne
    {
        return $this->hasOne(HourMeterLog::class)->latestOfMany('input_date');
    }
}
