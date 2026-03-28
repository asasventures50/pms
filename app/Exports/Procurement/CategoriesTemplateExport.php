<?php

namespace App\Exports\Procurement;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class CategoriesTemplateExport implements FromCollection, WithHeadings, WithTitle
{
    public function collection(): Collection
    {
        return collect([
            [
                'مثال',
                'Example Category',
                'example-category',
                'active',
                'مثال فرعي',
                'Example Subcategory',
                'example-subcategory',
                'active',
            ],
        ]);
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
        return 'template';
    }
}
