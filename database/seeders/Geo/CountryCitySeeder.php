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
                'name_en' => 'Syria',
                'name_ar' => 'Syria',
                'iso_code' => 'SY',
                'flag_emoji' => '🇸🇾',
                'cities' => [
                    ['name_en' => 'Damascus', 'name_ar' => 'Damascus'],
                    ['name_en' => 'Aleppo', 'name_ar' => 'Aleppo'],
                    ['name_en' => 'Homs', 'name_ar' => 'Homs'],
                    ['name_en' => 'Latakia', 'name_ar' => 'Latakia'],
                ],
            ],
            [
                'name_en' => 'United Arab Emirates',
                'name_ar' => 'United Arab Emirates',
                'iso_code' => 'AE',
                'flag_emoji' => '🇦🇪',
                'cities' => [
                    ['name_en' => 'Dubai', 'name_ar' => 'Dubai'],
                    ['name_en' => 'Abu Dhabi', 'name_ar' => 'Abu Dhabi'],
                    ['name_en' => 'Sharjah', 'name_ar' => 'Sharjah'],
                ],
            ],
            [
                'name_en' => 'Jordan',
                'name_ar' => 'Jordan',
                'iso_code' => 'JO',
                'flag_emoji' => '🇯🇴',
                'cities' => [
                    ['name_en' => 'Amman', 'name_ar' => 'Amman'],
                    ['name_en' => 'Irbid', 'name_ar' => 'Irbid'],
                    ['name_en' => 'Zarqa', 'name_ar' => 'Zarqa'],
                ],
            ],
            [
                'name_en' => 'Saudi Arabia',
                'name_ar' => 'Saudi Arabia',
                'iso_code' => 'SA',
                'flag_emoji' => '🇸🇦',
                'cities' => [
                    ['name_en' => 'Riyadh', 'name_ar' => 'Riyadh'],
                    ['name_en' => 'Jeddah', 'name_ar' => 'Jeddah'],
                    ['name_en' => 'Dammam', 'name_ar' => 'Dammam'],
                ],
            ],
            [
                'name_en' => 'Lebanon',
                'name_ar' => 'Lebanon',
                'iso_code' => 'LB',
                'flag_emoji' => '🇱🇧',
                'cities' => [
                    ['name_en' => 'Beirut', 'name_ar' => 'Beirut'],
                    ['name_en' => 'Tripoli', 'name_ar' => 'Tripoli'],
                    ['name_en' => 'Sidon', 'name_ar' => 'Sidon'],
                ],
            ],
        ];

        foreach ($dataset as $row) {
            $country = Country::query()->updateOrCreate(
                ['iso_code' => $row['iso_code']],
                [
                    'name' => $row['name_en'],
                    'name_en' => $row['name_en'],
                    'name_ar' => $row['name_ar'],
                    'flag_emoji' => $row['flag_emoji'],
                    'status' => 'active',
                ]
            );

            foreach ($row['cities'] as $city) {
                City::query()->updateOrCreate(
                    [
                        'country_id' => $country->id,
                        'name_en' => $city['name_en'],
                    ],
                    [
                        'name' => $city['name_en'],
                        'name_ar' => $city['name_ar'],
                        'status' => 'active',
                    ]
                );
            }
        }
    }
}
