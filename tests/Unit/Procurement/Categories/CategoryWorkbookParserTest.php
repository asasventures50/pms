<?php

namespace Tests\Unit\Procurement\Categories;

use App\Services\Procurement\Categories\CategoryWorkbookParser;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PHPUnit\Framework\TestCase;

class CategoryWorkbookParserTest extends TestCase
{
    public function test_parser_prefers_updated_sheet_and_carries_category_cells(): void
    {
        $path = $this->tempSpreadsheet(function (Spreadsheet $spreadsheet): void {
            $old = $spreadsheet->getActiveSheet();
            $old->setTitle('Categories');
            $old->fromArray([
                ['Category (Arabic)', 'Category (English)', 'Category Slug', 'Subcategory (Arabic)', 'Subcategory (English)', 'Subcategory Slug'],
                ['قديم', 'Old Category', 'old-category', 'قديم فرعي', 'Old Sub', 'old-sub'],
            ]);

            $updated = $spreadsheet->createSheet();
            $updated->setTitle('Categories Updated');
            $updated->fromArray([
                ['Category (Arabic)', 'Category (English)', null, 'Subcategory (Arabic)', 'Subcategory (English)'],
                ['أعمال الانشاءات والاكساءات', 'Construction, Cladding & Fitting-out works', null, 'أعمال أرضيات', 'Flooring Works'],
                [null, null, null, 'أعمال الدهان', 'Paint Work'],
                ['اعمال ميكانيك', 'Mechanical Works', null, 'أعمال تهوية', 'Ventilation works'],
            ]);
        });

        $file = new UploadedFile(
            $path,
            'categories.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true,
        );

        $parsed = (new CategoryWorkbookParser)->parse($file);

        $this->assertSame('Categories Updated', $parsed['sheet']);
        $this->assertCount(2, $parsed['categories']);
        $this->assertSame('Construction, Cladding & Fitting-out works', $parsed['categories'][0]['name_en']);
        $this->assertSame('c:construction-cladding-fitting-out-works', $parsed['categories'][0]['key']);
        $this->assertCount(2, $parsed['categories'][0]['subcategories']);
        $this->assertSame('Flooring Works', $parsed['categories'][0]['subcategories'][0]['name_en']);
        $this->assertSame('Paint Work', $parsed['categories'][0]['subcategories'][1]['name_en']);
        $this->assertSame('s:construction-cladding-fitting-out-works/paint-work', $parsed['categories'][0]['subcategories'][1]['key']);
        $this->assertSame('Mechanical Works', $parsed['categories'][1]['name_en']);
        $this->assertSame('Ventilation works', $parsed['categories'][1]['subcategories'][0]['name_en']);
    }

    public function test_duplicate_sub_names_under_different_parents_get_distinct_keys(): void
    {
        $path = $this->tempSpreadsheet(function (Spreadsheet $spreadsheet): void {
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Categories Updated');
            $sheet->fromArray([
                ['Category (English)', 'Subcategory (English)'],
                ['Hardscape Works', 'Lighting Works'],
                ['Electrical Works', 'Lighting Works'],
            ]);
        });

        $file = new UploadedFile(
            $path,
            'categories.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true,
        );

        $parsed = (new CategoryWorkbookParser)->parse($file);

        $this->assertSame('s:hardscape-works/lighting-works', $parsed['categories'][0]['subcategories'][0]['key']);
        $this->assertSame('s:electrical-works/lighting-works', $parsed['categories'][1]['subcategories'][0]['key']);
        $this->assertNotSame(
            $parsed['categories'][0]['subcategories'][0]['key'],
            $parsed['categories'][1]['subcategories'][0]['key'],
        );
    }

    /**
     * @param  callable(Spreadsheet): void  $build
     */
    private function tempSpreadsheet(callable $build): string
    {
        $spreadsheet = new Spreadsheet;
        $build($spreadsheet);

        $path = tempnam(sys_get_temp_dir(), 'cat-xlsx-').'.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return $path;
    }
}
