<div class="space-y-6" x-data="{ prOpen: true, poOpen: true, doOpen: true }">
    @if (session('status'))
        <div class="app-status-success rounded-2xl px-4 py-3 text-sm">
            {{ session('status') }}
        </div>
    @endif

    <section class="grid gap-4 md:grid-cols-4">
        <div class="app-panel rounded-[28px] p-5 md:col-span-1">
            <p class="app-muted text-xs uppercase tracking-[0.2em]">PR</p>
            <p class="app-title mt-3 text-3xl font-semibold">{{ $purchaseRequisitions->count() }}</p>
        </div>
        <div class="app-panel rounded-[28px] p-5 md:col-span-1">
            <p class="app-muted text-xs uppercase tracking-[0.2em]">PO</p>
            <p class="app-title mt-3 text-3xl font-semibold">{{ $purchaseOrders->count() }}</p>
        </div>
        <div class="app-panel rounded-[28px] p-5 md:col-span-1">
            <p class="app-muted text-xs uppercase tracking-[0.2em]">DO</p>
            <p class="app-title mt-3 text-3xl font-semibold">{{ $deliveryOrders->count() }}</p>
        </div>
        <div class="app-panel rounded-[28px] p-5 md:col-span-1">
            <div class="flex h-full flex-col justify-between gap-4">
                <div>
                    <p class="app-muted text-xs uppercase tracking-[0.2em]">Action</p>
                    <p class="app-title mt-3 text-lg font-semibold">Pengajuan Baru</p>
                    <p class="mt-2 text-sm app-muted">Ajukan PR langsung dari halaman ini.</p>
                </div>
                <button wire:click="openPrForm" type="button" class="app-button rounded-full px-5 py-3 text-sm font-semibold">
                    Tambah Pengajuan PR
                </button>
            </div>
        </div>
    </section>

    <section class="app-panel rounded-[32px] p-6" x-show="$wire.prFormOpen" x-cloak>
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="app-muted text-xs uppercase tracking-[0.2em]">PR Form</p>
                <h2 class="app-title mt-2 text-2xl font-semibold">Pengajuan Purchase Requisition</h2>
                <p class="app-muted mt-2 text-sm">Isi kebutuhan site, lalu tambahkan item yang ingin diajukan.</p>
            </div>
            <button wire:click="cancelPrForm" type="button" class="app-panel-toggle rounded-full px-4 py-2 text-sm font-medium">
                Tutup
            </button>
        </div>

        <form wire:submit="savePr" class="mt-6 space-y-5">
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <label class="grid gap-2 text-sm">
                    <span class="app-title">Site</span>
                    <select wire:model="siteId" class="app-input rounded-2xl px-4 py-3">
                        <option value="">Pilih site</option>
                        @foreach ($sites as $site)
                            <option value="{{ $site->id }}">{{ $site->name }}</option>
                        @endforeach
                    </select>
                    @error('siteId') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </label>

                <label class="grid gap-2 text-sm">
                    <span class="app-title">Tanggal Dibutuhkan</span>
                    <input wire:model="neededDate" type="date" class="app-input rounded-2xl px-4 py-3">
                    @error('neededDate') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </label>

                <label class="grid gap-2 text-sm md:col-span-2 xl:col-span-1">
                    <span class="app-title">Catatan Umum</span>
                    <input wire:model="notes" type="text" class="app-input rounded-2xl px-4 py-3" placeholder="Contoh: kebutuhan operasional mingguan">
                    @error('notes') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </label>
            </div>

            @error('items') <div class="text-sm text-red-600">{{ $message }}</div> @enderror

            <div class="space-y-4">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="text-sm font-semibold app-title">Item Pengajuan</p>
                        <p class="text-xs app-muted">Bisa pilih dari master inventory atau isi manual jika item belum terdaftar.</p>
                    </div>
                    <button wire:click="addItem" type="button" class="app-button-secondary rounded-full px-4 py-2 text-sm font-medium">
                        Tambah Item
                    </button>
                </div>

                @foreach ($items as $index => $item)
                    <div class="rounded-[28px] p-4" style="background: var(--surface-muted); border: 1px solid var(--border);">
                        <div class="mb-4 flex items-center justify-between gap-3">
                            <p class="font-semibold app-title">Item {{ $index + 1 }}</p>
                            <button wire:click="removeItem({{ $index }})" type="button" class="app-panel-toggle rounded-full px-4 py-2 text-sm">
                                Hapus
                            </button>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
                            <label class="grid gap-2 text-sm xl:col-span-2">
                                <span class="app-title">Master Inventory</span>
                                <select wire:model.live="items.{{ $index }}.inventory_item_id" class="app-input rounded-2xl px-4 py-3">
                                    <option value="">Pilih item master</option>
                                    @foreach ($inventoryItems as $inventoryItem)
                                        <option value="{{ $inventoryItem->id }}">{{ $inventoryItem->name }} ({{ $inventoryItem->uom }})</option>
                                    @endforeach
                                </select>
                                @error("items.$index.inventory_item_id") <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            </label>

                            <label class="grid gap-2 text-sm xl:col-span-2">
                                <span class="app-title">Deskripsi Barang</span>
                                <input wire:model="items.{{ $index }}.description" type="text" class="app-input rounded-2xl px-4 py-3" placeholder="Isi jika barang custom">
                                @error("items.$index.description") <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            </label>

                            <label class="grid gap-2 text-sm">
                                <span class="app-title">Qty</span>
                                <input wire:model="items.{{ $index }}.quantity" type="number" step="0.01" min="0.01" class="app-input rounded-2xl px-4 py-3">
                                @error("items.$index.quantity") <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            </label>

                            <label class="grid gap-2 text-sm">
                                <span class="app-title">UOM</span>
                                <input wire:model="items.{{ $index }}.uom" type="text" class="app-input rounded-2xl px-4 py-3">
                                @error("items.$index.uom") <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            </label>

                            <label class="grid gap-2 text-sm md:col-span-2 xl:col-span-4">
                                <span class="app-title">Catatan Item</span>
                                <input wire:model="items.{{ $index }}.notes" type="text" class="app-input rounded-2xl px-4 py-3" placeholder="Spesifikasi, urgensi, atau vendor referensi">
                                @error("items.$index.notes") <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                            </label>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="flex justify-end gap-3">
                <button wire:click="cancelPrForm" type="button" class="app-button-secondary rounded-full px-5 py-3 text-sm font-semibold">
                    Batal
                </button>
                <button type="submit" class="app-button rounded-full px-5 py-3 text-sm font-semibold">
                    Ajukan PR
                </button>
            </div>
        </form>
    </section>

    <section class="app-panel rounded-[32px] p-6" x-show="$wire.poFormOpen" x-cloak>
        <div class="flex items-start justify-between gap-4">
            <div>
                <p class="app-muted text-xs uppercase tracking-[0.2em]">PO Form</p>
                <h2 class="app-title mt-2 text-2xl font-semibold">Generate Purchase Order</h2>
                <p class="app-muted mt-2 text-sm">Buat PO dari PR yang sudah approved.</p>
            </div>
            <button wire:click="cancelPoForm" type="button" class="app-panel-toggle rounded-full px-4 py-2 text-sm font-medium">
                Tutup
            </button>
        </div>

        <form wire:submit="savePo" class="mt-6 grid gap-4 md:grid-cols-3">
            <label class="grid gap-2 text-sm">
                <span class="app-title">PR Sumber</span>
                <select wire:model="poSourcePrId" class="app-input rounded-2xl px-4 py-3">
                    <option value="">Pilih PR approved</option>
                    @foreach ($purchaseRequisitions->where('status', 'approved') as $pr)
                        @if (! $purchaseOrders->pluck('purchase_requisition_id')->contains($pr->id))
                            <option value="{{ $pr->id }}">{{ $pr->pr_number }} - {{ $pr->site?->name ?? 'Tanpa site' }}</option>
                        @endif
                    @endforeach
                </select>
                @error('poSourcePrId') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </label>

            <label class="grid gap-2 text-sm">
                <span class="app-title">Supplier</span>
                <input wire:model="poSupplierName" type="text" class="app-input rounded-2xl px-4 py-3" placeholder="Nama vendor / supplier">
                @error('poSupplierName') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </label>

            <label class="grid gap-2 text-sm">
                <span class="app-title">ETA</span>
                <input wire:model="poEtaDate" type="date" class="app-input rounded-2xl px-4 py-3">
                @error('poEtaDate') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
            </label>

            <div class="md:col-span-3 flex justify-end gap-3">
                <button wire:click="cancelPoForm" type="button" class="app-button-secondary rounded-full px-5 py-3 text-sm font-semibold">
                    Batal
                </button>
                <button type="submit" class="app-button rounded-full px-5 py-3 text-sm font-semibold">
                    Buat PO
                </button>
            </div>
        </form>
    </section>

    <section class="grid gap-6 xl:grid-cols-3">
        <div class="app-panel rounded-[32px] p-6">
            <div class="mb-4 flex items-start justify-between gap-4">
                <div>
                    <p class="app-muted text-xs uppercase tracking-[0.2em]">PR</p>
                    <h2 class="app-title mt-2 text-xl font-semibold">Purchase Requisition</h2>
                </div>
                <button type="button" class="app-panel-toggle rounded-full px-4 py-2 text-sm" @click="prOpen = !prOpen">
                    <span x-text="prOpen ? 'Minimize' : 'Buka'"></span>
                </button>
            </div>
            <div class="space-y-3" x-show="prOpen">
                @forelse ($purchaseRequisitions as $pr)
                    <div class="rounded-2xl px-4 py-4" style="background: var(--surface-muted); border: 1px solid var(--border);">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="font-semibold app-title">{{ $pr->pr_number }}</p>
                                <p class="mt-1 text-sm app-muted">{{ $pr->site?->name ?? 'Tanpa site' }}</p>
                                <p class="mt-1 text-xs app-muted">Requester: {{ $pr->requester?->name ?? '-' }}</p>
                            </div>
                            <span class="rounded-full px-3 py-1 text-xs font-medium {{ $pr->status === 'approved' ? 'app-status-success' : ($pr->status === 'rejected' ? 'app-status-danger' : 'app-status-warning') }}">
                                {{ $pr->status }}
                            </span>
                        </div>
                        <p class="mt-2 text-xs app-muted">Needed: {{ $pr->needed_date?->format('d M Y') ?? '-' }}</p>
                        <p class="mt-1 text-xs app-muted">Items: {{ $pr->items->count() }}</p>
                        @if ($pr->approver)
                            <p class="mt-1 text-xs app-muted">Approver: {{ $pr->approver->name }}</p>
                        @endif
                        @if ($pr->notes)
                            <p class="mt-3 text-sm app-muted">{{ $pr->notes }}</p>
                        @endif

                        @if ($canApprovePr && $pr->status === 'submitted')
                            <div class="mt-4 flex flex-wrap gap-2">
                                <button wire:click="approvePr({{ $pr->id }})" type="button" class="app-button rounded-full px-4 py-2 text-sm font-semibold">
                                    Approve
                                </button>
                                <button wire:click="rejectPr({{ $pr->id }})" type="button" class="rounded-full px-4 py-2 text-sm font-semibold" style="background: var(--danger); color: white;">
                                    Reject
                                </button>
                            </div>
                        @endif

                        @if ($canManagePo && $pr->status === 'approved' && ! $purchaseOrders->pluck('purchase_requisition_id')->contains($pr->id))
                            <div class="mt-4">
                                <button wire:click="openPoForm({{ $pr->id }})" type="button" class="app-button-secondary rounded-full px-4 py-2 text-sm font-semibold">
                                    Generate PO
                                </button>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="rounded-2xl px-4 py-4 text-sm app-muted" style="background: var(--surface-muted);">
                        Belum ada PR.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="app-panel rounded-[32px] p-6">
            <div class="mb-4 flex items-start justify-between gap-4">
                <div>
                    <p class="app-muted text-xs uppercase tracking-[0.2em]">PO</p>
                    <h2 class="app-title mt-2 text-xl font-semibold">Purchase Order</h2>
                </div>
                <button type="button" class="app-panel-toggle rounded-full px-4 py-2 text-sm" @click="poOpen = !poOpen">
                    <span x-text="poOpen ? 'Minimize' : 'Buka'"></span>
                </button>
            </div>
            <div class="space-y-3" x-show="poOpen">
                @forelse ($purchaseOrders as $po)
                    <div class="rounded-2xl px-4 py-4" style="background: var(--surface-muted); border: 1px solid var(--border);">
                        <p class="font-semibold app-title">{{ $po->po_number }}</p>
                        <p class="mt-1 text-sm app-muted">{{ $po->supplier_name }}</p>
                        <p class="mt-1 text-xs app-muted">PR: {{ $po->purchaseRequisition?->pr_number ?? '-' }}</p>
                        <p class="mt-2 text-xs app-muted">Status: {{ $po->status }}</p>
                    </div>
                @empty
                    <div class="rounded-2xl px-4 py-4 text-sm app-muted" style="background: var(--surface-muted);">
                        Belum ada PO.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="app-panel rounded-[32px] p-6">
            <div class="mb-4 flex items-start justify-between gap-4">
                <div>
                    <p class="app-muted text-xs uppercase tracking-[0.2em]">DO</p>
                    <h2 class="app-title mt-2 text-xl font-semibold">Delivery Order</h2>
                </div>
                <button type="button" class="app-panel-toggle rounded-full px-4 py-2 text-sm" @click="doOpen = !doOpen">
                    <span x-text="doOpen ? 'Minimize' : 'Buka'"></span>
                </button>
            </div>
            <div class="space-y-3" x-show="doOpen">
                @forelse ($deliveryOrders as $do)
                    <div class="rounded-2xl px-4 py-4" style="background: var(--surface-muted); border: 1px solid var(--border);">
                        <p class="font-semibold app-title">{{ $do->do_number }}</p>
                        <p class="mt-1 text-sm app-muted">Status: {{ $do->status }}</p>
                        <p class="mt-2 text-xs app-muted">Received: {{ $do->received_at?->format('d M Y H:i') ?? '-' }}</p>
                    </div>
                @empty
                    <div class="rounded-2xl px-4 py-4 text-sm app-muted" style="background: var(--surface-muted);">
                        Belum ada DO.
                    </div>
                @endforelse
            </div>
        </div>
    </section>
</div>
