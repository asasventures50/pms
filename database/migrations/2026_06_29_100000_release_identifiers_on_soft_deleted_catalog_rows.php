<?php

use App\Models\Procurement\Vendors\Category;
use App\Models\Procurement\Vendors\Subcategory;
use App\Support\CatalogIdentifiers;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Category::onlyTrashed()
            ->orderBy('id')
            ->each(function (Category $category): void {
                $category->update([
                    'slug' => CatalogIdentifiers::releaseSlug($category->slug, $category->id),
                    'name_en' => CatalogIdentifiers::releaseNameEn($category->name_en, $category->id),
                ]);
            });

        Subcategory::onlyTrashed()
            ->orderBy('id')
            ->each(function (Subcategory $subcategory): void {
                $subcategory->update([
                    'slug' => CatalogIdentifiers::releaseSlug($subcategory->slug, $subcategory->id),
                    'name_en' => CatalogIdentifiers::releaseNameEn($subcategory->name_en, $subcategory->id),
                ]);
            });
    }

    public function down(): void
    {
        // Irreversible: released identifiers cannot be restored safely.
    }
};
