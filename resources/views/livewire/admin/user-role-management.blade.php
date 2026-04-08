<div class="space-y-8">
    @if (session('status'))
        <div class="rounded-2xl border border-emerald-400/20 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-100">
            {{ session('status') }}
        </div>
    @endif

    <div class="grid gap-8 xl:grid-cols-[1.2fr_0.8fr]">
        <section class="rounded-3xl border border-white/10 bg-slate-900/70 p-6 backdrop-blur">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-cyan-300">Owner Control</p>
                    <h2 class="mt-2 text-2xl font-semibold text-white">User & Direct Permission</h2>
                    <p class="mt-2 text-sm text-slate-400">Role bisa ditambah ke user, lalu permission spesifik dapat dioverride langsung per user bila diperlukan.</p>
                </div>
                <button wire:click="resetUserForm" type="button" class="rounded-full border border-white/10 px-4 py-2 text-sm text-slate-200 hover:bg-white/5">
                    User Baru
                </button>
            </div>

            <form wire:submit="saveUser" class="mt-6 grid gap-5">
                <div class="grid gap-5 md:grid-cols-2">
                    <label class="grid gap-2 text-sm text-slate-300">
                        <span>Nama</span>
                        <input wire:model="userName" type="text" class="rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white focus:border-cyan-400 focus:outline-none">
                        @error('userName') <span class="text-xs text-rose-300">{{ $message }}</span> @enderror
                    </label>

                    <label class="grid gap-2 text-sm text-slate-300">
                        <span>Email</span>
                        <input wire:model="userEmail" type="email" class="rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white focus:border-cyan-400 focus:outline-none">
                        @error('userEmail') <span class="text-xs text-rose-300">{{ $message }}</span> @enderror
                    </label>

                    <label class="grid gap-2 text-sm text-slate-300">
                        <span>ID Karyawan</span>
                        <input wire:model="userEmployeeId" type="text" class="rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white focus:border-cyan-400 focus:outline-none">
                        @error('userEmployeeId') <span class="text-xs text-rose-300">{{ $message }}</span> @enderror
                    </label>

                    <label class="grid gap-2 text-sm text-slate-300">
                        <span>Telepon</span>
                        <input wire:model="userPhone" type="text" class="rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white focus:border-cyan-400 focus:outline-none">
                    </label>

                    <label class="grid gap-2 text-sm text-slate-300">
                        <span>Site</span>
                        <select wire:model="userSiteId" class="rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white focus:border-cyan-400 focus:outline-none">
                            <option value="">Pilih site</option>
                            @foreach ($sites as $site)
                                <option value="{{ $site->id }}">{{ $site->name }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="grid gap-2 text-sm text-slate-300">
                        <span>Password {{ $editingUserId ? '(kosongkan jika tidak diubah)' : '' }}</span>
                        <input wire:model="userPassword" type="password" class="rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white focus:border-cyan-400 focus:outline-none">
                        @error('userPassword') <span class="text-xs text-rose-300">{{ $message }}</span> @enderror
                    </label>
                </div>

                <label class="inline-flex items-center gap-3 text-sm text-slate-200">
                    <input wire:model="userIsActive" type="checkbox" class="rounded border-white/10 bg-slate-950 text-cyan-400">
                    User aktif
                </label>

                <div class="grid gap-5 lg:grid-cols-2">
                    <div>
                        <p class="mb-3 text-sm font-medium text-white">Role</p>
                        <div class="grid gap-2">
                            @foreach ($roles as $role)
                                <label class="inline-flex items-center gap-3 rounded-2xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-slate-200">
                                    <input wire:model="selectedRoles" value="{{ $role->name }}" type="checkbox" class="rounded border-white/10 bg-slate-950 text-cyan-400">
                                    <span>{{ $role->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <p class="mb-3 text-sm font-medium text-white">Direct Permission</p>
                        <input wire:model.live.debounce.250ms="permissionSearch" type="text" placeholder="Cari permission..." class="mb-3 w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-sm text-white focus:border-cyan-400 focus:outline-none">
                        <div class="grid max-h-[32rem] gap-4 overflow-y-auto pr-1">
                            @foreach ($permissionGroups as $group)
                                <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                                    <div class="mb-3">
                                        <p class="font-medium text-white">{{ $group['label'] }}</p>
                                        <p class="mt-1 text-xs text-slate-400">{{ $group['description'] }}</p>
                                    </div>

                                    <div class="grid gap-2">
                                        @foreach ($group['permissions'] as $permission)
                                            <label class="inline-flex items-start gap-3 rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-slate-200">
                                                <input wire:model="selectedPermissions" value="{{ $permission['name'] }}" type="checkbox" class="mt-1 rounded border-white/10 bg-slate-950 text-cyan-400">
                                                <span>
                                                    <span class="block font-medium text-white">{{ $permission['label'] }}</span>
                                                    <span class="block text-xs text-slate-400">{{ $permission['name'] }}</span>
                                                </span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="rounded-full bg-cyan-400 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-cyan-300">
                        Simpan User
                    </button>
                </div>
            </form>
        </section>

        <section class="rounded-3xl border border-white/10 bg-slate-900/70 p-6 backdrop-blur">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-xs uppercase tracking-[0.3em] text-amber-300">Dynamic RBAC</p>
                    <h2 class="mt-2 text-2xl font-semibold text-white">Role Matrix</h2>
                    <p class="mt-2 text-sm text-slate-400">Owner dapat membuat role baru dan mencentang akses modul serta aksi tanpa hardcode.</p>
                </div>
                <button wire:click="resetRoleForm" type="button" class="rounded-full border border-white/10 px-4 py-2 text-sm text-slate-200 hover:bg-white/5">
                    Role Baru
                </button>
            </div>

            <form wire:submit="saveRole" class="mt-6 grid gap-5">
                <label class="grid gap-2 text-sm text-slate-300">
                    <span>Nama Role</span>
                    <input wire:model="roleName" type="text" class="rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-white focus:border-cyan-400 focus:outline-none">
                    @error('roleName') <span class="text-xs text-rose-300">{{ $message }}</span> @enderror
                </label>

                <div>
                    <p class="mb-3 text-sm font-medium text-white">Permission untuk role ini</p>
                    <input wire:model.live.debounce.250ms="permissionSearch" type="text" placeholder="Cari permission..." class="mb-3 w-full rounded-2xl border border-white/10 bg-slate-950/70 px-4 py-3 text-sm text-white focus:border-cyan-400 focus:outline-none">
                    <div class="grid max-h-[32rem] gap-4 overflow-y-auto pr-1">
                        @foreach ($permissionGroups as $group)
                            <div class="rounded-2xl border border-white/10 bg-white/5 p-4">
                                <div class="mb-3">
                                    <p class="font-medium text-white">{{ $group['label'] }}</p>
                                    <p class="mt-1 text-xs text-slate-400">{{ $group['description'] }}</p>
                                </div>

                                <div class="grid gap-2">
                                    @foreach ($group['permissions'] as $permission)
                                        <label class="inline-flex items-start gap-3 rounded-2xl border border-white/10 bg-slate-950/50 px-4 py-3 text-sm text-slate-200">
                                            <input wire:model="rolePermissions" value="{{ $permission['name'] }}" type="checkbox" class="mt-1 rounded border-white/10 bg-slate-950 text-cyan-400">
                                            <span>
                                                <span class="block font-medium text-white">{{ $permission['label'] }}</span>
                                                <span class="block text-xs text-slate-400">{{ $permission['name'] }}</span>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="rounded-full bg-amber-300 px-5 py-3 text-sm font-semibold text-slate-950 transition hover:bg-amber-200">
                        Simpan Role
                    </button>
                </div>
            </form>
        </section>
    </div>

    <div class="grid gap-8 xl:grid-cols-2">
        <section class="rounded-3xl border border-white/10 bg-slate-900/70 p-6 backdrop-blur">
            <h3 class="text-lg font-semibold text-white">Daftar User</h3>
            <p class="mt-2 text-sm text-slate-400">Klik user untuk edit role, site, status aktif, dan permission khusus.</p>
            <div class="mt-5 space-y-3">
                @foreach ($users as $user)
                    <button wire:click="editUser({{ $user->id }})" type="button" class="w-full rounded-2xl border border-white/10 bg-white/5 p-4 text-left transition hover:border-cyan-400/40 hover:bg-cyan-400/10">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="font-medium text-white">{{ $user->name }}</p>
                                <p class="mt-1 text-sm text-slate-300">{{ $user->email }} • {{ $user->site?->name ?? 'Tanpa site' }}</p>
                                <p class="mt-2 text-xs text-slate-400">
                                    Role: {{ $user->roles->pluck('name')->join(', ') ?: 'Belum ada role' }}
                                </p>
                                <p class="mt-1 text-xs text-slate-500">
                                    Direct permission: {{ $user->permissions->pluck('name')->join(', ') ?: 'Tidak ada override' }}
                                </p>
                            </div>
                            <span class="rounded-full px-3 py-1 text-xs {{ $user->is_active ? 'bg-emerald-500/15 text-emerald-200' : 'bg-rose-500/15 text-rose-200' }}">
                                {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>
                    </button>
                @endforeach
            </div>
        </section>

        <section class="rounded-3xl border border-white/10 bg-slate-900/70 p-6 backdrop-blur">
            <h3 class="text-lg font-semibold text-white">Daftar Role</h3>
            <p class="mt-2 text-sm text-slate-400">Klik role untuk edit permission matrix per modul.</p>
            <div class="mt-5 space-y-3">
                @foreach ($roles as $role)
                    <button wire:click="editRole({{ $role->id }})" type="button" class="w-full rounded-2xl border border-white/10 bg-white/5 p-4 text-left transition hover:border-amber-400/40 hover:bg-amber-400/10">
                        <p class="font-medium text-white">{{ $role->name }}</p>
                        <p class="mt-2 text-xs text-slate-400">
                            {{ $role->permissions->pluck('name')->join(', ') ?: 'Belum ada permission' }}
                        </p>
                        <p class="mt-2 text-xs text-slate-500">
                            {{ $role->permissions->count() }} permission aktif
                        </p>
                    </button>
                @endforeach
            </div>
        </section>
    </div>
</div>
