<?php

namespace Tests\Feature\Fleet;

use App\Livewire\Fleet\HmEntryForm;
use App\Models\HourMeterLog;
use App\Models\Unit;
use App\Models\User;
use App\Models\WorkArea;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class HmEntryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_guest_is_redirected_from_hm_entry_page(): void
    {
        $this->get(route('fleet.hm-entry'))
            ->assertRedirect(route('login', absolute: false));
    }

    public function test_site_admin_can_open_hm_entry_page(): void
    {
        $user = User::where('email', 'siteadmin@hfs.local')->firstOrFail();

        $this->actingAs($user)
            ->get(route('fleet.hm-entry'))
            ->assertOk()
            ->assertSee('HM Input Anti-Fraud');
    }

    public function test_hm_entry_can_be_saved_with_camera_payload_and_gps(): void
    {
        Storage::fake('public');

        $user = User::where('email', 'siteadmin@hfs.local')->firstOrFail();
        $unit = Unit::where('code', 'EXC-001')->firstOrFail();
        $workArea = WorkArea::where('code', 'PIT-A1')->firstOrFail();

        Livewire::actingAs($user)
            ->test(HmEntryForm::class)
            ->set('unitId', $unit->id)
            ->set('workAreaId', $workArea->id)
            ->set('inputDate', now()->addDay()->toDateString())
            ->set('shift', 'day')
            ->set('hmStart', '254')
            ->set('hmEnd', '260')
            ->set('fuelLiters', '100')
            ->set('notes', 'Capture dari unit test')
            ->set('capturePayload', 'data:image/webp;base64,UklGRiIAAABXRUJQVlA4IBYAAAAwAQCdASoBAAEAAUAmJaACdLoB+AADsAD+8ut//NgVzXPv9//S4P0uD9Lg/9KQAAA=')
            ->set('captureTimestamp', now()->toISOString())
            ->set('latitude', -4.123456)
            ->set('longitude', 137.123456)
            ->set('syncStatus', 'server')
            ->call('save')
            ->assertHasNoErrors();

        $log = HourMeterLog::latest('id')->first();

        $this->assertNotNull($log);
        $this->assertSame('254.00', $log->hm_start);
        $this->assertSame('260.00', $log->hm_end);
        $this->assertNotNull($log->photo_path);
        Storage::disk('public')->assertExists($log->photo_path);
    }

    public function test_offline_queue_can_be_synced_when_connection_returns(): void
    {
        Storage::fake('public');

        $user = User::where('email', 'siteadmin@hfs.local')->firstOrFail();
        $unit = Unit::where('code', 'EXC-001')->firstOrFail();
        $workArea = WorkArea::where('code', 'PIT-A1')->firstOrFail();

        $response = $this->actingAs($user)->postJson(route('fleet.hm-entry.sync'), [
            'entries' => [[
                'local_id' => 'offline-001',
                'unitId' => $unit->id,
                'workAreaId' => $workArea->id,
                'inputDate' => now()->addDay()->toDateString(),
                'shift' => 'night',
                'hmStart' => '254',
                'hmEnd' => '261',
                'fuelLiters' => '90',
                'notes' => 'Sinkronisasi dari antrean offline',
                'capturePayload' => 'data:image/webp;base64,UklGRiIAAABXRUJQVlA4IBYAAAAwAQCdASoBAAEAAUAmJaACdLoB+AADsAD+8ut//NgVzXPv9//S4P0uD9Lg/9KQAAA=',
                'captureTimestamp' => now()->toISOString(),
                'latitude' => -4.123456,
                'longitude' => 137.123456,
            ]],
        ]);

        $response->assertOk()
            ->assertJsonPath('results.0.local_id', 'offline-001');

        $log = HourMeterLog::latest('id')->first();

        $this->assertNotNull($log);
        $this->assertSame('server', $log->sync_status);
        $this->assertNotNull($log->synced_at);
        Storage::disk('public')->assertExists($log->photo_path);
    }
}
