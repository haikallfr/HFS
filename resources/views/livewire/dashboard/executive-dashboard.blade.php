<div class="mx-auto max-w-7xl" x-data="{ menuOpen: true, fuelOpen: true, serviceOpen: true }">
    <div class="py-2 space-y-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <p class="app-muted text-sm uppercase tracking-[0.24em]">HFS Operations</p>
                <h1 class="app-title mt-2 text-3xl font-semibold tracking-tight">Executive Dashboard</h1>
                <p class="app-muted mt-3 max-w-2xl text-sm">
                    Ringkasan singkat kondisi operasional site, tanpa tampilan yang terlalu padat.
                </p>
            </div>

            <div class="grid gap-3 sm:grid-cols-2 lg:w-[28rem]">
                @foreach ($metrics as $label => $value)
                    <div class="app-panel rounded-2xl p-4">
                        <p class="app-muted text-xs uppercase tracking-[0.2em]">{{ str_replace('_', ' ', $label) }}</p>
                        <p class="app-title mt-2 text-3xl font-semibold">{{ $value }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-[17rem_minmax(0,1fr)]">
            <aside class="app-panel rounded-3xl p-5">
                <div class="flex items-center justify-between gap-3">
                    <div>
                        <p class="app-muted text-xs uppercase tracking-[0.2em]">Menu</p>
                        <h2 class="app-title mt-1 text-lg font-semibold">Akses Cepat</h2>
                    </div>
                    <button type="button" class="app-panel-toggle rounded-xl px-3 py-2 text-xs font-medium" @click="menuOpen = !menuOpen">
                        <span x-text="menuOpen ? 'Minimize' : 'Buka'"></span>
                    </button>
                </div>

                <div class="mt-5 space-y-3" x-show="menuOpen">
                    @forelse ($navigation as $item)
                        <a href="{{ route($item['route']) }}" class="app-panel-strong app-title block rounded-2xl px-4 py-3 text-sm transition hover:opacity-80">
                            {{ $item['label'] }}
                        </a>
                    @empty
                        <div class="rounded-2xl border border-dashed px-4 py-3 text-sm app-muted" style="border-color: var(--border);">
                            Menu muncul sesuai permission user yang sedang login.
                        </div>
                    @endforelse
                </div>
            </aside>

            <section class="grid gap-6 xl:grid-cols-2">
                <article class="app-panel rounded-3xl p-6">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="app-muted text-xs uppercase tracking-[0.2em]">Fuel vs HM</p>
                            <h2 class="app-title mt-2 text-xl font-semibold">Peringatan Solar</h2>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="app-status-danger rounded-full px-3 py-1 text-xs font-medium">Perlu cek</div>
                            <button type="button" class="app-panel-toggle rounded-xl px-3 py-2 text-xs font-medium" @click="fuelOpen = !fuelOpen">
                                <span x-text="fuelOpen ? 'Minimize' : 'Buka'"></span>
                            </button>
                        </div>
                    </div>

                    <div class="mt-6 space-y-3" x-show="fuelOpen">
                        @forelse ($fuelAlerts as $alert)
                            <div class="rounded-2xl p-4 app-status-danger">
                                <div class="flex items-start justify-between gap-4">
                                    <div>
                                        <p class="font-medium">{{ $alert->unit?->name ?? 'Unit' }}</p>
                                        <p class="mt-1 text-sm">
                                            {{ $alert->input_date?->format('d M Y') }} • LPH {{ number_format((float) $alert->calculated_lph, 2) }}
                                        </p>
                                    </div>
                                    <span class="rounded-full px-2 py-1 text-xs" style="background: color-mix(in srgb, var(--danger) 12%, transparent);">
                                        {{ $alert->fuel_flag_reason }}
                                    </span>
                                </div>
                            </div>
                        @empty
                            <div class="rounded-2xl border border-dashed p-4 text-sm app-muted" style="border-color: var(--border);">
                                Belum ada alert fuel. Data akan muncul otomatis setelah HM log masuk.
                            </div>
                        @endforelse
                    </div>
                </article>

                <article class="app-panel rounded-3xl p-6">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="app-muted text-xs uppercase tracking-[0.2em]">Maintenance</p>
                            <h2 class="app-title mt-2 text-xl font-semibold">Jadwal Service</h2>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="app-status-warning rounded-full px-3 py-1 text-xs font-medium">250 HM cycle</div>
                            <button type="button" class="app-panel-toggle rounded-xl px-3 py-2 text-xs font-medium" @click="serviceOpen = !serviceOpen">
                                <span x-text="serviceOpen ? 'Minimize' : 'Buka'"></span>
                            </button>
                        </div>
                    </div>

                    <div class="mt-6 space-y-3" x-show="serviceOpen">
                        @forelse ($serviceAlerts as $unit)
                            <div class="app-panel-strong rounded-2xl p-4">
                                <div class="flex items-center justify-between gap-4">
                                    <div>
                                        <p class="app-title font-medium">{{ $unit->name }}</p>
                                        <p class="app-muted mt-1 text-sm">
                                            {{ $unit->site?->name ?? 'Site belum diatur' }} • HM {{ number_format((float) $unit->current_hm, 2) }}
                                        </p>
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
                            <div class="rounded-2xl border border-dashed p-4 text-sm app-muted" style="border-color: var(--border);">
                                Belum ada unit yang mendekati window service.
                            </div>
                        @endforelse
                    </div>
                </article>
            </section>
        </div>
    </div>
</div>
