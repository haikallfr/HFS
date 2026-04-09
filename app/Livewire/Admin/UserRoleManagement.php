<?php

namespace App\Livewire\Admin;

use App\Models\Site;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserRoleManagement extends Component
{
    public ?int $editingUserId = null;
    public ?int $editingRoleId = null;
    public string $userName = '';
    public string $userEmail = '';
    public string $userEmployeeId = '';
    public string $userPhone = '';
    public ?int $userSiteId = null;
    public bool $userIsActive = true;
    public string $userPassword = '';
    public array $selectedRoles = [];
    public array $selectedPermissions = [];
    public string $roleName = '';
    public array $rolePermissions = [];
    public string $permissionSearch = '';

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('rbac.manage'), 403);
    }

    public function editUser(int $userId): void
    {
        $user = User::with(['roles', 'permissions'])->findOrFail($userId);

        $this->editingUserId = $user->id;
        $this->userName = $user->name;
        $this->userEmail = $user->email;
        $this->userEmployeeId = $user->employee_id ?? '';
        $this->userPhone = $user->phone ?? '';
        $this->userSiteId = $user->site_id;
        $this->userIsActive = (bool) $user->is_active;
        $this->userPassword = '';
        $this->selectedRoles = $user->roles->pluck('name')->all();
        $this->selectedPermissions = $user->permissions->pluck('name')->all();

        $this->dispatch('open-user-form');
    }

    public function createUser(): void
    {
        $this->resetUserForm();
        $this->dispatch('open-user-form');
    }

    public function resetUserForm(): void
    {
        $this->reset([
            'editingUserId',
            'userName',
            'userEmail',
            'userEmployeeId',
            'userPhone',
            'userSiteId',
            'userPassword',
            'selectedRoles',
            'selectedPermissions',
        ]);

        $this->userIsActive = true;
        $this->dispatch('close-user-form');
    }

    public function saveUser(): void
    {
        $validated = $this->validate([
            'userName' => ['required', 'string', 'max:255'],
            'userEmail' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->editingUserId)],
            'userEmployeeId' => ['nullable', 'string', 'max:255', Rule::unique('users', 'employee_id')->ignore($this->editingUserId)],
            'userPhone' => ['nullable', 'string', 'max:255'],
            'userSiteId' => ['nullable', 'exists:sites,id'],
            'userIsActive' => ['boolean'],
            'selectedRoles' => ['array'],
            'selectedRoles.*' => ['exists:roles,name'],
            'selectedPermissions' => ['array'],
            'selectedPermissions.*' => ['exists:permissions,name'],
            'userPassword' => [$this->editingUserId ? 'nullable' : 'required', 'string', 'min:8'],
        ]);

        $user = User::query()->updateOrCreate(
            ['id' => $this->editingUserId],
            [
                'name' => $validated['userName'],
                'email' => $validated['userEmail'],
                'employee_id' => $validated['userEmployeeId'] ?: null,
                'phone' => $validated['userPhone'] ?: null,
                'site_id' => $validated['userSiteId'],
                'is_active' => $validated['userIsActive'],
                ...($validated['userPassword'] ? ['password' => Hash::make($validated['userPassword'])] : []),
            ]
        );

        $user->syncRoles($validated['selectedRoles']);
        $user->syncPermissions($validated['selectedPermissions']);

        $this->resetUserForm();
        session()->flash('status', 'User berhasil disimpan.');
    }

    public function editRole(int $roleId): void
    {
        $role = Role::with('permissions')->findOrFail($roleId);

        $this->editingRoleId = $role->id;
        $this->roleName = $role->name;
        $this->rolePermissions = $role->permissions->pluck('name')->all();

        $this->dispatch('open-role-form');
    }

    public function createRole(): void
    {
        $this->resetRoleForm();
        $this->dispatch('open-role-form');
    }

    public function resetRoleForm(): void
    {
        $this->reset([
            'editingRoleId',
            'roleName',
            'rolePermissions',
        ]);

        $this->dispatch('close-role-form');
    }

    public function saveRole(): void
    {
        $validated = $this->validate([
            'roleName' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->ignore($this->editingRoleId)],
            'rolePermissions' => ['array'],
            'rolePermissions.*' => ['exists:permissions,name'],
        ]);

        $role = Role::query()->updateOrCreate(
            ['id' => $this->editingRoleId],
            ['name' => $validated['roleName'], 'guard_name' => 'web']
        );

        $role->syncPermissions($validated['rolePermissions']);

        $this->resetRoleForm();
        session()->flash('status', 'Role berhasil disimpan.');
    }

    public function deleteUser(int $userId): void
    {
        $user = User::findOrFail($userId);

        abort_if(auth()->id() === $user->id, 422, 'User yang sedang login tidak bisa dihapus.');

        $user->syncRoles([]);
        $user->syncPermissions([]);
        $user->delete();

        if ($this->editingUserId === $userId) {
            $this->resetUserForm();
        }

        session()->flash('status', 'User berhasil dihapus.');
    }

    public function deleteRole(int $roleId): void
    {
        $role = Role::findOrFail($roleId);

        abort_if($role->name === 'Owner', 422, 'Role Owner tidak bisa dihapus.');
        abort_if(User::role($role->name)->exists(), 422, 'Role masih dipakai user dan belum bisa dihapus.');

        $role->syncPermissions([]);
        $role->delete();

        if ($this->editingRoleId === $roleId) {
            $this->resetRoleForm();
        }

        session()->flash('status', 'Role berhasil dihapus.');
    }

    public function render()
    {
        $permissionGroups = collect(config('hfs.permission_groups'))
            ->map(function (array $group): array {
                $permissions = collect($group['permissions'])
                    ->map(function (string $label, string $permission) {
                        return [
                            'name' => $permission,
                            'label' => $label,
                        ];
                    })
                    ->filter(function (array $permission): bool {
                        if ($this->permissionSearch === '') {
                            return true;
                        }

                        $needle = str($this->permissionSearch)->lower()->value();

                        return str($permission['name'])->lower()->contains($needle)
                            || str($permission['label'])->lower()->contains($needle);
                    })
                    ->values()
                    ->all();

                return [
                    ...$group,
                    'permissions' => $permissions,
                ];
            })
            ->filter(fn (array $group): bool => count($group['permissions']) > 0)
            ->values();

        return view('livewire.admin.user-role-management', [
            'users' => User::with(['roles', 'permissions', 'site'])->orderBy('name')->get(),
            'roles' => Role::with('permissions')->orderBy('name')->get(),
            'permissions' => Permission::query()->orderBy('name')->get(),
            'sites' => Site::query()->orderBy('name')->get(),
            'permissionGroups' => $permissionGroups,
        ]);
    }
}
