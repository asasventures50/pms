<?php

namespace App\Exports\Procurement;

use App\Exports\Procurement\Concerns\FormatsCategoriesExcelSheet;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;

class CategoriesTemplateExport implements FromCollection, WithColumnWidths, WithEvents, WithHeadings, WithStyles, WithTitle
{
    use FormatsCategoriesExcelSheet;

    public function collection(): Collection
    {
        return collect([
            [
                'مثال',
                'Example Category',
                'example-category',
                'Active',
                'مثال فرعي ١',
                'Example Subcategory 1',
                'example-subcategory-1',
                'Active',
            ],
            [
                null,
                null,
                null,
                null,
                'مثال فرعي ٢',
                'Example Subcategory 2',
                'example-subcategory-2',
                'Active',
            ],
        ]);
    }

    public function title(): string
    {
        return 'Template';
    }
}
