<?php

namespace App\Exports\Procurement\VendorQuotationInvite;

use App\Models\Procurement\Rfqs\RfqVendorQuotationInvite;
use App\Support\Procurement\Rfqs\PublicVendorQuotationExcelSchema;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PublicVendorQuotationItemsSheet implements FromCollection, WithColumnWidths, WithEvents, WithStyles, WithTitle
{
    public function __construct(
        private readonly RfqVendorQuotationInvite $invite,
    ) {}

    public function title(): string
    {
        return PublicVendorQuotationExcelSchema::SHEET_ITEMS;
    }

    public function collection(): Collection
    {
        $this->invite->loadMissing('rfq.items');

        $displayRow = PublicVendorQuotationExcelSchema::itemDisplayHeadings();
        $keyRow = PublicVendorQuotationExcelSchema::itemKeys();

        $dataRows = $this->invite->rfq->items
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
                    null, // line_total filled by Excel formula
                ];
            })
            ->all();

        return collect(array_merge([$displayRow, $keyRow], $dataRows));
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
            'O' => 14,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 11],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true,
                ],
            ],
            2 => [
                'font' => ['italic' => true, 'size' => 8, 'color' => ['rgb' => '64748B']],
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
                $itemCount = $this->invite->rfq->items->count();
                $firstDataRow = 3;
                $lastDataRow = $itemCount > 0 ? ($firstDataRow + $itemCount - 1) : 2;
                $totalRow = $lastDataRow + 1;

                $lockedFill = [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2E8F0'],
                ];
                $fillableFill = [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FEF3C7'],
                ];
                $totalFill = [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'DCFCE7'],
                ];

                // Header rows
                $sheet->getStyle('A1:E2')->getFill()->applyFromArray($lockedFill);
                $sheet->getStyle('F1:N2')->getFill()->applyFromArray($fillableFill);
                $sheet->getStyle('O1:O2')->getFill()->applyFromArray($totalFill);
                $sheet->getRowDimension(1)->setRowHeight(46);
                $sheet->getRowDimension(2)->setRowHeight(18);

                $this->addHeaderComments($sheet);

                if ($itemCount > 0) {
                    $sheet->getStyle("A{$firstDataRow}:E{$lastDataRow}")->getFill()->applyFromArray($lockedFill);
                    $sheet->getStyle("F{$firstDataRow}:N{$lastDataRow}")->getFill()->applyFromArray($fillableFill);
                    $sheet->getStyle("O{$firstDataRow}:O{$lastDataRow}")->getFill()->applyFromArray($totalFill);
                    $sheet->getStyle("A{$firstDataRow}:O{$lastDataRow}")
                        ->getAlignment()
                        ->setWrapText(true)
                        ->setVertical(Alignment::VERTICAL_TOP);

                    foreach (['D', 'F', 'J', 'K', 'L', 'M', 'O'] as $col) {
                        $sheet->getStyle("{$col}{$firstDataRow}:{$col}{$lastDataRow}")
                            ->getNumberFormat()
                            ->setFormatCode('#,##0.00');
                    }

                    $this->addNonNegativeValidation($sheet, "F{$firstDataRow}:F{$lastDataRow}");
                    $this->addNonNegativeValidation($sheet, "J{$firstDataRow}:J{$lastDataRow}");
                    $this->addNonNegativeValidation($sheet, "K{$firstDataRow}:K{$lastDataRow}");
                    $this->addNonNegativeValidation($sheet, "L{$firstDataRow}:L{$lastDataRow}");
                    $this->addNonNegativeValidation($sheet, "M{$firstDataRow}:M{$lastDataRow}");

                    for ($row = $firstDataRow; $row <= $lastDataRow; $row++) {
                        // Same logic as the online form: (qty * unit_price) - discount + installation + delivery
                        $sheet->setCellValue(
                            "O{$row}",
                            "=MAX(0,IF(F{$row}=\"\",0,F{$row})*IF(J{$row}=\"\",0,J{$row})-IF(K{$row}=\"\",0,K{$row}))+IF(L{$row}=\"\",0,L{$row})+IF(M{$row}=\"\",0,M{$row})"
                        );
                    }

                    $sheet->setCellValue("N{$totalRow}", __('vendor_quotation_invite.excel.grand_total_label'));
                    $sheet->setCellValue("O{$totalRow}", "=SUM(O{$firstDataRow}:O{$lastDataRow})");
                    $sheet->getStyle("N{$totalRow}:O{$totalRow}")->getFont()->setBold(true)->setSize(12);
                    $sheet->getStyle("N{$totalRow}:O{$totalRow}")->getFill()->applyFromArray($totalFill);
                    $sheet->getStyle("O{$totalRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                    $sheet->getStyle("N{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle("N{$totalRow}:O{$totalRow}")->getBorders()->getOutline()->setBorderStyle(Border::BORDER_MEDIUM);

                    $sheet->freezePane('F3');
                }

                $sheet->getStyle('A1:O2')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            },
        ];
    }

    private function addHeaderComments(Worksheet $sheet): void
    {
        $commentKeys = [
            'A' => 'rfq_item_id',
            'B' => 'item',
            'C' => 'description',
            'D' => 'quantity',
            'E' => 'unit',
            'F' => 'quantity_quoted',
            'G' => 'currency',
            'H' => 'brand',
            'I' => 'model',
            'J' => 'unit_price',
            'K' => 'discount',
            'L' => 'installation',
            'M' => 'delivery_charges',
            'N' => 'remarks',
            'O' => 'line_total',
        ];

        foreach ($commentKeys as $col => $key) {
            $text = (string) __('vendor_quotation_invite.excel.item_column_help.'.$key);
            if ($text === '' || str_starts_with($text, 'vendor_quotation_invite.')) {
                continue;
            }

            $rich = new RichText;
            $rich->createText($text);
            $sheet->getComment("{$col}1")
                ->setText($rich)
                ->setWidth('240pt')
                ->setHeight('90pt');
        }
    }

    private function addNonNegativeValidation(Worksheet $sheet, string $range): void
    {
        $validation = $sheet->getCell(explode(':', $range)[0])->getDataValidation();
        $validation->setType(DataValidation::TYPE_DECIMAL);
        $validation->setErrorStyle(DataValidation::STYLE_STOP);
        $validation->setAllowBlank(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowInputMessage(true);
        $validation->setErrorTitle('Invalid value');
        $validation->setError('Value must be 0 or greater.');
        $validation->setPromptTitle(__('vendor_quotation_invite.excel.fill_yellow_title'));
        $validation->setPrompt(__('vendor_quotation_invite.excel.fill_yellow_prompt'));
        $validation->setFormula1('0');
        $validation->setOperator(DataValidation::OPERATOR_GREATERTHANOREQUAL);
        $sheet->setDataValidation($range, $validation);
    }
}
