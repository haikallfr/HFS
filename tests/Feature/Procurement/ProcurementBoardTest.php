<?php

namespace Tests\Feature\Procurement;

use App\Livewire\Procurement\ProcurementBoard;
use App\Models\InventoryItem;
use App\Models\PurchaseRequisition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProcurementBoardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_site_admin_can_open_procurement_page(): void
    {
        $siteAdmin = User::where('email', 'siteadmin@hfs.local')->firstOrFail();

        $this->actingAs($siteAdmin)
            ->get(route('procurement.index'))
            ->assertOk()
            ->assertSee('Tambah Pengajuan PR');
    }

    public function test_purchase_requisition_can_be_submitted_from_procurement_page(): void
    {
        $siteAdmin = User::where('email', 'siteadmin@hfs.local')->firstOrFail();
        $inventoryItem = InventoryItem::query()->firstOrFail();

        Livewire::actingAs($siteAdmin)
            ->test(ProcurementBoard::class)
            ->call('openPrForm')
            ->set('siteId', $siteAdmin->site_id)
            ->set('neededDate', now()->addDays(2)->toDateString())
            ->set('notes', 'Kebutuhan mendesak untuk operasional site.')
            ->set('items.0.inventory_item_id', (string) $inventoryItem->id)
            ->set('items.0.description', $inventoryItem->name)
            ->set('items.0.quantity', '25')
            ->set('items.0.uom', $inventoryItem->uom)
            ->set('items.0.notes', 'Untuk stok mingguan')
            ->call('savePr')
            ->assertHasNoErrors();

        $pr = PurchaseRequisition::query()->latest('id')->first();

        $this->assertNotNull($pr);
        $this->assertSame('submitted', $pr->status);
        $this->assertSame($siteAdmin->id, $pr->requested_by);
        $this->assertCount(1, $pr->items);
    }

    public function test_owner_can_approve_submitted_purchase_requisition(): void
    {
        $owner = User::where('email', 'owner@hfs.local')->firstOrFail();
        $pr = PurchaseRequisition::where('status', 'submitted')->firstOrFail();

        Livewire::actingAs($owner)
            ->test(ProcurementBoard::class)
            ->call('approvePr', $pr->id)
            ->assertHasNoErrors();

        $pr->refresh();

        $this->assertSame('approved', $pr->status);
        $this->assertSame($owner->id, $pr->approved_by);
    }

    public function test_logistik_ho_can_access_procurement_approval_flow(): void
    {
        $logistik = User::where('email', 'logistik@hfs.local')->firstOrFail();

        $this->assertTrue($logistik->hasRole('Logistik HO'));
        $this->assertTrue($logistik->can('procurement.pr.approve'));

        $this->actingAs($logistik)
            ->get(route('procurement.index'))
            ->assertOk()
            ->assertSee('Approve');
    }
}
