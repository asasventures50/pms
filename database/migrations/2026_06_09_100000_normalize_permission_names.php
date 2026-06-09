<?php

use App\Models\Access\Permission;
use App\Models\Access\Role;
use App\Support\Access\PermissionCatalog;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        foreach (PermissionCatalog::definitions() as $name => $meta) {
            Permission::query()->updateOrCreate(
                ['name' => $name],
                ['label' => $meta['label'], 'group' => $meta['group']]
            );
        }

        Role::query()->each(function (Role $role): void {
            $legacyNames = DB::table('permission_role')
                ->join('permissions', 'permissions.id', '=', 'permission_role.permission_id')
                ->where('permission_role.role_id', $role->id)
                ->pluck('permissions.name');

            $canonical = $legacyNames
                ->map(fn (string $name) => PermissionCatalog::canonicalName($name))
                ->filter()
                ->unique()
                ->values()
                ->all();

            $role->syncPermissions($canonical);
        });

        Permission::query()
            ->whereNotIn('name', PermissionCatalog::names())
            ->delete();

        $superAdmin = Role::query()->where('name', PermissionCatalog::SUPER_ADMIN_ROLE)->first();
        $superAdmin?->syncPermissions(PermissionCatalog::names());

        $officer = Role::query()->where('name', PermissionCatalog::PROCUREMENT_OFFICER_ROLE)->first();
        $officer?->syncPermissions(PermissionCatalog::procurementOfficerPermissions());
    }

    public function down(): void
    {
        // Legacy permission names cannot be restored reliably.
    }
};
