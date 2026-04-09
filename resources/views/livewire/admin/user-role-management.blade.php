<div
    class="space-y-6"
    x-data="{ userFormOpen: false, roleFormOpen: false, userListOpen: true, roleListOpen: true }"
    x-on:open-user-form.window="userFormOpen = true"
    x-on:close-user-form.window="userFormOpen = false"
    x-on:open-role-form.window="roleFormOpen = true"
    x-on:close-role-form.window="roleFormOpen = false"
>
    @if (session('status'))
        <div class="app-status-success rounded-2xl px-4 py-3 text-sm">
            {{ session('status') }}
        </div>
    @endif

    <section class="grid gap-4 lg:grid-cols-2">
        <div class="app-panel rounded-[32px] p-6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="app-muted text-xs uppercase tracking-[0.2em]">User</p>
                    <h2 class="app-title mt-2 text-2xl font-semibold">User Baru</h2>
                    <p class="app-muted mt-2 text-sm">Form disembunyikan sampai Anda benar-benar ingin menambah atau mengedit user.</p>
                </div>
                <button wire:click="createUser" type="button" class="app-button rounded-full px-5 py-3 text-sm font-semibold">
                    Tambah User Baru
                </button>
            </div>
        </div>

        <div class="app-panel rounded-[32px] p-6">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="app-muted text-xs uppercase tracking-[0.2em]">Role</p>
                    <h2 class="app-title mt-2 text-2xl font-semibold">Role Baru</h2>
                    <p class="app-muted mt-2 text-sm">Form role muncul saat Anda ingin membuat baru atau mengubah role yang sudah ada.</p>
                </div>
                <button wire:click="createRole" type="button" class="app-button rounded-full px-5 py-3 text-sm font-semibold">
                    Tambah Role Baru
                </button>
            </div>
        </div>
    </section>

    <section class="grid gap-4 lg:grid-cols-2">
        <div class="app-panel rounded-[32px] p-6" x-show="userFormOpen" x-cloak>
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="app-muted text-xs uppercase tracking-[0.2em]">User</p>
                    <h2 class="app-title mt-2 text-2xl font-semibold">{{ $editingUserId ? 'Edit User' : 'User Baru' }}</h2>
                    <p class="app-muted mt-2 text-sm">Kelola akun, site, status aktif, dan permission tambahan.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button wire:click="resetUserForm" type="button" class="app-panel-toggle rounded-full px-4 py-2 text-sm font-medium">
                        Reset
                    </button>
                    <button type="button" class="app-panel-toggle rounded-full px-4 py-2 text-sm font-medium" @click="userFormOpen = !userFormOpen">
                        <span x-text="userFormOpen ? 'Minimize' : 'Buka'"></span>
                    </button>
                </div>
            </div>

            <form wire:submit="saveUser" class="mt-6 space-y-5" x-show="userFormOpen">
                <div class="grid gap-4 md:grid-cols-2">
                    <label class="grid gap-2 text-sm">
                        <span class="app-title">Nama</span>
                        <input wire:model="userName" type="text" class="app-input rounded-2xl px-4 py-3">
                        @error('userName') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </label>

                    <label class="grid gap-2 text-sm">
                        <span class="app-title">Email</span>
                        <input wire:model="userEmail" type="email" class="app-input rounded-2xl px-4 py-3">
                        @error('userEmail') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </label>

                    <label class="grid gap-2 text-sm">
                        <span class="app-title">ID Karyawan</span>
                        <input wire:model="userEmployeeId" type="text" class="app-input rounded-2xl px-4 py-3">
                        @error('userEmployeeId') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </label>

                    <label class="grid gap-2 text-sm">
                        <span class="app-title">Telepon</span>
                        <input wire:model="userPhone" type="text" class="app-input rounded-2xl px-4 py-3">
                    </label>

                    <label class="grid gap-2 text-sm">
                        <span class="app-title">Site</span>
                        <select wire:model="userSiteId" class="app-input rounded-2xl px-4 py-3">
                            <option value="">Pilih site</option>
                            @foreach ($sites as $site)
                                <option value="{{ $site->id }}">{{ $site->name }}</option>
                            @endforeach
                        </select>
                    </label>

                    <label class="grid gap-2 text-sm">
                        <span class="app-title">Password {{ $editingUserId ? '(opsional)' : '' }}</span>
                        <input wire:model="userPassword" type="password" class="app-input rounded-2xl px-4 py-3">
                        @error('userPassword') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </label>
                </div>

                <label class="inline-flex items-center gap-3 text-sm app-title">
                    <input wire:model="userIsActive" type="checkbox" class="app-check">
                    User aktif
                </label>

                <div class="grid gap-5 xl:grid-cols-2">
                    <div>
                        <p class="mb-3 text-sm font-semibold app-title">Role</p>
                        <div class="grid gap-2">
                            @foreach ($roles as $role)
                                @php($roleSelected = in_array($role->name, $selectedRoles, true))
                                <label
                                    class="inline-flex items-center gap-3 rounded-2xl px-4 py-3 text-sm transition"
                                    style="{{ $roleSelected ? 'background: var(--accent-soft); border: 1px solid color-mix(in srgb, var(--accent) 45%, var(--border));' : 'background: var(--surface-muted); border: 1px solid transparent;' }}"
                                >
                                    <input wire:model="selectedRoles" value="{{ $role->name }}" type="checkbox" class="app-check">
                                    <span class="app-title">{{ $role->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <div class="mb-3 flex items-center justify-between gap-3">
                            <p class="text-sm font-semibold app-title">Direct Permission</p>
                            <input wire:model.live.debounce.250ms="permissionSearch" type="text" placeholder="Cari..." class="app-input w-40 rounded-full px-4 py-2 text-sm">
                        </div>

                        <div class="grid max-h-[26rem] gap-3 overflow-y-auto pr-1">
                            @foreach ($permissionGroups as $group)
                                <div class="rounded-2xl px-4 py-4" style="background: var(--surface-muted);">
                                    <p class="font-semibold app-title">{{ $group['label'] }}</p>
                                    <p class="mt-1 text-xs app-muted">{{ $group['description'] }}</p>

                                    <div class="mt-3 grid gap-2">
                                        @foreach ($group['permissions'] as $permission)
                                            @php($permissionSelected = in_array($permission['name'], $selectedPermissions, true))
                                            <label
                                                class="inline-flex items-start gap-3 rounded-2xl px-4 py-3 text-sm transition"
                                                style="{{ $permissionSelected ? 'background: var(--accent-soft); border: 1px solid color-mix(in srgb, var(--accent) 45%, var(--border));' : 'background: var(--surface-strong); border: 1px solid var(--border);' }}"
                                            >
                                                <input wire:model="selectedPermissions" value="{{ $permission['name'] }}" type="checkbox" class="app-check mt-1">
                                                <span>
                                                    <span class="block font-medium app-title">{{ $permission['label'] }}</span>
                                                    <span class="block text-xs app-muted">{{ $permission['name'] }}</span>
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
                    <button type="submit" class="app-button rounded-full px-5 py-3 text-sm font-semibold">
                        Simpan User
                    </button>
                </div>
            </form>
        </div>

        <div class="app-panel rounded-[32px] p-6" x-show="roleFormOpen" x-cloak>
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="app-muted text-xs uppercase tracking-[0.2em]">Role</p>
                    <h2 class="app-title mt-2 text-2xl font-semibold">{{ $editingRoleId ? 'Edit Role' : 'Role Baru' }}</h2>
                    <p class="app-muted mt-2 text-sm">Atur permission role tanpa hardcode menu atau aksi.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button wire:click="resetRoleForm" type="button" class="app-panel-toggle rounded-full px-4 py-2 text-sm font-medium">
                        Reset
                    </button>
                    <button type="button" class="app-panel-toggle rounded-full px-4 py-2 text-sm font-medium" @click="roleFormOpen = !roleFormOpen">
                        <span x-text="roleFormOpen ? 'Minimize' : 'Buka'"></span>
                    </button>
                </div>
            </div>

            <form wire:submit="saveRole" class="mt-6 space-y-5" x-show="roleFormOpen">
                <label class="grid gap-2 text-sm">
                    <span class="app-title">Nama Role</span>
                    <input wire:model="roleName" type="text" class="app-input rounded-2xl px-4 py-3">
                    @error('roleName') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </label>

                <div>
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <p class="text-sm font-semibold app-title">Permission Matrix</p>
                        <input wire:model.live.debounce.250ms="permissionSearch" type="text" placeholder="Cari..." class="app-input w-40 rounded-full px-4 py-2 text-sm">
                    </div>

                    <div class="grid max-h-[36rem] gap-3 overflow-y-auto pr-1">
                        @foreach ($permissionGroups as $group)
                            <div class="rounded-2xl px-4 py-4" style="background: var(--surface-muted);">
                                <p class="font-semibold app-title">{{ $group['label'] }}</p>
                                <p class="mt-1 text-xs app-muted">{{ $group['description'] }}</p>

                                <div class="mt-3 grid gap-2">
                                    @foreach ($group['permissions'] as $permission)
                                        @php($rolePermissionSelected = in_array($permission['name'], $rolePermissions, true))
                                        <label
                                            class="inline-flex items-start gap-3 rounded-2xl px-4 py-3 text-sm transition"
                                            style="{{ $rolePermissionSelected ? 'background: var(--accent-soft); border: 1px solid color-mix(in srgb, var(--accent) 45%, var(--border));' : 'background: var(--surface-strong); border: 1px solid var(--border);' }}"
                                        >
                                            <input wire:model="rolePermissions" value="{{ $permission['name'] }}" type="checkbox" class="app-check mt-1">
                                            <span>
                                                <span class="block font-medium app-title">{{ $permission['label'] }}</span>
                                                <span class="block text-xs app-muted">{{ $permission['name'] }}</span>
                                            </span>
                                        </label>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-between">
                    @if ($editingRoleId)
                        <button
                            wire:click="deleteRole({{ $editingRoleId }})"
                            wire:confirm="Hapus role ini?"
                            type="button"
                            class="rounded-full px-5 py-3 text-sm font-semibold"
                            style="background: var(--danger); color: white;"
                        >
                            Hapus Role
                        </button>
                    @else
                        <span></span>
                    @endif

                    <button type="submit" class="app-button rounded-full px-5 py-3 text-sm font-semibold">
                        Simpan Role
                    </button>
                </div>
            </form>
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-2">
        <div class="app-panel rounded-[32px] p-6">
            <div class="mb-4 flex items-start justify-between gap-4">
                <div>
                    <p class="app-muted text-xs uppercase tracking-[0.2em]">Users</p>
                    <h3 class="app-title mt-2 text-xl font-semibold">Daftar User</h3>
                </div>
                <button type="button" class="app-panel-toggle rounded-full px-4 py-2 text-sm font-medium" @click="userListOpen = !userListOpen">
                    <span x-text="userListOpen ? 'Minimize' : 'Buka'"></span>
                </button>
            </div>

            <div class="space-y-3" x-show="userListOpen">
                @foreach ($users as $user)
                    <div class="rounded-2xl px-4 py-4" style="background: var(--surface-muted); border: 1px solid var(--border);">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <p class="font-semibold app-title">{{ $user->name }}</p>
                                <p class="mt-1 text-sm app-muted">{{ $user->email }}</p>
                                <p class="mt-2 text-xs app-muted">{{ $user->site?->name ?? 'Tanpa site' }}</p>
                                <p class="mt-1 text-xs app-muted">Role: {{ $user->roles->pluck('name')->join(', ') ?: 'Belum ada role' }}</p>
                            </div>
                            <span class="rounded-full px-3 py-1 text-xs font-medium {{ $user->is_active ? 'app-status-success' : 'app-status-danger' }}">
                                {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <button wire:click="editUser({{ $user->id }})" type="button" class="app-button-secondary rounded-full px-4 py-2 text-sm">
                                Edit
                            </button>
                            <button
                                wire:click="deleteUser({{ $user->id }})"
                                wire:confirm="Hapus user ini?"
                                type="button"
                                class="rounded-full px-4 py-2 text-sm font-semibold"
                                style="background: var(--danger); color: white;"
                            >
                                Hapus
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="app-panel rounded-[32px] p-6">
            <div class="mb-4 flex items-start justify-between gap-4">
                <div>
                    <p class="app-muted text-xs uppercase tracking-[0.2em]">Roles</p>
                    <h3 class="app-title mt-2 text-xl font-semibold">Daftar Role</h3>
                </div>
                <button type="button" class="app-panel-toggle rounded-full px-4 py-2 text-sm font-medium" @click="roleListOpen = !roleListOpen">
                    <span x-text="roleListOpen ? 'Minimize' : 'Buka'"></span>
                </button>
            </div>

            <div class="space-y-3" x-show="roleListOpen">
                @foreach ($roles as $role)
                    <div class="rounded-2xl px-4 py-4" style="background: var(--surface-muted); border: 1px solid var(--border);">
                        <p class="font-semibold app-title">{{ $role->name }}</p>
                        <p class="mt-2 text-xs app-muted">{{ $role->permissions->count() }} permission aktif</p>
                        <p class="mt-1 text-xs app-muted">{{ $role->permissions->pluck('name')->join(', ') ?: 'Belum ada permission' }}</p>

                        <div class="mt-4 flex flex-wrap gap-2">
                            <button wire:click="editRole({{ $role->id }})" type="button" class="app-button-secondary rounded-full px-4 py-2 text-sm">
                                Edit
                            </button>
                            @if ($role->name !== 'Owner')
                                <button
                                    wire:click="deleteRole({{ $role->id }})"
                                    wire:confirm="Hapus role ini?"
                                    type="button"
                                    class="rounded-full px-4 py-2 text-sm font-semibold"
                                    style="background: var(--danger); color: white;"
                                >
                                    Hapus
                                </button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
</div>
