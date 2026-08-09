<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('procurement_request_items', 'category_id')) {
            Schema::table('procurement_request_items', function (Blueprint $table) {
                $table->foreignId('category_id')
                    ->nullable()
                    ->after('zone_id')
                    ->constrained('categories')
                    ->nullOnDelete();
                $table->foreignId('subcategory_id')
                    ->nullable()
                    ->after('category_id')
                    ->constrained('subcategories')
                    ->nullOnDelete();
            });
        }

        $this->copyHeaderCategoriesToItems();

        if (Schema::hasColumn('procurement_requests', 'category_id')) {
            Schema::table('procurement_requests', function (Blueprint $table) {
                $table->dropConstrainedForeignId('subcategory_id');
                $table->dropConstrainedForeignId('category_id');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('procurement_requests', 'category_id')) {
            Schema::table('procurement_requests', function (Blueprint $table) {
                $table->foreignId('category_id')
                    ->nullable()
                    ->after('zone_id')
                    ->constrained('categories')
                    ->nullOnDelete();
                $table->foreignId('subcategory_id')
                    ->nullable()
                    ->after('category_id')
                    ->constrained('subcategories')
                    ->nullOnDelete();
            });
        }

        $this->restoreHeaderCategoriesFromFirstItem();

        if (Schema::hasColumn('procurement_request_items', 'category_id')) {
            Schema::table('procurement_request_items', function (Blueprint $table) {
                $table->dropConstrainedForeignId('subcategory_id');
                $table->dropConstrainedForeignId('category_id');
            });
        }
    }

    private function copyHeaderCategoriesToItems(): void
    {
        if (! Schema::hasColumn('procurement_requests', 'category_id')) {
            return;
        }

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            DB::statement('
                UPDATE procurement_request_items AS items
                INNER JOIN procurement_requests AS requests ON requests.id = items.procurement_request_id
                LEFT JOIN categories AS cats ON cats.id = requests.category_id
                LEFT JOIN subcategories AS subs ON subs.id = requests.subcategory_id
                SET
                    items.category_id = COALESCE(items.category_id, requests.category_id),
                    items.subcategory_id = COALESCE(items.subcategory_id, requests.subcategory_id),
                    items.category = CASE
                        WHEN (items.category IS NULL OR items.category = \'\')
                            THEN COALESCE(cats.name_en, cats.name_ar, items.category)
                        ELSE items.category
                    END,
                    items.subcategory = CASE
                        WHEN (items.subcategory IS NULL OR items.subcategory = \'\')
                            THEN COALESCE(subs.name_en, subs.name_ar, items.subcategory)
                        ELSE items.subcategory
                    END
                WHERE requests.category_id IS NOT NULL
                   OR requests.subcategory_id IS NOT NULL
            ');

            return;
        }

        $requests = DB::table('procurement_requests')
            ->select(['id', 'category_id', 'subcategory_id'])
            ->where(function ($query) {
                $query->whereNotNull('category_id')
                    ->orWhereNotNull('subcategory_id');
            })
            ->get();

        $categoryNames = DB::table('categories')
            ->select(['id', 'name_en', 'name_ar'])
            ->get()
            ->keyBy('id');
        $subcategoryNames = DB::table('subcategories')
            ->select(['id', 'name_en', 'name_ar'])
            ->get()
            ->keyBy('id');

        foreach ($requests as $request) {
            $categoryName = null;
            $subcategoryName = null;

            if ($request->category_id && isset($categoryNames[$request->category_id])) {
                $cat = $categoryNames[$request->category_id];
                $categoryName = $cat->name_en ?: $cat->name_ar;
            }

            if ($request->subcategory_id && isset($subcategoryNames[$request->subcategory_id])) {
                $sub = $subcategoryNames[$request->subcategory_id];
                $subcategoryName = $sub->name_en ?: $sub->name_ar;
            }

            $items = DB::table('procurement_request_items')
                ->where('procurement_request_id', $request->id)
                ->get(['id', 'category_id', 'subcategory_id', 'category', 'subcategory']);

            foreach ($items as $item) {
                $updates = [];

                if ($item->category_id === null && $request->category_id !== null) {
                    $updates['category_id'] = $request->category_id;
                }

                if ($item->subcategory_id === null && $request->subcategory_id !== null) {
                    $updates['subcategory_id'] = $request->subcategory_id;
                }

                if (($item->category === null || $item->category === '') && $categoryName !== null) {
                    $updates['category'] = $categoryName;
                }

                if (($item->subcategory === null || $item->subcategory === '') && $subcategoryName !== null) {
                    $updates['subcategory'] = $subcategoryName;
                }

                if ($updates !== []) {
                    DB::table('procurement_request_items')
                        ->where('id', $item->id)
                        ->update($updates);
                }
            }
        }
    }

    private function restoreHeaderCategoriesFromFirstItem(): void
    {
        $items = DB::table('procurement_request_items')
            ->select(['procurement_request_id', 'category_id', 'subcategory_id'])
            ->whereNotNull('category_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->groupBy('procurement_request_id');

        foreach ($items as $requestId => $rows) {
            $first = $rows->first();

            DB::table('procurement_requests')
                ->where('id', $requestId)
                ->update([
                    'category_id' => $first->category_id,
                    'subcategory_id' => $first->subcategory_id,
                ]);
        }
    }
};
