<?php

use App\Models\Access\Permission;
use App\Support\Access\PermissionCatalog;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Production-safe: registers new permission rows only.
     */
    public function up(): void
    {
        foreach (PermissionCatalog::definitions() as $name => $meta) {
            Permission::query()->updateOrCreate(
                ['name' => $name],
                ['label' => $meta['label'], 'group' => $meta['group']]
            );
        }
    }

    public function down(): void
    {
        // Permissions cannot be rolled back safely on production.
    }
};
