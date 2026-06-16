<?php

use App\Models\Access\Permission;
use App\Models\Access\Role;
use App\Support\Access\PermissionCatalog;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Production-safe: registers new permission rows and the pr-requester role only.
     * Does not reset permissions on existing roles — assign via Roles UI on production.
     */
    public function up(): void
    {
        foreach (PermissionCatalog::definitions() as $name => $meta) {
            Permission::query()->updateOrCreate(
                ['name' => $name],
                ['label' => $meta['label'], 'group' => $meta['group']]
            );
        }

        Role::query()->firstOrCreate(
            ['name' => PermissionCatalog::PR_REQUESTER_ROLE],
            ['label' => 'PR Requester']
        );
    }

    public function down(): void
    {
        // Permissions and roles cannot be rolled back safely on production.
    }
};
