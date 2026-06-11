<?php

namespace App\Models\Concerns;

use App\Models\Access\Permission;
use App\Models\Access\Role;
use App\Models\Procurement\ProcurementRequests\ProcurementRequest;
use App\Support\Access\PermissionCatalog;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

trait HasRoles
{
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function hasRole(string $roleName): bool
    {
        if ($this->relationLoaded('roles')) {
            return $this->getRelation('roles')->contains('name', $roleName);
        }

        return $this->roles()->where('name', $roleName)->exists();
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(PermissionCatalog::SUPER_ADMIN_ROLE);
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        $granted = $this->resolvePermissionNames();

        if ($granted->contains($permission)) {
            return true;
        }

        return self::permissionImpliedByGrant($granted, $permission);
    }

    public function canViewAllProcurementRequests(): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        return $this->resolvePermissionNames()->contains('procurement-requests.view');
    }

    public function scopesProcurementRequestsToOwn(): bool
    {
        if ($this->canViewAllProcurementRequests()) {
            return false;
        }

        $granted = $this->resolvePermissionNames();

        return $granted->contains('procurement-requests.view-own')
            || $granted->contains('procurement-requests.create');
    }

    public function canViewProcurementRequest(ProcurementRequest $procurementRequest): bool
    {
        if ($this->isSuperAdmin() || $this->canViewAllProcurementRequests()) {
            return true;
        }

        if ($this->resolvePermissionNames()->contains('procurement-requests.update')) {
            return true;
        }

        if ($this->scopesProcurementRequestsToOwn()) {
            return (int) $procurementRequest->created_by === (int) $this->id;
        }

        return $this->hasPermission('procurement-requests.view')
            || $this->hasPermission('procurement-requests.view-own');
    }

    public function syncRoles(array $roleNames): void
    {
        $ids = Role::query()
            ->whereIn('name', $roleNames)
            ->pluck('id');

        $this->roles()->sync($ids);
    }

    /**
     * @return Collection<int, string>
     */
    protected function resolvePermissionNames(): Collection
    {
        if ($this->relationLoaded('roles')) {
            $roles = $this->getRelation('roles');
        } else {
            $roles = $this->roles()->with('permissions')->get();
        }

        return $roles
            ->flatMap(fn (Role $role) => $role->permissions->pluck('name'))
            ->map(fn (string $name) => PermissionCatalog::canonicalName($name))
            ->filter()
            ->unique()
            ->values();
    }

    /**
     * Higher actions imply lower ones on the same resource (update → create → view).
     *
     * @param  Collection<int, string>  $granted
     */
    private static function permissionImpliedByGrant(Collection $granted, string $required): bool
    {
        if (preg_match('/^(.+)\.view$/', $required, $viewMatches)) {
            if ($granted->contains("{$viewMatches[1]}.view-own")) {
                return true;
            }
        }

        if (! preg_match('/^(.+)\.(view|create|update)$/', $required, $matches)) {
            return false;
        }

        [, $resource, $action] = $matches;
        $levels = ['view' => 1, 'create' => 2, 'update' => 3];
        $requiredLevel = $levels[$action];

        foreach ($levels as $grantedAction => $grantedLevel) {
            if ($grantedLevel >= $requiredLevel && $granted->contains("{$resource}.{$grantedAction}")) {
                return true;
            }
        }

        return false;
    }
}
