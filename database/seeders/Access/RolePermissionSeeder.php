<?php

namespace Database\Seeders\Access;

use App\Models\Access\Permission;
use App\Models\Access\Role;
use App\Support\Access\PermissionCatalog;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (PermissionCatalog::definitions() as $name => $meta) {
            Permission::query()->updateOrCreate(
                ['name' => $name],
                ['label' => $meta['label'], 'group' => $meta['group']]
            );
        }

        $superAdmin = Role::query()->updateOrCreate(
            ['name' => PermissionCatalog::SUPER_ADMIN_ROLE],
            ['label' => 'Super Admin']
        );
        $superAdmin->syncPermissions(PermissionCatalog::names());

        $officer = Role::query()->updateOrCreate(
            ['name' => PermissionCatalog::PROCUREMENT_OFFICER_ROLE],
            ['label' => 'Procurement Officer']
        );
        $officer->syncPermissions(PermissionCatalog::procurementOfficerPermissions());
    }
}
