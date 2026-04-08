<?php

namespace App\Livewire\Fleet;

use App\Actions\Fleet\PersistHmEntry;
use App\Models\HourMeterLog;
use App\Models\Unit;
use App\Models\WorkArea;
use Livewire\Component;

class HmEntryForm extends Component
{
    public ?int $unitId = null;
    public ?int $workAreaId = null;
    public string $inputDate = '';
    public string $shift = 'day';
    public ?string $hmStart = null;
    public ?string $hmEnd = null;
    public ?string $fuelLiters = null;
    public ?string $notes = null;
    public ?string $capturePayload = null;
    public ?string $captureTimestamp = null;
    public ?float $latitude = null;
    public ?float $longitude = null;
    public string $syncStatus = 'server';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('fleet.hm.input'), 403);

        $this->inputDate = now()->toDateString();
    }

    public function updatedUnitId($value): void
    {
        if (! $value) {
            $this->hmStart = null;

            return;
        }

        $unit = Unit::with('latestHourMeterLog')->find($value);

        $this->hmStart = $unit?->latestHourMeterLog?->hm_end !== null
            ? (string) $unit->latestHourMeterLog->hm_end
            : (string) ($unit?->current_hm ?? 0);

        if (! $this->workAreaId) {
            $this->workAreaId = WorkArea::query()
                ->when(auth()->user()?->site_id, fn ($query, $siteId) => $query->where('site_id', $siteId))
                ->value('id');
        }
    }

    public function save(PersistHmEntry $persistHmEntry): void
    {
        $validated = $this->validate([
            'unitId' => ['required', 'exists:units,id'],
            'workAreaId' => ['required', 'exists:work_areas,id'],
            'inputDate' => ['required', 'date'],
            'shift' => ['required', 'in:day,night'],
            'hmStart' => ['required', 'numeric', 'min:0'],
            'hmEnd' => ['required', 'numeric', 'gt:hmStart'],
            'fuelLiters' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'capturePayload' => ['required', 'string'],
            'captureTimestamp' => ['required', 'date'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'syncStatus' => ['required', 'in:server,queued'],
        ], [
            'capturePayload.required' => 'Foto HM wajib diambil langsung dari kamera perangkat.',
            'latitude.required' => 'GPS wajib aktif sebelum foto diambil.',
            'longitude.required' => 'GPS wajib aktif sebelum foto diambil.',
        ]);

        $persistHmEntry->handle($validated, auth()->user());

        $this->reset([
            'unitId',
            'workAreaId',
            'hmStart',
            'hmEnd',
            'fuelLiters',
            'notes',
            'capturePayload',
            'captureTimestamp',
            'latitude',
            'longitude',
        ]);

        $this->inputDate = now()->toDateString();
        $this->shift = 'day';
        $this->syncStatus = 'server';

        $this->dispatch('hm-entry-saved');
        session()->flash('status', 'Input HM berhasil disimpan dengan foto ber-watermark.');
    }

    public function render()
    {
        return view('livewire.fleet.hm-entry-form', [
            'units' => Unit::query()
                ->when(auth()->user()?->site_id, fn ($query, $siteId) => $query->where('site_id', $siteId))
                ->orderBy('name')
                ->get(),
            'workAreas' => WorkArea::query()
                ->when(auth()->user()?->site_id, fn ($query, $siteId) => $query->where('site_id', $siteId))
                ->orderBy('name')
                ->get(),
            'recentLogs' => HourMeterLog::query()
                ->with(['unit', 'workArea'])
                ->latest('input_date')
                ->latest('id')
                ->take(6)
                ->get(),
        ]);
    }
}
