<?php

namespace App\Exports\Procurement\VendorQuotationInvite;

use App\Models\Procurement\Rfqs\RfqVendorQuotationInvite;
use App\Support\Procurement\Rfqs\PublicVendorQuotationExcelSchema;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PublicVendorQuotationItemsSheet implements FromCollection, ShouldAutoSize, WithColumnWidths, WithEvents, WithHeadings, WithStyles, WithTitle
{
    public function __construct(
        private readonly RfqVendorQuotationInvite $invite,
    ) {}

    public function title(): string
    {
        return PublicVendorQuotationExcelSchema::SHEET_ITEMS;
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return PublicVendorQuotationExcelSchema::itemHeadings();
    }

    public function collection(): Collection
    {
        $this->invite->loadMissing('rfq.items');

        return $this->invite->rfq->items
            ->sortBy('sort_order')
            ->values()
            ->map(function ($line) {
                $qty = (float) $line->quantity;

                return [
                    $line->id,
                    $line->item ?: '',
                    $line->description ?: '',
                    $qty,
                    $line->unit ?: '',
                    $qty,
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                    '',
                ];
            });
    }

    /**
     * @return array<string, float>
     */
    public function columnWidths(): array
    {
        return [
            'A' => 12,
            'B' => 14,
            'C' => 42,
            'D' => 12,
            'E' => 10,
            'F' => 16,
            'G' => 12,
            'H' => 16,
            'I' => 18,
            'J' => 14,
            'K' => 12,
            'L' => 14,
            'M' => 16,
            'N' => 28,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
            ],
        ];
    }

    /**
     * @return list<array{AfterSheet::class, callable}>
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();
                $rowCount = max(2, $this->invite->rfq->items->count() + 1);

                $lockedFill = [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2E8F0'],
                ];
                $fillableFill = [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FEF3C7'],
                ];

                $sheet->getStyle('A1:E1')->getFill()->applyFromArray($lockedFill);
                $sheet->getStyle('F1:N1')->getFill()->applyFromArray($fillableFill);

                if ($rowCount >= 2) {
                    $sheet->getStyle("A2:E{$rowCount}")->getFill()->applyFromArray($lockedFill);
                    $sheet->getStyle("F2:N{$rowCount}")->getFill()->applyFromArray($fillableFill);
                    $sheet->getStyle("A2:N{$rowCount}")->getAlignment()->setWrapText(true)->setVertical(Alignment::VERTICAL_TOP);

                    foreach (['D', 'F', 'J', 'K', 'L', 'M'] as $col) {
                        $sheet->getStyle("{$col}2:{$col}{$rowCount}")
                            ->getNumberFormat()
                            ->setFormatCode(NumberFormat::FORMAT_NUMBER_00);
                    }

                    $this->addNonNegativeValidation($sheet, "F2:F{$rowCount}");
                    $this->addNonNegativeValidation($sheet, "J2:J{$rowCount}");
                    $this->addNonNegativeValidation($sheet, "K2:K{$rowCount}");
                    $this->addNonNegativeValidation($sheet, "L2:L{$rowCount}");
                    $this->addNonNegativeValidation($sheet, "M2:M{$rowCount}");
                }

                $sheet->freezePane('F2');
                $sheet->getRowDimension(1)->setRowHeight(28);
            },
        ];
    }

    private function addNonNegativeValidation(Worksheet $sheet, string $range): void
    {
        $validation = $sheet->getCell(explode(':', $range)[0])->getDataValidation();
        $validation->setType(DataValidation::TYPE_DECIMAL);
        $validation->setErrorStyle(DataValidation::STYLE_STOP);
        $validation->setAllowBlank(true);
        $validation->setShowErrorMessage(true);
        $validation->setErrorTitle('Invalid value');
        $validation->setError('Value must be 0 or greater.');
        $validation->setFormula1('0');
        $validation->setOperator(DataValidation::OPERATOR_GREATERTHANOREQUAL);
        $sheet->setDataValidation($range, $validation);
    }
}
