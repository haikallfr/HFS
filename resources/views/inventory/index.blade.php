<x-app-layout>
    <x-slot name="header">
        <div>
            <p class="app-muted text-xs uppercase tracking-[0.24em]">Inventory</p>
            <h1 class="app-title mt-2 text-2xl font-semibold">Stok dan Pergerakan Barang</h1>
        </div>
    </x-slot>

    <div class="space-y-6" x-data="{ stockOpen: true, movementOpen: true }">
        <section class="grid gap-4 md:grid-cols-2">
            <div class="app-panel rounded-[28px] p-5">
                <p class="app-muted text-xs uppercase tracking-[0.2em]">Item</p>
                <p class="app-title mt-3 text-3xl font-semibold">{{ $inventoryItems->count() }}</p>
            </div>
            <div class="app-panel rounded-[28px] p-5">
                <p class="app-muted text-xs uppercase tracking-[0.2em]">Movement</p>
                <p class="app-title mt-3 text-3xl font-semibold">{{ $inventoryMovements->count() }}</p>
            </div>
        </section>

        <section class="grid gap-6 xl:grid-cols-2">
            <div class="app-panel rounded-[32px] p-6">
                <div class="mb-4 flex items-start justify-between gap-4">
                    <div>
                        <p class="app-muted text-xs uppercase tracking-[0.2em]">Stock</p>
                        <h2 class="app-title mt-2 text-xl font-semibold">Daftar Inventory</h2>
                    </div>
                    <button type="button" class="app-panel-toggle rounded-full px-4 py-2 text-sm" @click="stockOpen = !stockOpen">
                        <span x-text="stockOpen ? 'Minimize' : 'Buka'"></span>
                    </button>
                </div>

                <div class="space-y-3" x-show="stockOpen">
                    @forelse ($inventoryItems as $item)
                        <div class="rounded-2xl px-4 py-4" style="background: var(--surface-muted); border: 1px solid var(--border);">
                            <div class="flex items-center justify-between gap-3">
                                <div>
                                    <p class="font-semibold app-title">{{ $item->name }}</p>
                                    <p class="mt-1 text-sm app-muted">{{ $item->sku }} • {{ $item->uom }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold app-title">{{ number_format((float) $item->current_stock, 2) }}</p>
                                    <p class="text-xs app-muted">Min {{ number_format((float) $item->minimum_stock, 2) }}</p>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl px-4 py-4 text-sm app-muted" style="background: var(--surface-muted);">
                            Belum ada inventory.
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="app-panel rounded-[32px] p-6">
                <div class="mb-4 flex items-start justify-between gap-4">
                    <div>
                        <p class="app-muted text-xs uppercase tracking-[0.2em]">Audit</p>
                        <h2 class="app-title mt-2 text-xl font-semibold">Movement Terakhir</h2>
                    </div>
                    <button type="button" class="app-panel-toggle rounded-full px-4 py-2 text-sm" @click="movementOpen = !movementOpen">
                        <span x-text="movementOpen ? 'Minimize' : 'Buka'"></span>
                    </button>
                </div>

                <div class="space-y-3" x-show="movementOpen">
                    @forelse ($inventoryMovements as $movement)
                        <div class="rounded-2xl px-4 py-4" style="background: var(--surface-muted); border: 1px solid var(--border);">
                            <p class="font-semibold app-title">{{ $movement->movement_type }}</p>
                            <p class="mt-1 text-sm app-muted">Ref {{ $movement->reference_type }} #{{ $movement->reference_id }}</p>
                            <p class="mt-2 text-xs app-muted">Qty {{ number_format((float) $movement->quantity, 2) }}</p>
                        </div>
                    @empty
                        <div class="rounded-2xl px-4 py-4 text-sm app-muted" style="background: var(--surface-muted);">
                            Belum ada movement inventory.
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
</x-app-layout>
