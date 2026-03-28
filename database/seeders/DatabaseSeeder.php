<?php

namespace Database\Seeders;

use Database\Seeders\Geo\CountryCitySeeder;
use Database\Seeders\Procurement\VendorCatalogSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(AdminUserSeeder::class);
        $this->call(CountryCitySeeder::class);
        $this->call(VendorCatalogSeeder::class);
    }
}
