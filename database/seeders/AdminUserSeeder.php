<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\Access\PermissionCatalog;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Default back-office user (no public registration — accounts are provisioned).
     */
    public function run(): void
    {
        $user = User::query()->firstOrCreate(
            ['email' => 'pms@tamkeensy.com'],
            [
                'name' => 'PMS Admin',
                'password' => 'AdminPMS@2030!',
            ]
        );

        $user->syncRoles([PermissionCatalog::SUPER_ADMIN_ROLE]);
    }
}
