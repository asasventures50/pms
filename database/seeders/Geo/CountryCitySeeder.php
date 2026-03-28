<?php

namespace Database\Seeders\Geo;

use App\Models\Geo\City;
use App\Models\Geo\Country;
use Illuminate\Database\Seeder;

class CountryCitySeeder extends Seeder
{
    public function run(): void
    {
        $dataset = [
            [
                'name' => 'Syria',
                'iso_code' => 'SY',
                'flag_emoji' => '🇸🇾',
                'cities' => ['Damascus', 'Aleppo', 'Homs', 'Latakia'],
            ],
            [
                'name' => 'United Arab Emirates',
                'iso_code' => 'AE',
                'flag_emoji' => '🇦🇪',
                'cities' => ['Dubai', 'Abu Dhabi', 'Sharjah'],
            ],
            [
                'name' => 'Jordan',
                'iso_code' => 'JO',
                'flag_emoji' => '🇯🇴',
                'cities' => ['Amman', 'Irbid', 'Zarqa'],
            ],
            [
                'name' => 'Saudi Arabia',
                'iso_code' => 'SA',
                'flag_emoji' => '🇸🇦',
                'cities' => ['Riyadh', 'Jeddah', 'Dammam'],
            ],
            [
                'name' => 'Lebanon',
                'iso_code' => 'LB',
                'flag_emoji' => '🇱🇧',
                'cities' => ['Beirut', 'Tripoli', 'Sidon'],
            ],
        ];

        foreach ($dataset as $row) {
            $country = Country::query()->firstOrCreate(
                ['iso_code' => $row['iso_code']],
                [
                    'name' => $row['name'],
                    'flag_emoji' => $row['flag_emoji'],
                ]
            );

            foreach ($row['cities'] as $cityName) {
                City::query()->firstOrCreate(
                    [
                        'country_id' => $country->id,
                        'name' => $cityName,
                    ]
                );
            }
        }
    }
}
