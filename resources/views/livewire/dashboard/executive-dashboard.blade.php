<div class="mx-auto max-w-7xl" x-data="{ menuOpen: true, fuelOpen: true, serviceOpen: true }">
    <div class="space-y-6 py-2">
        <section class="grid gap-4 lg:grid-cols-[1.2fr_0.8fr]">
            <div class="app-panel rounded-[32px] p-6 md:p-8">
                <p class="app-muted text-xs uppercase tracking-[0.24em]">Overview</p>
                <h1 class="app-title mt-3 text-3xl font-semibold tracking-tight">Ringkasan Operasional HFS</h1>
                <p class="app-muted mt-3 max-w-2xl text-sm">
                    Tampilan ini disederhanakan agar tim bisa fokus ke kondisi unit, alert penting, dan akses halaman utama tanpa banyak distraksi.
                </p>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                @foreach ($metrics as $label => $value)
                    <div class="app-panel rounded-[28px] p-5">
                        <p class="app-muted text-xs uppercase tracking-[0.2em]">{{ str_replace('_', ' ', $label) }}</p>
                        <p class="app-title mt-3 text-3xl font-semibold">{{ $value }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-[18rem_minmax(0,1fr)]">
            <aside class="app-panel rounded-[32px] p-5">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="app-muted text-xs uppercase tracking-[0.2em]">Menu</p>
                        <h2 class="app-title mt-1 text-lg font-semibold">Navigasi</h2>
                    </div>
                    <button type="button" class="app-panel-toggle rounded-full px-3 py-2 text-xs font-medium" @click="menuOpen = !menuOpen">
                        <span x-text="menuOpen ? 'Tutup' : 'Buka'"></span>
                    </button>
                </div>

                <div class="mt-5 space-y-2" x-show="menuOpen">
                    @forelse ($navigation as $item)
                        <a href="{{ route($item['route']) }}" class="block rounded-2xl px-4 py-3 text-sm font-medium transition" style="background: var(--surface-muted); color: var(--text);">
                            {{ $item['label'] }}
                        </a>
                    @empty
                        <div class="rounded-2xl border border-dashed px-4 py-3 text-sm app-muted" style="border-color: var(--border);">
                            Menu akan menyesuaikan permission user.
                        </div>
                    @endforelse
                </div>
            </aside>

            <section class="grid gap-6 xl:grid-cols-2">
                <article class="app-panel rounded-[32px] p-6">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="app-muted text-xs uppercase tracking-[0.2em]">Fuel</p>
                            <h2 class="app-title mt-2 text-xl font-semibold">Peringatan Solar</h2>
                        </div>
                        <button type="button" class="app-panel-toggle rounded-full px-3 py-2 text-xs font-medium" @click="fuelOpen = !fuelOpen">
                            <span x-text="fuelOpen ? 'Tutup' : 'Buka'"></span>
                        </button>
                    </div>

                    <div class="mt-5 space-y-3" x-show="fuelOpen">
                        @forelse ($fuelAlerts as $alert)
                            <div class="rounded-2xl px-4 py-4 app-status-danger">
                                <p class="font-semibold">{{ $alert->unit?->name ?? 'Unit' }}</p>
                                <p class="mt-1 text-sm">{{ $alert->input_date?->format('d M Y') }} • LPH {{ number_format((float) $alert->calculated_lph, 2) }}</p>
                                <p class="mt-2 text-xs">{{ $alert->fuel_flag_reason }}</p>
                            </div>
                        @empty
                            <div class="rounded-2xl px-4 py-4 text-sm app-muted" style="background: var(--surface-muted);">
                                Belum ada alert fuel.
                            </div>
                        @endforelse
                    </div>
                </article>

                <article class="app-panel rounded-[32px] p-6">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="app-muted text-xs uppercase tracking-[0.2em]">Maintenance</p>
                            <h2 class="app-title mt-2 text-xl font-semibold">Jadwal Service</h2>
                        </div>
                        <button type="button" class="app-panel-toggle rounded-full px-3 py-2 text-xs font-medium" @click="serviceOpen = !serviceOpen">
                            <span x-text="serviceOpen ? 'Tutup' : 'Buka'"></span>
                        </button>
                    </div>

                    <div class="mt-5 space-y-3" x-show="serviceOpen">
                        @forelse ($serviceAlerts as $unit)
                            <div class="rounded-2xl px-4 py-4" style="background: var(--surface-muted);">
                                <div class="flex items-center justify-between gap-3">
                                    <div>
                                        <p class="app-title font-semibold">{{ $unit->name }}</p>
                                        <p class="app-muted mt-1 text-sm">HM {{ number_format((float) $unit->current_hm, 2) }}</p>
                                    </div>
                                    <span @class([
                                        'rounded-full px-3 py-1 text-xs font-medium',
                                        'app-status-danger' => $unit->latestHourMeterLog?->service_alert_level === 'due',
                                        'app-status-warning' => $unit->latestHourMeterLog?->service_alert_level === 'warning',
                                        'app-status-success' => $unit->latestHourMeterLog?->service_alert_level === 'normal',
                                    ])>
                                        {{ strtoupper($unit->latestHourMeterLog?->service_alert_level ?? 'normal') }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-2xl px-4 py-4 text-sm app-muted" style="background: var(--surface-muted);">
                                Belum ada unit mendekati jadwal service.
                            </div>
                        @endforelse
                    </div>
                </article>
            </section>
        </div>
    </div>
</div>
