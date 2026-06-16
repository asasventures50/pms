<?php

namespace App\Http\Controllers\Access;

use App\Http\Controllers\Controller;
use App\Http\Requests\Access\StoreRoleRequest;
use App\Http\Requests\Access\UpdateRoleRequest;
use App\Models\Access\Permission;
use App\Models\Access\Role;
use App\Support\Access\PermissionCatalog;
use App\Support\TableSort;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = max(1, min(100, (int) $request->query('per_page', 15)));
        $sort = TableSort::resolve($request, ['label', 'name', 'created_at'], 'label', 'asc');

        $query = Role::query()->withCount(['users', 'permissions']);

        if ($request->filled('q')) {
            $term = '%'.$request->string('q').'%';
            $query->where(function ($q) use ($term) {
                $q->where('label', 'like', $term)
                    ->orWhere('name', 'like', $term);
            });
        }

        $query->orderBy($sort['column'], $sort['direction'])->orderBy('id');

        return view('access.roles.index', [
            'roles' => $query->paginate($perPage)->appends($request->query()),
            'sortColumn' => $sort['column'],
            'sortDirection' => $sort['direction'],
        ]);
    }

    public function create(): View
    {
        return view('access.roles.create', [
            'role' => new Role,
            'permissionGroups' => PermissionCatalog::grouped(),
            'permissionLabels' => Permission::query()->pluck('label', 'name'),
            'selectedPermissions' => old('permissions', []),
        ]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $role = Role::query()->create([
            'name' => $data['name'],
            'label' => $data['label'],
        ]);

        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role created successfully.');
    }

    public function edit(Role $role): View
    {
        $role->load('permissions');

        return view('access.roles.edit', [
            'role' => $role,
            'permissionGroups' => PermissionCatalog::grouped(),
            'permissionLabels' => Permission::query()->pluck('label', 'name'),
            'selectedPermissions' => old('permissions', $role->permissions->pluck('name')->all()),
            'isSystemRole' => $this->isSystemRole($role),
        ]);
    }

    public function update(UpdateRoleRequest $request, Role $role): RedirectResponse
    {
        $data = $request->validated();

        if ($this->isSystemRole($role)) {
            $role->syncPermissions($data['permissions'] ?? []);

            return redirect()
                ->route('roles.index')
                ->with('success', 'Role permissions updated successfully.');
        }

        $role->update([
            'name' => $data['name'],
            'label' => $data['label'],
        ]);

        $role->syncPermissions($data['permissions'] ?? []);

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role updated successfully.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if ($this->isSystemRole($role)) {
            return redirect()
                ->route('roles.index')
                ->with('error', 'System roles cannot be deleted.');
        }

        if ($role->users()->exists()) {
            return redirect()
                ->route('roles.index')
                ->with('error', 'Cannot delete a role that is assigned to users.');
        }

        $role->permissions()->detach();
        $role->delete();

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role deleted successfully.');
    }

    private function isSystemRole(Role $role): bool
    {
        return in_array($role->name, [
            PermissionCatalog::SUPER_ADMIN_ROLE,
            PermissionCatalog::PROCUREMENT_OFFICER_ROLE,
            PermissionCatalog::PR_REQUESTER_ROLE,
        ], true);
    }
}
