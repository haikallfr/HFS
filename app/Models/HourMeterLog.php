<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class HourMeterLog extends Model
{
    protected $fillable = [
        'unit_id',
        'site_id',
        'work_area_id',
        'operator_id',
        'recorded_by',
        'input_date',
        'shift',
        'hm_start',
        'hm_end',
        'fuel_liters',
        'calculated_lph',
        'is_fuel_flagged',
        'fuel_flag_reason',
        'photo_path',
        'photo_taken_at',
        'latitude',
        'longitude',
        'sync_status',
        'synced_at',
        'service_alert_level',
        'service_due_hm',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'input_date' => 'date',
            'hm_start' => 'decimal:2',
            'hm_end' => 'decimal:2',
            'fuel_liters' => 'decimal:2',
            'calculated_lph' => 'decimal:2',
            'is_fuel_flagged' => 'boolean',
            'photo_taken_at' => 'datetime',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'synced_at' => 'datetime',
            'service_due_hm' => 'decimal:2',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $log): void {
            $log->guardHourMeterSequence();
            $log->calculateFuelDiscrepancy();
            $log->calculateServiceAlert();
        });

        static::saved(function (self $log): void {
            $log->unit()->update([
                'current_hm' => $log->hm_end,
            ]);
        });
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function workArea(): BelongsTo
    {
        return $this->belongsTo(WorkArea::class);
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    protected function guardHourMeterSequence(): void
    {
        if ($this->hm_end <= $this->hm_start) {
            throw ValidationException::withMessages([
                'hm_end' => 'HM akhir harus lebih besar dari HM awal.',
            ]);
        }

        $previousLog = static::query()
            ->where('unit_id', $this->unit_id)
            ->whereKeyNot($this->getKey())
            ->whereDate('input_date', '<=', $this->input_date)
            ->orderByDesc('input_date')
            ->orderByDesc('id')
            ->first();

        if ($previousLog && (float) $previousLog->hm_end !== (float) $this->hm_start) {
            throw ValidationException::withMessages([
                'hm_start' => 'HM awal wajib sama dengan HM akhir input sebelumnya untuk unit ini.',
            ]);
        }
    }

    protected function calculateFuelDiscrepancy(): void
    {
        $operatingHours = (float) $this->hm_end - (float) $this->hm_start;

        if ($operatingHours <= 0) {
            $this->calculated_lph = 0;
            $this->is_fuel_flagged = false;
            $this->fuel_flag_reason = null;

            return;
        }

        $this->calculated_lph = round(((float) $this->fuel_liters / $operatingHours), 2);

        $standardRatio = (float) optional($this->unit)->standard_fuel_ratio;
        $this->is_fuel_flagged = $standardRatio > 0 && $this->calculated_lph > $standardRatio;
        $this->fuel_flag_reason = $this->is_fuel_flagged
            ? 'LPH melebihi standard fuel ratio unit.'
            : null;
    }

    protected function calculateServiceAlert(): void
    {
        $lastServiceHm = (float) (optional($this->unit)->last_service_hm ?? 0);
        $serviceInterval = 250;
        $nextServiceHm = $lastServiceHm > 0 ? $lastServiceHm + $serviceInterval : $serviceInterval;
        $remainingHm = $nextServiceHm - (float) $this->hm_end;

        $this->service_due_hm = $nextServiceHm;
        $this->service_alert_level = match (true) {
            $remainingHm <= 0 => 'due',
            $remainingHm <= 25 => 'warning',
            default => 'normal',
        };
    }
}
