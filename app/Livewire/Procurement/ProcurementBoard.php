<?php

namespace App\Livewire\Procurement;

use App\Models\DeliveryOrder;
use App\Models\InventoryItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\PurchaseRequisition;
use App\Models\Site;
use Illuminate\Support\Str;
use Livewire\Component;

class ProcurementBoard extends Component
{
    public bool $prFormOpen = false;
    public ?int $siteId = null;
    public string $neededDate = '';
    public string $notes = '';
    public array $items = [];
    public bool $poFormOpen = false;
    public ?int $poSourcePrId = null;
    public string $poSupplierName = '';
    public string $poEtaDate = '';

    public function mount(): void
    {
        $user = auth()->user();

        abort_unless(
            $user?->can('procurement.pr.create')
            || $user?->can('procurement.pr.approve')
            || $user?->can('procurement.po.manage')
            || $user?->can('procurement.do.receive'),
            403
        );

        $this->siteId = $user?->site_id;
        $this->neededDate = now()->addDays(3)->toDateString();
        $this->items = [$this->emptyItem()];
        $this->poEtaDate = now()->addDays(5)->toDateString();
    }

    public function openPrForm(): void
    {
        $this->prFormOpen = true;
    }

    public function cancelPrForm(): void
    {
        $this->resetPrForm();
    }

    public function openPoForm(int $purchaseRequisitionId): void
    {
        abort_unless(auth()->user()?->can('procurement.po.manage'), 403);

        $pr = PurchaseRequisition::query()->findOrFail($purchaseRequisitionId);
        abort_if($pr->status !== 'approved', 422, 'PR harus disetujui dulu sebelum dibuatkan PO.');

        $this->poSourcePrId = $pr->id;
        $this->poSupplierName = '';
        $this->poEtaDate = now()->addDays(5)->toDateString();
        $this->poFormOpen = true;
    }

    public function cancelPoForm(): void
    {
        $this->resetPoForm();
    }

    public function addItem(): void
    {
        $this->items[] = $this->emptyItem();
    }

    public function removeItem(int $index): void
    {
        if (count($this->items) === 1) {
            $this->items = [$this->emptyItem()];

            return;
        }

        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    public function savePr(): void
    {
        $validated = $this->validate([
            'siteId' => ['nullable', 'exists:sites,id'],
            'neededDate' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.inventory_item_id' => ['nullable', 'exists:inventory_items,id'],
            'items.*.description' => ['nullable', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.uom' => ['required', 'string', 'max:20'],
            'items.*.notes' => ['nullable', 'string'],
        ]);

        foreach ($validated['items'] as $item) {
            if (blank($item['inventory_item_id']) && blank($item['description'])) {
                $this->addError('items', 'Setiap baris item harus memilih master inventory atau mengisi deskripsi barang.');

                return;
            }
        }

        $user = auth()->user();

        $pr = PurchaseRequisition::create([
            'pr_number' => $this->nextPrNumber(),
            'site_id' => $validated['siteId'],
            'requested_by' => $user?->id,
            'status' => 'submitted',
            'needed_date' => $validated['neededDate'],
            'notes' => $validated['notes'] ?: null,
            'created_by' => $user?->id,
            'updated_by' => $user?->id,
        ]);

        foreach ($validated['items'] as $item) {
            $inventoryItem = filled($item['inventory_item_id'])
                ? InventoryItem::find($item['inventory_item_id'])
                : null;

            $pr->items()->create([
                'inventory_item_id' => $inventoryItem?->id,
                'description' => $item['description'] ?: $inventoryItem?->name ?: 'Item PR',
                'quantity' => $item['quantity'],
                'uom' => $item['uom'] ?: $inventoryItem?->uom ?: 'Unit',
                'notes' => $item['notes'] ?: null,
            ]);
        }

        $this->resetPrForm();
        session()->flash('status', "PR {$pr->pr_number} berhasil diajukan.");
    }

    public function approvePr(int $purchaseRequisitionId): void
    {
        abort_unless(auth()->user()?->can('procurement.pr.approve'), 403);

        $pr = PurchaseRequisition::query()->findOrFail($purchaseRequisitionId);
        abort_if($pr->status !== 'submitted', 422, 'Hanya PR submitted yang bisa di-approve.');

        $pr->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        session()->flash('status', "PR {$pr->pr_number} berhasil di-approve.");
    }

    public function rejectPr(int $purchaseRequisitionId): void
    {
        abort_unless(auth()->user()?->can('procurement.pr.approve'), 403);

        $pr = PurchaseRequisition::query()->findOrFail($purchaseRequisitionId);
        abort_if($pr->status !== 'submitted', 422, 'Hanya PR submitted yang bisa di-reject.');

        $pr->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        session()->flash('status', "PR {$pr->pr_number} ditolak.");
    }

    public function savePo(): void
    {
        abort_unless(auth()->user()?->can('procurement.po.manage'), 403);

        $validated = $this->validate([
            'poSourcePrId' => ['required', 'exists:purchase_requisitions,id'],
            'poSupplierName' => ['required', 'string', 'max:255'],
            'poEtaDate' => ['required', 'date'],
        ]);

        $pr = PurchaseRequisition::query()->with('items')->findOrFail($validated['poSourcePrId']);
        abort_if($pr->status !== 'approved', 422, 'Hanya PR approved yang bisa dibuatkan PO.');
        abort_if(PurchaseOrder::query()->where('purchase_requisition_id', $pr->id)->exists(), 422, 'PR ini sudah memiliki PO.');

        $po = PurchaseOrder::create([
            'po_number' => $this->nextPoNumber(),
            'purchase_requisition_id' => $pr->id,
            'supplier_name' => $validated['poSupplierName'],
            'status' => 'issued',
            'issued_at' => now(),
            'eta_date' => $validated['poEtaDate'],
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        foreach ($pr->items as $item) {
            PurchaseOrderItem::create([
                'purchase_order_id' => $po->id,
                'inventory_item_id' => $item->inventory_item_id,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'uom' => $item->uom,
                'unit_price' => 0,
            ]);
        }

        $pr->update([
            'updated_by' => auth()->id(),
        ]);

        $this->resetPoForm();
        session()->flash('status', "PO {$po->po_number} berhasil dibuat dari {$pr->pr_number}.");
    }

    public function updatedItems($value, string $key): void
    {
        if (! str($key)->endsWith('.inventory_item_id')) {
            return;
        }

        [$index] = explode('.', $key);
        $inventoryItemId = (int) $value;

        if (! $inventoryItemId) {
            return;
        }

        $item = InventoryItem::find($inventoryItemId);

        if (! $item) {
            return;
        }

        $this->items[(int) $index]['description'] = $item->name;
        $this->items[(int) $index]['uom'] = $item->uom;
    }

    public function render()
    {
        return view('livewire.procurement.procurement-board', [
            'sites' => Site::query()->orderBy('name')->get(),
            'inventoryItems' => InventoryItem::query()->where('is_active', true)->orderBy('name')->get(),
            'purchaseRequisitions' => PurchaseRequisition::query()->with(['site', 'items', 'requester', 'approver'])->latest()->get(),
            'purchaseOrders' => PurchaseOrder::query()->with('purchaseRequisition')->latest()->get(),
            'deliveryOrders' => DeliveryOrder::query()->latest()->get(),
            'canApprovePr' => auth()->user()?->can('procurement.pr.approve') ?? false,
            'canManagePo' => auth()->user()?->can('procurement.po.manage') ?? false,
        ]);
    }

    protected function resetPrForm(): void
    {
        $this->resetErrorBag();
        $this->siteId = auth()->user()?->site_id;
        $this->neededDate = now()->addDays(3)->toDateString();
        $this->notes = '';
        $this->items = [$this->emptyItem()];
        $this->prFormOpen = false;
    }

    protected function resetPoForm(): void
    {
        $this->resetErrorBag();
        $this->poFormOpen = false;
        $this->poSourcePrId = null;
        $this->poSupplierName = '';
        $this->poEtaDate = now()->addDays(5)->toDateString();
    }

    protected function emptyItem(): array
    {
        return [
            'inventory_item_id' => '',
            'description' => '',
            'quantity' => '1',
            'uom' => 'Unit',
            'notes' => '',
        ];
    }

    protected function nextPrNumber(): string
    {
        $prefix = 'PR-'.now()->format('Ymd').'-';
        $latestToday = PurchaseRequisition::query()
            ->where('pr_number', 'like', $prefix.'%')
            ->latest('id')
            ->value('pr_number');

        $nextSequence = 1;

        if ($latestToday && Str::startsWith($latestToday, $prefix)) {
            $nextSequence = ((int) Str::afterLast($latestToday, '-')) + 1;
        }

        return $prefix.str_pad((string) $nextSequence, 3, '0', STR_PAD_LEFT);
    }

    protected function nextPoNumber(): string
    {
        $prefix = 'PO-'.now()->format('Ymd').'-';
        $latestToday = PurchaseOrder::query()
            ->where('po_number', 'like', $prefix.'%')
            ->latest('id')
            ->value('po_number');

        $nextSequence = 1;

        if ($latestToday && Str::startsWith($latestToday, $prefix)) {
            $nextSequence = ((int) Str::afterLast($latestToday, '-')) + 1;
        }

        return $prefix.str_pad((string) $nextSequence, 3, '0', STR_PAD_LEFT);
    }
}
