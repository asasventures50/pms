<?php

namespace App\Exports\Procurement;

use App\Models\Procurement\Vendors\Category;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class CategoriesExport implements FromCollection, WithHeadings, WithTitle
{
    public function collection(): Collection
    {
        $rows = collect();

        $categories = Category::query()
            ->with(['subcategories' => fn ($q) => $q->orderBy('id')])
            ->orderBy('id')
            ->get();

        foreach ($categories as $category) {
            if ($category->subcategories->isEmpty()) {
                $rows->push([
                    $category->name_ar,
                    $category->name_en,
                    $category->slug,
                    $category->status,
                    '',
                    '',
                    '',
                    '',
                ]);

                continue;
            }

            foreach ($category->subcategories as $sub) {
                $rows->push([
                    $category->name_ar,
                    $category->name_en,
                    $category->slug,
                    $category->status,
                    $sub->name_ar,
                    $sub->name_en,
                    $sub->slug,
                    $sub->status,
                ]);
            }
        }

        return $rows;
    }

    public function headings(): array
    {
        return [
            'category_name_ar',
            'category_name_en',
            'category_slug',
            'category_status',
            'subcategory_name_ar',
            'subcategory_name_en',
            'subcategory_slug',
            'subcategory_status',
        ];
    }

    public function title(): string
    {
        return 'categories';
    }
}
