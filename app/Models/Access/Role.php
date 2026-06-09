<?php

namespace App\Models\Access;

use App\Models\User;
use App\Support\Access\PermissionCatalog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'label',
    ];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function syncPermissions(array $permissionNames): void
    {
        $definitions = PermissionCatalog::definitions();

        foreach ($permissionNames as $name) {
            if (! isset($definitions[$name])) {
                continue;
            }

            $meta = $definitions[$name];

            Permission::query()->updateOrCreate(
                ['name' => $name],
                ['label' => $meta['label'], 'group' => $meta['group']]
            );
        }

        $ids = Permission::query()
            ->whereIn('name', $permissionNames)
            ->pluck('id');

        $this->permissions()->sync($ids);
    }
}
