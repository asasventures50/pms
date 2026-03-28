<?php

namespace Database\Seeders\Procurement;

use App\Models\Procurement\Vendors\Category;
use App\Models\Procurement\Vendors\Subcategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class VendorCatalogSeeder extends Seeder
{
    public function run(): void
    {
        /** @var list<array{name_en: string, name_ar: string, subcategories: list<array{name_en: string, name_ar: string}>}> $catalog */
        $catalog = require __DIR__.'/vendor_catalog_dataset.php';

        foreach ($catalog as $categoryData) {
            $categorySlug = Str::slug($categoryData['name_en']);

            $category = Category::query()->withTrashed()->updateOrCreate(
                ['slug' => $categorySlug],
                [
                    'name_en' => $categoryData['name_en'],
                    'name_ar' => $categoryData['name_ar'],
                    'status' => 'active',
                    'deleted_at' => null,
                ]
            );

            foreach ($categoryData['subcategories'] as $subData) {
                $subSlug = Str::slug($subData['name_en']);

                Subcategory::query()->withTrashed()->updateOrCreate(
                    [
                        'category_id' => $category->id,
                        'slug' => $subSlug,
                    ],
                    [
                        'name_en' => $subData['name_en'],
                        'name_ar' => $subData['name_ar'],
                        'status' => 'active',
                        'deleted_at' => null,
                    ]
                );
            }
        }
    }
}
