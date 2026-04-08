<?php

namespace Database\Seeders;

use App\Models\HourMeterLog;
use App\Models\InventoryItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequisition;
use App\Models\Site;
use App\Models\Unit;
use App\Models\User;
use App\Models\WorkArea;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $site = Site::query()->firstOrCreate(
            ['code' => 'PAPUA-HFS'],
            [
                'name' => 'HFS Papua Site',
                'region' => 'Papua',
                'is_remote' => true,
                'timezone' => 'Asia/Jayapura',
            ]
        );

        foreach (config('hfs.permissions', []) as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }

        $ownerRole = Role::findOrCreate('Owner', 'web');
        $ownerRole->syncPermissions(config('hfs.permissions', []));

        $siteAdminRole = Role::findOrCreate('Admin Site', 'web');
        $siteAdminRole->syncPermissions([
            'dashboard.view',
            'fleet.view',
            'fleet.manage',
            'fleet.hm.input',
            'fleet.fuel.input',
            'camera.capture',
            'camera.upload',
            'procurement.pr.create',
            'procurement.do.receive',
            'inventory.view',
            'inventory.manage',
            'reports.executive.view',
        ]);

        $logistikRole = Role::findOrCreate('Logistik HO', 'web');
        $logistikRole->syncPermissions([
            'dashboard.view',
            'procurement.pr.approve',
            'procurement.po.manage',
            'procurement.do.receive',
            'inventory.view',
            'inventory.audit.view',
            'reports.executive.view',
        ]);

        $owner = User::query()->firstOrCreate([
            'email' => 'owner@hfs.local',
        ], [
            'site_id' => $site->id,
            'name' => 'HFS Owner',
            'employee_id' => 'HFS-OWN-001',
            'phone' => '081200000001',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        $owner->assignRole($ownerRole);

        $siteAdmin = User::query()->firstOrCreate([
            'email' => 'siteadmin@hfs.local',
        ], [
            'site_id' => $site->id,
            'name' => 'Admin Site Papua',
            'employee_id' => 'HFS-SITE-001',
            'phone' => '081200000002',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        $siteAdmin->assignRole($siteAdminRole);

        $workArea = WorkArea::query()->firstOrCreate([
            'code' => 'PIT-A1',
        ], [
            'site_id' => $site->id,
            'name' => 'Pit A1',
            'latitude' => -4.1234567,
            'longitude' => 137.1234567,
            'notes' => 'Area produksi utama shift pagi.',
        ]);

        $unit = Unit::query()->firstOrCreate([
            'code' => 'EXC-001',
        ], [
            'site_id' => $site->id,
            'name' => 'Excavator 320D',
            'category' => 'Excavator',
            'brand' => 'Caterpillar',
            'model' => '320D',
            'standard_fuel_ratio' => 20,
            'current_hm' => 248,
            'last_service_hm' => 0,
            'status' => 'active',
        ]);

        HourMeterLog::query()->updateOrCreate([
            'unit_id' => $unit->id,
            'input_date' => now()->subDay()->toDateString(),
        ], [
            'site_id' => $site->id,
            'work_area_id' => $workArea->id,
            'recorded_by' => $siteAdmin->id,
            'operator_id' => $siteAdmin->id,
            'shift' => 'day',
            'hm_start' => 240,
            'hm_end' => 248,
            'fuel_liters' => 140,
            'latitude' => $workArea->latitude,
            'longitude' => $workArea->longitude,
            'sync_status' => 'server',
        ]);

        HourMeterLog::query()->updateOrCreate([
            'unit_id' => $unit->id,
            'input_date' => now()->toDateString(),
        ], [
            'site_id' => $site->id,
            'work_area_id' => $workArea->id,
            'recorded_by' => $siteAdmin->id,
            'operator_id' => $siteAdmin->id,
            'shift' => 'day',
            'hm_start' => 248,
            'hm_end' => 254,
            'fuel_liters' => 145,
            'latitude' => $workArea->latitude,
            'longitude' => $workArea->longitude,
            'sync_status' => 'server',
        ]);

        InventoryItem::query()->firstOrCreate([
            'sku' => 'SOLAR-B35',
        ], [
            'name' => 'Solar B35',
            'uom' => 'Liter',
            'minimum_stock' => 1000,
            'current_stock' => 5200,
            'is_active' => true,
        ]);

        $pr = PurchaseRequisition::query()->firstOrCreate([
            'pr_number' => 'PR-20260408-001',
        ], [
            'site_id' => $site->id,
            'requested_by' => $siteAdmin->id,
            'status' => 'submitted',
            'needed_date' => now()->addDays(3)->toDateString(),
            'notes' => 'Permintaan sparepart dan solar untuk operasional mingguan.',
            'created_by' => $siteAdmin->id,
            'updated_by' => $siteAdmin->id,
        ]);

        PurchaseOrder::query()->firstOrCreate([
            'po_number' => 'PO-20260408-001',
        ], [
            'purchase_requisition_id' => $pr->id,
            'supplier_name' => 'PT Mitra Tambang Supply',
            'status' => 'issued',
            'issued_at' => now(),
            'eta_date' => now()->addDays(5)->toDateString(),
            'created_by' => $owner->id,
            'updated_by' => $owner->id,
        ]);
    }
}
