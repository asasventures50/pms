<?php

namespace App\Exports\Procurement;

use App\Exports\Procurement\Concerns\FormatsCategoriesExcelSheet;
use App\Models\Procurement\Vendors\Category;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;

class CategoriesExport implements FromCollection, WithColumnWidths, WithEvents, WithHeadings, WithStyles, WithTitle
{
    use FormatsCategoriesExcelSheet;

    public function collection(): Collection
    {
        $rows = collect();

        $categories = Category::query()
            ->with(['subcategories' => fn ($q) => $q->orderBy('name_ar')->orderBy('name_en')->orderBy('id')])
            ->orderBy('name_ar')
            ->orderBy('name_en')
            ->orderBy('id')
            ->get();

        foreach ($categories as $category) {
            if ($category->subcategories->isEmpty()) {
                $rows->push([
                    $category->name_ar,
                    $category->name_en,
                    $category->slug,
                    $this->formatStatus($category->status),
                    null,
                    null,
                    null,
                    null,
                ]);

                continue;
            }

            $isFirstSubcategory = true;

            foreach ($category->subcategories as $subcategory) {
                $rows->push([
                    $isFirstSubcategory ? $category->name_ar : null,
                    $isFirstSubcategory ? $category->name_en : null,
                    $isFirstSubcategory ? $category->slug : null,
                    $isFirstSubcategory ? $this->formatStatus($category->status) : null,
                    $subcategory->name_ar,
                    $subcategory->name_en,
                    $subcategory->slug,
                    $this->formatStatus($subcategory->status),
                ]);

                $isFirstSubcategory = false;
            }
        }

        return $rows;
    }

    public function title(): string
    {
        return 'Categories';
    }

    private function formatStatus(?string $status): string
    {
        $value = strtolower(trim((string) $status));

        return match ($value) {
            'active' => 'Active',
            'inactive' => 'Inactive',
            default => $status ? ucfirst($status) : 'Active',
        };
    }
}
