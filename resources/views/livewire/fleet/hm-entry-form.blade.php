<div
    x-data="hmCapture($wire)"
    x-on:hm-entry-saved.window="resetCapture()"
    class="space-y-6"
>
    @if (session('status'))
        <div class="app-status-success rounded-2xl px-4 py-3 text-sm">
            {{ session('status') }}
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-[0.95fr_1.05fr]">
        <section class="app-panel rounded-3xl p-6" x-data="{ open: true }">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="app-muted text-xs uppercase tracking-[0.24em]">Field Input</p>
                    <h2 class="app-title mt-2 text-2xl font-semibold">HM Input</h2>
                    <p class="app-muted mt-2 text-sm">
                        Alur input dibuat lebih singkat: aktifkan lokasi, buka kamera, ambil foto, lalu simpan.
                    </p>
                </div>
                <button type="button" class="app-panel-toggle rounded-xl px-3 py-2 text-xs font-medium" @click="open = !open">
                    <span x-text="open ? 'Minimize' : 'Buka'"></span>
                </button>
            </div>

            <div class="mt-6 space-y-5" x-show="open">
                <div class="grid gap-3 sm:grid-cols-2">
                    <div class="app-panel-strong rounded-2xl p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="app-muted text-xs uppercase tracking-[0.2em]">Lokasi</p>
                                <p class="app-title mt-2 text-base font-semibold" x-text="gpsReady ? 'Lokasi terkunci' : 'Lokasi belum aktif'"></p>
                                <p class="app-muted mt-1 text-sm" x-text="gpsMessage"></p>
                            </div>
                            <span class="rounded-full px-3 py-1 text-xs font-medium" :class="gpsReady ? 'app-status-success' : 'app-status-warning'">
                                <span x-text="gpsReady ? 'Siap' : 'Perlu izin'"></span>
                            </span>
                        </div>
                        <p class="app-muted mt-3 text-xs" x-text="gpsHelpText"></p>
                    </div>

                    <div class="app-panel-strong rounded-2xl p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="app-muted text-xs uppercase tracking-[0.2em]">Sinkronisasi</p>
                                <p class="app-title mt-2 text-base font-semibold" x-text="syncStatusValue === 'queued' ? 'Offline queue aktif' : 'Realtime ke server'"></p>
                                <p class="app-muted mt-1 text-sm" x-text="queuedCount ? `${queuedCount} antrean lokal menunggu sync.` : 'Tidak ada antrean lokal.'"></p>
                            </div>
                            <span class="app-chip rounded-full px-3 py-1 text-xs font-medium" x-text="syncStatusValue === 'queued' ? 'Offline' : 'Online'"></span>
                        </div>
                        <template x-if="lastSyncMessage">
                            <p class="app-muted mt-3 text-xs" x-text="lastSyncMessage"></p>
                        </template>
                    </div>

                    <div class="app-panel-strong rounded-2xl p-4 sm:col-span-2">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="app-muted text-xs uppercase tracking-[0.2em]">Kamera</p>
                                <p class="app-title mt-2 text-base font-semibold" x-text="cameraReady ? 'Kamera siap' : 'Kamera belum siap'"></p>
                                <p class="app-muted mt-1 text-sm" x-text="cameraMessage"></p>
                            </div>
                            <span class="rounded-full px-3 py-1 text-xs font-medium" :class="cameraReady ? 'app-status-success' : 'app-status-warning'">
                                <span x-text="cameraReady ? 'Aktif' : 'Cek izin'"></span>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="button" @click="requestGps(true)" :disabled="locating || !gpsSupported" class="app-button inline-flex items-center rounded-xl px-4 py-3 text-sm font-semibold disabled:cursor-not-allowed disabled:opacity-60">
                        <span x-text="locating ? 'Mencari lokasi...' : 'Aktifkan Lokasi'"></span>
                    </button>
                    <button
                        type="button"
                        @click="openLiveCamera()"
                        :disabled="!gpsReady || openingCamera"
                        class="app-button-secondary inline-flex items-center rounded-xl px-4 py-3 text-sm font-medium disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        <span x-text="openingCamera ? 'Membuka kamera...' : 'Buka Kamera'"></span>
                    </button>
                    <button
                        type="button"
                        @click="captureFromLiveCamera()"
                        :disabled="!gpsReady || !cameraReady || processing"
                        class="app-button-secondary inline-flex items-center rounded-xl px-4 py-3 text-sm font-medium disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        <span x-text="processing ? 'Memproses foto...' : 'Ambil Foto'"></span>
                    </button>
                    <button
                        type="button"
                        @click="triggerFileFallback()"
                        :disabled="!gpsReady || processing"
                        class="app-button-secondary inline-flex items-center rounded-xl px-4 py-3 text-sm font-medium disabled:cursor-not-allowed disabled:opacity-60"
                    >
                        Upload/Camera Fallback
                    </button>
                </div>

                <form x-ref="form" @submit.prevent="submitEntry()" class="space-y-5">
                    <div class="grid gap-5 md:grid-cols-2">
                        <label class="grid gap-2 text-sm">
                            <span class="app-title">Unit</span>
                            <select name="unitId" wire:model.live="unitId" class="app-input rounded-2xl px-4 py-3">
                                <option value="">Pilih unit</option>
                                @foreach ($units as $unit)
                                    <option value="{{ $unit->id }}">{{ $unit->code }} • {{ $unit->name }}</option>
                                @endforeach
                            </select>
                            @error('unitId') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </label>

                        <label class="grid gap-2 text-sm">
                            <span class="app-title">Area kerja</span>
                            <select name="workAreaId" wire:model="workAreaId" class="app-input rounded-2xl px-4 py-3">
                                <option value="">Pilih area</option>
                                @foreach ($workAreas as $area)
                                    <option value="{{ $area->id }}">{{ $area->code }} • {{ $area->name }}</option>
                                @endforeach
                            </select>
                            @error('workAreaId') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </label>

                        <label class="grid gap-2 text-sm">
                            <span class="app-title">Tanggal input</span>
                            <input name="inputDate" wire:model="inputDate" type="date" class="app-input rounded-2xl px-4 py-3">
                            @error('inputDate') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </label>

                        <label class="grid gap-2 text-sm">
                            <span class="app-title">Shift</span>
                            <select name="shift" wire:model="shift" class="app-input rounded-2xl px-4 py-3">
                                <option value="day">Day</option>
                                <option value="night">Night</option>
                            </select>
                        </label>

                        <label class="grid gap-2 text-sm">
                            <span class="app-title">HM awal</span>
                            <input name="hmStart" wire:model="hmStart" type="number" step="0.01" min="0" class="app-input rounded-2xl px-4 py-3">
                            @error('hmStart') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </label>

                        <label class="grid gap-2 text-sm">
                            <span class="app-title">HM akhir</span>
                            <input name="hmEnd" wire:model="hmEnd" type="number" step="0.01" min="0" class="app-input rounded-2xl px-4 py-3">
                            @error('hmEnd') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </label>

                        <label class="grid gap-2 text-sm md:col-span-2">
                            <span class="app-title">Solar diisi (liter)</span>
                            <input name="fuelLiters" wire:model="fuelLiters" type="number" step="0.01" min="0" class="app-input rounded-2xl px-4 py-3">
                            @error('fuelLiters') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </label>
                    </div>

                    <label class="grid gap-2 text-sm">
                        <span class="app-title">Catatan</span>
                        <textarea name="notes" wire:model="notes" rows="4" class="app-input rounded-2xl px-4 py-3"></textarea>
                    </label>

                    @error('capturePayload') <span class="block text-xs text-red-600">{{ $message }}</span> @enderror
                    @error('latitude') <span class="block text-xs text-red-600">{{ $message }}</span> @enderror

                    <div class="flex flex-wrap items-center justify-between gap-3 border-t pt-4" style="border-color: var(--border);">
                        <div class="app-muted text-sm">
                            Foto wajib berasal dari kamera langsung dan menyertakan watermark koordinat.
                        </div>

                        <button type="submit" :disabled="submitting || processing" class="app-button inline-flex items-center rounded-xl px-5 py-3 text-sm font-semibold disabled:cursor-not-allowed disabled:opacity-60">
                            <span x-text="submitting ? 'Menyimpan...' : 'Simpan Input HM'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </section>

        <section class="space-y-6">
            <div class="app-panel rounded-3xl p-6" x-data="{ open: true }">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="app-muted text-xs uppercase tracking-[0.24em]">Camera</p>
                        <h2 class="app-title mt-2 text-2xl font-semibold">Preview Kamera</h2>
                    </div>
                    <button type="button" class="app-panel-toggle rounded-xl px-3 py-2 text-xs font-medium" @click="open = !open">
                        <span x-text="open ? 'Minimize' : 'Buka'"></span>
                    </button>
                </div>

                <div class="mt-6 space-y-4" x-show="open">
                    <input
                        id="hm-camera-input"
                        x-ref="cameraInput"
                        type="file"
                        accept="image/*"
                        capture="environment"
                        class="sr-only"
                        @change="handleCameraFile($event)"
                    >

                    <div class="grid gap-4 lg:grid-cols-2">
                        <div class="overflow-hidden rounded-3xl border" style="border-color: var(--border); background: var(--surface-strong);">
                            <video
                                x-ref="video"
                                autoplay
                                playsinline
                                muted
                                x-show="!previewData && isStreamActive"
                                class="aspect-[4/5] w-full object-cover"
                            ></video>
                            <div
                                x-show="!previewData && !isStreamActive"
                                class="grid aspect-[4/5] place-items-center p-6 text-center text-sm app-muted"
                            >
                                Buka kamera untuk menampilkan preview live di halaman.
                            </div>
                            <template x-if="previewData">
                                <img :src="previewData" alt="Preview HM" class="aspect-[4/5] w-full object-cover">
                            </template>
                        </div>

                        <div class="overflow-hidden rounded-3xl border" style="border-color: var(--border); background: var(--surface-strong);">
                            <template x-if="previewData">
                                <img :src="previewData" alt="Hasil foto HM" class="aspect-[4/5] w-full object-cover">
                            </template>
                            <template x-if="!previewData">
                                <div class="grid aspect-[4/5] place-items-center p-6 text-center text-sm app-muted">
                                    Setelah Anda ambil foto, hasil ber-watermark akan muncul di sini.
                                </div>
                            </template>
                        </div>
                    </div>

                    <canvas x-ref="canvas" class="hidden"></canvas>

                    <div class="grid gap-3 text-sm md:grid-cols-2">
                        <div class="app-panel-strong rounded-2xl p-4">
                            <div class="app-muted text-xs uppercase tracking-[0.2em]">Koordinat</div>
                            <div class="app-title mt-2 font-medium" x-text="coordinatesText"></div>
                        </div>
                        <div class="app-panel-strong rounded-2xl p-4">
                            <div class="app-muted text-xs uppercase tracking-[0.2em]">Capture info</div>
                            <div class="app-title mt-2 font-medium" x-text="captureInfo"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="app-panel rounded-3xl p-6" x-data="{ open: true }">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="app-muted text-xs uppercase tracking-[0.24em]">Recent Entries</p>
                        <h3 class="app-title mt-2 text-xl font-semibold">Log HM Terakhir</h3>
                    </div>
                    <button type="button" class="app-panel-toggle rounded-xl px-3 py-2 text-xs font-medium" @click="open = !open">
                        <span x-text="open ? 'Minimize' : 'Buka'"></span>
                    </button>
                </div>

                <div class="mt-5 space-y-3" x-show="open">
                    @forelse ($recentLogs as $log)
                        <div class="app-panel-strong rounded-2xl p-4">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="app-title font-medium">{{ $log->unit?->code }} • {{ $log->unit?->name }}</p>
                                    <p class="app-muted mt-1 text-sm">
                                        {{ $log->input_date?->format('d M Y') }} • {{ strtoupper($log->shift) }} • HM {{ number_format((float) $log->hm_start, 2) }} → {{ number_format((float) $log->hm_end, 2) }}
                                    </p>
                                    <p class="app-muted mt-1 text-xs">
                                        {{ $log->workArea?->name ?? 'Tanpa area' }} • {{ $log->photo_path ? 'Foto tersimpan' : 'Tanpa foto' }}
                                    </p>
                                </div>
                                <span class="rounded-full px-3 py-1 text-xs font-medium {{ $log->is_fuel_flagged ? 'app-status-danger' : 'app-status-success' }}">
                                    {{ $log->is_fuel_flagged ? 'Flag Merah' : 'Normal' }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed p-4 text-sm app-muted" style="border-color: var(--border);">
                            Belum ada log HM.
                        </div>
                    @endforelse
                </div>
            </div>
        </section>
    </div>

    @script
    <script>
        Alpine.data('hmCapture', wire => ({
            wire,
            db: null,
            isStreamActive: false,
            watchId: null,
            locating: false,
            openingCamera: false,
            processing: false,
            submitting: false,
            gpsReady: false,
            gpsSupported: 'geolocation' in navigator,
            gpsPermissionState: 'prompt',
            cameraReady: false,
            previewData: null,
            gpsMessage: 'Lokasi belum aktif.',
            gpsHelpText: 'Tekan "Aktifkan Lokasi", lalu izinkan browser membaca lokasi perangkat.',
            cameraMessage: 'Kamera belum dibuka.',
            captureInfo: 'Belum ada capture.',
            coordinatesText: 'Koordinat belum tersedia.',
            syncStatusValue: 'server',
            currentLat: null,
            currentLng: null,
            captureTimestampValue: null,
            queuedCount: 0,
            lastSyncMessage: '',
            async init() {
                this.liveStream = null;
                this.db = await this.openDb();
                await this.refreshQueueCount();
                this.setConnectionState();
                this.observeConnectionChanges();
                await this.detectGpsPermission();

                if (this.gpsPermissionState === 'granted') {
                    this.startPositionWatch();
                    await this.requestGps(false);
                }

                if (!this.gpsSupported) {
                    this.gpsMessage = 'Browser ini tidak mendukung geolocation.';
                    this.gpsHelpText = 'Gunakan browser modern di perangkat yang mengizinkan akses lokasi.';
                }
            },
            observeConnectionChanges() {
                window.addEventListener('online', () => {
                    this.setConnectionState();
                    this.syncQueuedEntries();
                });

                window.addEventListener('offline', () => {
                    this.setConnectionState();
                });
            },
            setConnectionState() {
                this.syncStatusValue = navigator.onLine ? 'server' : 'queued';
                this.wire.set('syncStatus', this.syncStatusValue);
            },
            async detectGpsPermission() {
                if (!navigator.permissions?.query || !this.gpsSupported) {
                    return;
                }

                try {
                    const status = await navigator.permissions.query({ name: 'geolocation' });
                    this.gpsPermissionState = status.state;
                    this.updateGpsHelpText();

                    status.onchange = () => {
                        this.gpsPermissionState = status.state;
                        this.updateGpsHelpText();

                        if (status.state === 'granted') {
                            this.startPositionWatch();
                            this.requestGps(false);
                        }
                    };
                } catch (error) {
                    this.gpsPermissionState = 'prompt';
                }
            },
            updateGpsHelpText() {
                if (!this.gpsSupported) {
                    this.gpsHelpText = 'Gunakan browser yang mendukung geolocation.';
                    return;
                }

                if (this.gpsPermissionState === 'denied') {
                    this.gpsHelpText = 'Izin lokasi diblokir. Aktifkan lagi dari pengaturan browser atau sistem, lalu buka ulang halaman ini.';
                    return;
                }

                if (this.gpsPermissionState === 'granted') {
                    this.gpsHelpText = 'Lokasi sudah diizinkan. Sistem akan memperbarui koordinat secara otomatis.';
                    return;
                }

                this.gpsHelpText = 'Tekan "Aktifkan Lokasi", lalu pilih Allow pada browser.';
            },
            openDb() {
                return new Promise((resolve, reject) => {
                    const request = indexedDB.open('hfs-offline-db', 1);

                    request.onupgradeneeded = event => {
                        const db = event.target.result;

                        if (!db.objectStoreNames.contains('hm_entries')) {
                            db.createObjectStore('hm_entries', { keyPath: 'local_id' });
                        }
                    };

                    request.onsuccess = event => resolve(event.target.result);
                    request.onerror = () => reject(new Error('Gagal membuka IndexedDB.'));
                });
            },
            async enqueueOffline(payload) {
                if (!this.db) {
                    this.db = await this.openDb();
                }

                await new Promise((resolve, reject) => {
                    const tx = this.db.transaction('hm_entries', 'readwrite');
                    tx.objectStore('hm_entries').put(payload);
                    tx.oncomplete = () => resolve();
                    tx.onerror = () => reject(new Error('Gagal menyimpan antrean offline.'));
                });

                this.syncStatusValue = 'queued';
                this.wire.set('syncStatus', 'queued');
                await this.refreshQueueCount();
            },
            async getQueuedEntries() {
                if (!this.db) {
                    this.db = await this.openDb();
                }

                return new Promise((resolve, reject) => {
                    const tx = this.db.transaction('hm_entries', 'readonly');
                    const request = tx.objectStore('hm_entries').getAll();
                    request.onsuccess = () => resolve(request.result || []);
                    request.onerror = () => reject(new Error('Gagal membaca antrean offline.'));
                });
            },
            async removeQueuedEntry(localId) {
                if (!this.db) {
                    this.db = await this.openDb();
                }

                return new Promise((resolve, reject) => {
                    const tx = this.db.transaction('hm_entries', 'readwrite');
                    tx.objectStore('hm_entries').delete(localId);
                    tx.oncomplete = () => resolve();
                    tx.onerror = () => reject(new Error('Gagal menghapus antrean lokal.'));
                });
            },
            async refreshQueueCount() {
                const entries = await this.getQueuedEntries();
                this.queuedCount = entries.length;
            },
            async syncQueuedEntries() {
                if (!navigator.onLine) {
                    return;
                }

                const entries = await this.getQueuedEntries();

                if (!entries.length) {
                    this.queuedCount = 0;
                    return;
                }

                try {
                    const response = await fetch('{{ route('fleet.hm-entry.sync') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify({ entries }),
                    });

                    if (!response.ok) {
                        throw new Error('Sinkronisasi queue gagal.');
                    }

                    const result = await response.json();

                    for (const item of result.results ?? []) {
                        await this.removeQueuedEntry(item.local_id);
                    }

                    await this.refreshQueueCount();
                    this.lastSyncMessage = result.message ?? 'Antrean offline berhasil disinkronkan.';
                } catch (error) {
                    this.lastSyncMessage = error.message || 'Sinkronisasi offline belum berhasil.';
                }
            },
            startPositionWatch() {
                if (!this.gpsSupported || this.watchId !== null) {
                    return;
                }

                this.watchId = navigator.geolocation.watchPosition(
                    position => {
                        this.applyPosition(position);
                        this.gpsMessage = 'Lokasi terkunci.';
                    },
                    error => {
                        this.handleGpsError(error);
                    },
                    { enableHighAccuracy: true, timeout: 15000, maximumAge: 5000 }
                );
            },
            async requestGps(forcePrompt = true) {
                if (!this.gpsSupported) {
                    this.gpsMessage = 'Browser ini tidak mendukung geolocation.';
                    this.updateGpsHelpText();
                    return;
                }

                this.locating = true;
                this.gpsMessage = 'Mencari lokasi...';

                try {
                    await this.refreshGpsBeforeShot(forcePrompt);
                    this.startPositionWatch();
                    this.gpsMessage = 'Lokasi aktif dan siap dipakai.';
                    this.updateGpsHelpText();
                } catch (error) {
                    this.gpsMessage = error.message || 'GPS belum siap.';
                } finally {
                    this.locating = false;
                }
            },
            applyPosition(position) {
                this.gpsReady = true;
                this.currentLat = position.coords.latitude;
                this.currentLng = position.coords.longitude;
                this.wire.set('latitude', this.currentLat);
                this.wire.set('longitude', this.currentLng);
                this.coordinatesText = `${this.currentLat.toFixed(6)}, ${this.currentLng.toFixed(6)}`;
            },
            handleGpsError(error) {
                this.gpsReady = false;

                if (error.code === 1) {
                    this.gpsPermissionState = 'denied';
                    this.gpsMessage = 'Izin lokasi ditolak.';
                } else if (error.code === 2) {
                    this.gpsMessage = 'Lokasi perangkat belum tersedia. Pastikan layanan lokasi sistem aktif.';
                } else if (error.code === 3) {
                    this.gpsMessage = 'Permintaan lokasi terlalu lama. Coba lagi setelah Wi-Fi/GPS stabil.';
                } else {
                    this.gpsMessage = 'GPS belum siap.';
                }

                this.updateGpsHelpText();
            },
            openNativeCamera() {
                if (!this.gpsReady) {
                    this.lastSyncMessage = 'Aktifkan lokasi dulu sebelum membuka kamera.';
                    return;
                }

                this.cameraMessage = 'Membuka kamera bawaan perangkat...';
                this.lastSyncMessage = '';
                this.$refs.cameraInput.value = '';
                this.$refs.cameraInput.click();
            },
            async openLiveCamera() {
                if (!this.gpsReady) {
                    this.lastSyncMessage = 'Aktifkan lokasi dulu sebelum membuka kamera.';
                    return;
                }

                if (!navigator.mediaDevices?.getUserMedia) {
                    this.cameraMessage = 'Browser tidak mendukung kamera live. Gunakan fallback.';
                    return;
                }

                this.openingCamera = true;
                this.cameraMessage = 'Membuka kamera live...';
                this.lastSyncMessage = '';

                try {
                    this.stopLiveCamera();
                    this.previewData = null;
                    this.captureTimestampValue = null;
                    this.liveStream = await this.requestCameraStream();
                    this.isStreamActive = true;

                    await this.attachLiveStream();
                    this.cameraReady = true;
                    this.cameraMessage = 'Kamera live siap.';
                } catch (error) {
                    this.cameraReady = false;
                    this.cameraMessage = 'Kamera live gagal dibuka. Gunakan fallback.';
                    this.lastSyncMessage = error.message || this.cameraMessage;
                } finally {
                    this.openingCamera = false;
                }
            },
            async requestCameraStream() {
                try {
                    return await navigator.mediaDevices.getUserMedia({
                        video: {
                            facingMode: { exact: 'environment' },
                        },
                        audio: false,
                    });
                } catch (error) {
                    console.warn('Kamera belakang tidak ditemukan, beralih ke kamera default...', error);

                    try {
                        return await navigator.mediaDevices.getUserMedia({
                            video: true,
                            audio: false,
                        });
                    } catch (fallbackError) {
                        console.error('Gagal total mengakses kamera:', fallbackError);
                        throw new Error('Kamera tidak dapat diakses. Pastikan izin kamera sudah diberikan.');
                    }
                }
            },
            triggerFileFallback() {
                this.openNativeCamera();
            },
            async attachLiveStream() {
                await this.$nextTick();

                const video = this.$refs.video;

                if (!video || !this.liveStream) {
                    throw new Error('Preview kamera tidak ditemukan.');
                }

                video.srcObject = this.liveStream;

                try {
                    await video.play();
                } catch (error) {
                    console.warn('Pemutaran video tertunda oleh browser:', error);
                }
            },
            stopLiveCamera() {
                if (this.liveStream) {
                    this.liveStream.getTracks().forEach(track => track.stop());
                    this.liveStream = null;
                }

                if (this.$refs.video) {
                    this.$refs.video.pause();
                    this.$refs.video.srcObject = null;
                }

                this.isStreamActive = false;
                this.cameraReady = false;
            },
            async captureFromLiveCamera() {
                if (!this.cameraReady || !this.liveStream) {
                    this.lastSyncMessage = 'Buka kamera live dulu sebelum mengambil foto.';
                    return;
                }

                this.processing = true;
                this.cameraMessage = 'Memproses foto dari kamera live...';

                try {
                    await this.refreshGpsBeforeShot(false);

                    const video = this.$refs.video;
                    const canvas = this.$refs.canvas;
                    const context = canvas.getContext('2d');
                    const width = video.videoWidth || 960;
                    const height = video.videoHeight || 1280;
                    canvas.width = width;
                    canvas.height = height;

                    context.drawImage(video, 0, 0, width, height);

                    await this.finalizeCanvasCapture(canvas);
                    this.stopLiveCamera();
                } catch (error) {
                    this.cameraReady = false;
                    this.cameraMessage = error.message || 'Foto dari kamera live tidak berhasil diproses.';
                    this.captureInfo = this.cameraMessage;
                    this.lastSyncMessage = this.cameraMessage;
                } finally {
                    this.processing = false;
                }
            },
            async handleCameraFile(event) {
                const file = event.target.files?.[0];

                if (!file) {
                    this.cameraReady = false;
                    this.cameraMessage = 'Belum ada foto yang diambil.';
                    return;
                }

                this.processing = true;
                this.cameraMessage = 'Memproses foto dari kamera...';

                try {
                    await this.refreshGpsBeforeShot(false);

                    const image = await this.loadImageFile(file);
                    const canvas = this.$refs.canvas;
                    const context = canvas.getContext('2d');
                    const width = image.naturalWidth || image.width || 960;
                    const height = image.naturalHeight || image.height || 1280;
                    canvas.width = width;
                    canvas.height = height;

                    context.drawImage(image, 0, 0, width, height);
                    await this.finalizeCanvasCapture(canvas);
                    this.cameraMessage = 'Foto berhasil diproses.';
                } catch (error) {
                    this.cameraReady = false;
                    this.cameraMessage = error.message || 'Foto dari kamera tidak berhasil diproses.';
                    this.captureInfo = this.cameraMessage;
                    this.lastSyncMessage = this.cameraMessage;
                } finally {
                    this.processing = false;
                    event.target.value = '';
                }
            },
            async finalizeCanvasCapture(canvas) {
                const context = canvas.getContext('2d');
                const width = canvas.width;
                const height = canvas.height;
                const now = new Date();
                const timestamp = now.toISOString();
                const lat = Number(this.currentLat);
                const lng = Number(this.currentLng);

                this.drawWatermark(context, width, height, timestamp, lat, lng);

                const { blob, dataUrl } = await this.canvasToCompressedWebp(canvas);

                if (blob.size > 500 * 1024) {
                    throw new Error('Ukuran foto masih melebihi 500 KB.');
                }

                this.previewData = dataUrl;
                this.captureTimestampValue = timestamp;
                this.wire.set('capturePayload', dataUrl);
                this.wire.set('captureTimestamp', timestamp);
                this.wire.set('latitude', lat);
                this.wire.set('longitude', lng);
                this.wire.set('syncStatus', this.syncStatusValue);
                this.cameraReady = true;
                this.captureInfo = `${blob.type} • ${(blob.size / 1024).toFixed(0)} KB • ${timestamp}`;
            },
            loadImageFile(file) {
                return new Promise((resolve, reject) => {
                    const reader = new FileReader();
                    reader.onload = () => {
                        const image = new Image();
                        image.onload = () => resolve(image);
                        image.onerror = () => reject(new Error('Foto tidak bisa dibaca.'));
                        image.src = reader.result;
                    };
                    reader.onerror = () => reject(new Error('Foto tidak bisa dibaca.'));
                    reader.readAsDataURL(file);
                });
            },
            async submitEntry() {
                if (this.submitting) {
                    return;
                }

                const payload = this.collectPayload();
                const validationError = await this.validatePayload(payload);

                if (validationError) {
                    this.lastSyncMessage = validationError;
                    return;
                }

                this.submitting = true;
                this.lastSyncMessage = '';

                try {
                    if (!navigator.onLine) {
                        await this.enqueueOffline(payload);
                        this.lastSyncMessage = 'Sinyal tidak tersedia. Input HM masuk antrean lokal dan akan disinkronkan otomatis.';
                        this.resetAfterQueued();
                        return;
                    }

                    this.syncStatusValue = 'server';
                    this.wire.set('syncStatus', 'server');
                    await this.wire.call('save');
                    this.lastSyncMessage = 'Input HM tersimpan ke server.';
                } catch (error) {
                    await this.enqueueOffline(payload);
                    this.lastSyncMessage = 'Server tidak terjangkau. Input HM diamankan di antrean lokal.';
                    this.resetAfterQueued();
                } finally {
                    this.submitting = false;
                }
            },
            collectPayload() {
                const formData = new FormData(this.$refs.form);

                return {
                    local_id: crypto.randomUUID(),
                    unitId: formData.get('unitId'),
                    workAreaId: formData.get('workAreaId'),
                    inputDate: formData.get('inputDate'),
                    shift: formData.get('shift'),
                    hmStart: formData.get('hmStart'),
                    hmEnd: formData.get('hmEnd'),
                    fuelLiters: formData.get('fuelLiters'),
                    notes: formData.get('notes'),
                    capturePayload: this.previewData,
                    captureTimestamp: this.captureTimestampValue,
                    latitude: this.currentLat,
                    longitude: this.currentLng,
                };
            },
            async validatePayload(payload) {
                if (!payload.unitId || !payload.workAreaId || !payload.inputDate) {
                    return 'Unit, area kerja, dan tanggal wajib diisi.';
                }

                if (!payload.hmStart || !payload.hmEnd || Number(payload.hmEnd) <= Number(payload.hmStart)) {
                    return 'HM akhir harus lebih besar dari HM awal.';
                }

                if (!payload.capturePayload || !payload.captureTimestamp) {
                    return 'Ambil foto HM dari kamera dulu sebelum simpan.';
                }

                if (payload.latitude === null || payload.longitude === null) {
                    await this.requestGps(true);

                    if (this.currentLat === null || this.currentLng === null) {
                        return 'Lokasi belum berhasil didapat. Aktifkan izin lokasi di browser atau sistem.';
                    }
                }

                return null;
            },
            async refreshGpsBeforeShot(forcePrompt = false) {
                if (!this.gpsSupported) {
                    throw new Error('Browser ini tidak mendukung geolocation.');
                }

                const attempts = [
                    { enableHighAccuracy: true, timeout: 12000, maximumAge: 0 },
                    { enableHighAccuracy: false, timeout: 15000, maximumAge: 60000 },
                ];

                let lastError = null;

                for (const options of attempts) {
                    try {
                        const position = await this.getPosition(options);
                        this.applyPosition(position);
                        this.gpsMessage = 'Lokasi terkunci.';
                        this.gpsPermissionState = 'granted';
                        return position;
                    } catch (error) {
                        lastError = error;
                        this.handleGpsError(error);
                    }
                }

                if (forcePrompt && lastError?.code === 1) {
                    throw new Error('Izin lokasi ditolak. Aktifkan lagi dari pengaturan browser atau sistem.');
                }

                if (lastError?.code === 2) {
                    throw new Error('Lokasi belum terbaca. Aktifkan Location Services di sistem operasi lalu coba lagi.');
                }

                if (lastError?.code === 3) {
                    throw new Error('Lokasi timeout. Coba lagi setelah koneksi Wi-Fi/GPS stabil.');
                }

                throw new Error('GPS wajib aktif sebelum pengambilan foto.');
            },
            getPosition(options) {
                return new Promise((resolve, reject) => {
                    navigator.geolocation.getCurrentPosition(resolve, reject, options);
                });
            },
            drawWatermark(context, width, height, timestamp, lat, lng) {
                const lines = [
                    'HFS HM FIELD CAPTURE',
                    `TIME ${timestamp}`,
                    `LAT ${lat.toFixed(6)}`,
                    `LNG ${lng.toFixed(6)}`,
                ];

                const padding = 28;
                const lineHeight = 28;
                const boxHeight = padding * 2 + lineHeight * lines.length;

                context.fillStyle = 'rgba(15, 23, 42, 0.74)';
                context.fillRect(20, height - boxHeight - 20, width - 40, boxHeight);

                context.strokeStyle = 'rgba(37, 99, 235, 0.85)';
                context.lineWidth = 2;
                context.strokeRect(20, height - boxHeight - 20, width - 40, boxHeight);

                context.fillStyle = '#ffffff';
                context.font = '700 24px "Instrument Sans", sans-serif';

                lines.forEach((line, index) => {
                    context.fillText(line, 40, height - boxHeight + 18 + (lineHeight * (index + 1)));
                });
            },
            async canvasToCompressedWebp(canvas) {
                let quality = 0.92;
                let blob = await this.toBlob(canvas, quality);

                while (blob.size > 500 * 1024 && quality > 0.35) {
                    quality -= 0.08;
                    blob = await this.toBlob(canvas, quality);
                }

                const dataUrl = await this.blobToDataUrl(blob);

                return { blob, dataUrl };
            },
            toBlob(canvas, quality) {
                return new Promise((resolve, reject) => {
                    canvas.toBlob(blob => {
                        if (!blob) {
                            reject(new Error('Gagal membuat file WebP.'));
                            return;
                        }

                        resolve(blob);
                    }, 'image/webp', quality);
                });
            },
            blobToDataUrl(blob) {
                return new Promise((resolve, reject) => {
                    const reader = new FileReader();
                    reader.onload = () => resolve(reader.result);
                    reader.onerror = () => reject(new Error('Gagal membaca file hasil capture.'));
                    reader.readAsDataURL(blob);
                });
            },
            resetCapture() {
                this.stopLiveCamera();
                this.previewData = null;
                this.captureTimestampValue = null;
                this.captureInfo = 'Belum ada capture.';
                this.currentLat = null;
                this.currentLng = null;
                this.gpsReady = false;
                this.cameraReady = false;
                this.cameraMessage = 'Kamera belum dibuka.';
                this.coordinatesText = 'Koordinat belum tersedia.';
                this.wire.set('capturePayload', null);
                this.wire.set('captureTimestamp', null);
                this.wire.set('latitude', null);
                this.wire.set('longitude', null);
            },
            resetAfterQueued() {
                this.wire.set('unitId', null);
                this.wire.set('workAreaId', null);
                this.wire.set('hmStart', null);
                this.wire.set('hmEnd', null);
                this.wire.set('fuelLiters', null);
                this.wire.set('notes', null);
                this.wire.set('inputDate', new Date().toISOString().slice(0, 10));
                this.wire.set('shift', 'day');
                this.resetCapture();
                this.requestGps(false);
            },
        }))
    </script>
    @endscript
</div>
