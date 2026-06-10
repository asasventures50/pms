<?php

namespace App\Exports\Procurement;

use App\Models\Procurement\Vendors\Category;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class CategoriesExport implements FromCollection, WithHeadings, WithTitle
{
    public function collection(): Collection
    {
        return Category::query()
            ->withCount('subcategories')
            ->withCount(['vendors as vendors_count' => function ($q) {
                $q->select(DB::raw('count(distinct vendors.id)'));
            }])
            ->orderBy('id')
            ->get()
            ->map(fn (Category $category) => [
                $category->id,
                $category->name_ar,
                $category->name_en,
                $category->slug,
                $category->status,
                (int) $category->subcategories_count,
                (int) $category->vendors_count,
                $category->created_at?->format('Y-m-d H:i'),
            ]);
    }

    public function headings(): array
    {
        return [
            'id',
            'name_ar',
            'name_en',
            'slug',
            'status',
            'subcategories_count',
            'vendors_count',
            'created_at',
        ];
    }

    public function title(): string
    {
        return 'categories';
    }
}
