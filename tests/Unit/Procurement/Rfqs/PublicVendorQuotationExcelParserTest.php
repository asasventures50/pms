<?php

namespace Tests\Unit\Procurement\Rfqs;

use App\Models\Procurement\Rfqs\Rfq;
use App\Models\Procurement\Rfqs\RfqItem;
use App\Models\Procurement\Rfqs\RfqVendorQuotationInvite;
use App\Services\Procurement\Rfqs\PublicVendorQuotationExcelParser;
use App\Support\Procurement\Rfqs\PublicVendorQuotationExcelSchema;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class PublicVendorQuotationExcelParserTest extends TestCase
{
    public function test_parser_rejects_workbook_without_required_sheets(): void
    {
        $invite = new RfqVendorQuotationInvite(['rfq_id' => 1]);
        $rfq = new Rfq;
        $rfq->setRelation('items', collect());
        $invite->setRelation('rfq', $rfq);

        $path = $this->tempSpreadsheet(function (Spreadsheet $spreadsheet): void {
            $spreadsheet->getActiveSheet()->setTitle('Wrong');
            $spreadsheet->getActiveSheet()->setCellValue('A1', 'x');
        });

        $file = new UploadedFile($path, 'bad.xlsx', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', null, true);

        $this->expectException(ValidationException::class);

        (new PublicVendorQuotationExcelParser)->parse($invite, $file);
    }

    public function test_schema_item_headings_cover_form_fields(): void
    {
        $headings = PublicVendorQuotationExcelSchema::itemHeadings();

        foreach ([
            'rfq_item_id',
            'quantity_quoted',
            'currency',
            'brand',
            'model',
            'unit_price',
            'discount',
            'installation',
            'delivery_charges',
            'remarks',
        ] as $required) {
            $this->assertContains($required, $headings);
        }

        $this->assertContains('line_total', PublicVendorQuotationExcelSchema::itemKeys());
        $this->assertSame(count(PublicVendorQuotationExcelSchema::itemKeys()), count(PublicVendorQuotationExcelSchema::itemDisplayHeadings()));
    }

    /**
     * @param  callable(Spreadsheet): void  $build
     */
    private function tempSpreadsheet(callable $build): string
    {
        $spreadsheet = new Spreadsheet;
        $build($spreadsheet);

        $path = tempnam(sys_get_temp_dir(), 'vq-xlsx-').'.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return $path;
    }
}
