<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Default back-office user (no public registration — accounts are provisioned).
     */
    public function run(): void
    {
        User::query()->firstOrCreate(
            ['email' => 'pms@tamkeensy.com'],
            [
                'name' => 'PMS Admin',
                'password' => 'AdminPMS@2030!',
            ]
        );
    }
}
