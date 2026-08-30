<?php

namespace App\Http\Resources\Api\V1\Auth;

use App\Models\Access\Role;
use App\Models\User;
use App\Support\Access\PermissionCatalog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin User
 */
class UserResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $roles = $this->relationLoaded('roles')
            ? $this->roles
            : $this->roles()->with('permissions')->get();

        $permissionNames = $this->isSuperAdmin()
            ? collect(PermissionCatalog::names())
            : $roles
                ->flatMap(fn (Role $role) => $role->permissions->pluck('name'))
                ->map(fn (string $name) => PermissionCatalog::canonicalName($name))
                ->filter()
                ->unique()
                ->values();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'department' => $this->department,
            'currency_code' => $this->currency_code,
            'is_super_admin' => $this->isSuperAdmin(),
            'roles' => $roles->pluck('name')->values(),
            'permissions' => $permissionNames,
        ];
    }
}
