<?php

namespace App\Actions\Fleet;

use App\Models\HourMeterLog;
use App\Models\Unit;
use App\Models\User;
use App\Models\WorkArea;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PersistHmEntry
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function handle(array $payload, User $user): HourMeterLog
    {
        $photoPath = $this->storeCapturedPhoto($payload['capturePayload']);
        $unit = Unit::findOrFail($payload['unitId']);
        $workArea = WorkArea::findOrFail($payload['workAreaId']);

        return HourMeterLog::create([
            'unit_id' => $unit->id,
            'site_id' => $unit->site_id ?? $user->site_id,
            'work_area_id' => $workArea->id,
            'operator_id' => $user->id,
            'recorded_by' => $user->id,
            'input_date' => $payload['inputDate'],
            'shift' => $payload['shift'],
            'hm_start' => $payload['hmStart'],
            'hm_end' => $payload['hmEnd'],
            'fuel_liters' => $payload['fuelLiters'],
            'photo_path' => $photoPath,
            'photo_taken_at' => $payload['captureTimestamp'],
            'latitude' => $payload['latitude'],
            'longitude' => $payload['longitude'],
            'sync_status' => $payload['syncStatus'],
            'synced_at' => $payload['syncStatus'] === 'server' ? now() : null,
            'notes' => $payload['notes'] ?? null,
        ]);
    }

    protected function storeCapturedPhoto(string $payload): string
    {
        abort_unless(preg_match('/^data:image\/webp;base64,/', $payload) === 1, 422, 'Format foto harus WebP hasil kompresi dari kamera.');

        $binary = base64_decode(Str::after($payload, ','), true);

        abort_if($binary === false, 422, 'Payload foto tidak valid.');
        abort_if(strlen($binary) > 500 * 1024, 422, 'Ukuran foto melebihi batas 500 KB.');

        $path = 'hm-photos/'.now()->format('Y/m').'/'.Str::uuid().'.webp';
        Storage::disk('public')->put($path, $binary);

        return $path;
    }
}
