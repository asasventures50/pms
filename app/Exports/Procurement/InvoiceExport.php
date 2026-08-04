<?php

namespace App\Exports\Procurement;

use App\Enums\Procurement\PrCompany;
use App\Models\Procurement\Invoices\Invoice;
use App\Models\Procurement\Invoices\InvoiceItem;
use App\Services\Procurement\Invoices\InvoiceProjectZoneResolver;
use App\Services\Procurement\PurchaseOrders\ProcurementRequestLineUnitLookup;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Single-sheet Excel mirror of the invoice print/PDF layout.
 * Read-only — does not mutate invoice data.
 */
class InvoiceExport implements FromArray, WithColumnWidths, WithEvents, WithStyles, WithTitle
{
    private int $titleRow = 1;

    private int $metaStartRow = 0;

    private int $metaEndRow = 0;

    private int $tableHeaderRow = 0;

    private int $firstDataRow = 0;

    private int $lastDataRow = 0;

    private int $totalRow = 0;

    private int $notesTitleRow = 0;

    /** @var list<int> */
    private array $noteRows = [];

    private int $bankTitleRow = 0;

    /** @var list<int> */
    private array $bankRows = [];

    private int $signaturesTitleRow = 0;

    private int $signaturesLabelRow = 0;

    private int $signaturesPadEndRow = 0;

    private int $footerStartRow = 0;

    private int $footerEndRow = 0;

    private ?string $logoPath = null;

    /**
     * @param  Collection<int|string, mixed>  $poItemsById
     * @param  array<string, string>  $unitsByLineCode
     */
    public function __construct(
        private Invoice $invoice,
        private Collection $poItemsById,
        private InvoiceProjectZoneResolver $projectZoneResolver,
        private array $unitsByLineCode = [],
    ) {
        $company = PrCompany::AsasVentures;
        if ($company->logoExists()) {
            $relative = $company->logoRelativePath();
            $path = public_path($relative);
            if (! is_file($path) && $company === PrCompany::AsasVentures) {
                $path = public_path('images/po/logo.png');
            }
            $this->logoPath = is_file($path) ? $path : null;
        }
    }

    public function array(): array
    {
        $currency = $this->invoice->displayCurrency();
        $currencySuffix = $currency ? ' ('.$currency.')' : '';
        $projectLabel = $this->projectZoneResolver->uniqueProjectsLabelForInvoice(
            $this->invoice,
            $this->poItemsById,
        );
        $buyer = PrCompany::AsasVentures->details();

        $rows = [
            ['', 'فاتورة', '', '', '', '', ''],
        ];
        $this->titleRow = 1;

        $this->metaStartRow = 3;
        $rows[] = [];
        $rows[] = ['رقم الفاتورة', $this->invoice->invoice_number, '', '', '', '', ''];
        $rows[] = ['التاريخ', $this->invoice->invoiced_at?->format('d-m-Y'), '', '', '', '', ''];
        $rows[] = ['السيد / السادة', $this->invoice->recipient_name, '', '', '', '', ''];
        if ($projectLabel !== null && $projectLabel !== '') {
            $rows[] = ['المشروع', $projectLabel, '', '', '', '', ''];
        }
        $this->metaEndRow = count($rows);

        $rows[] = [];
        $this->tableHeaderRow = count($rows) + 1;
        $rows[] = [
            'م',
            'المنطقة',
            'البيان',
            'الكمية',
            'الوحدة',
            'سعر الوحدة'.$currencySuffix,
            'المجموع'.$currencySuffix,
        ];
        $this->firstDataRow = $this->tableHeaderRow + 1;

        foreach ($this->invoice->items as $line) {
            $rows[] = $this->mapItemRow($line);
        }

        $nextLineNumber = ((int) $this->invoice->items->max('line_number')) + 1;
        foreach ($this->invoice->feeRowsForPrint() as $fee) {
            $feeZone = InvoiceProjectZoneResolver::splitStoredLabel($fee['project_zone'] ?? '')['zone'];
            $quantity = (float) $fee['quantity'];
            $amount = (float) $fee['amount'];
            $unitPrice = $quantity > 0 ? round($amount / $quantity, 2) : 0.0;

            $rows[] = [
                $nextLineNumber++,
                $feeZone !== '' ? $feeZone : '—',
                $fee['description'],
                round($quantity, 3),
                ($fee['unit'] ?? '') !== '' ? $fee['unit'] : '—',
                $unitPrice,
                round($amount, 2),
            ];
        }

        $this->lastDataRow = count($rows);
        $this->totalRow = $this->lastDataRow + 1;
        $rows[] = ['المجموع الكلي', '', '', '', '', '', round((float) $this->invoice->total_price, 2)];

        $notes = $this->invoice->displayNotes();
        if ($notes !== []) {
            $rows[] = [];
            $this->notesTitleRow = count($rows) + 1;
            $rows[] = ['ملاحظات:', '', '', '', '', '', ''];
            foreach ($notes as $note) {
                $this->noteRows[] = count($rows) + 1;
                $rows[] = [$note, '', '', '', '', '', ''];
            }
        }

        $bank = $buyer['bank'] ?? ['title' => 'معلومات البنك', 'lines' => []];
        $bankLines = array_values(array_filter($bank['lines'] ?? [], static fn ($line) => filled($line)));
        if ($bankLines !== []) {
            $rows[] = [];
            $this->bankTitleRow = count($rows) + 1;
            $rows[] = [$bank['title'] ?? 'معلومات البنك', '', '', '', '', '', ''];
            foreach ($bankLines as $line) {
                [$label, $value] = $this->splitLabelValue((string) $line);
                $this->bankRows[] = count($rows) + 1;
                $rows[] = [$label, $value, '', '', '', '', ''];
            }
        }

        $rows[] = [];
        $this->signaturesTitleRow = count($rows) + 1;
        $rows[] = ['التواقيع', '', '', '', '', '', ''];
        $this->signaturesLabelRow = count($rows) + 1;
        $rows[] = ['استلام العميل للفاتورة', '', 'إدارة الحسابات', '', 'الإدارة العامة', '', ''];
        $rows[] = ['', '', '', '', '', '', ''];
        $rows[] = ['', '', '', '', '', '', ''];
        $rows[] = ['', '', '', '', '', '', ''];
        $this->signaturesPadEndRow = count($rows);

        $footerParts = array_values(array_filter([
            $buyer['company_legal_type'] ?? null,
            $buyer['commercial_registry'] ?? null,
        ]));
        $contactParts = array_values(array_filter([
            filled($buyer['phone'] ?? null) ? 'هاتف: '.$buyer['phone'] : null,
            filled($buyer['email'] ?? null) ? 'بريد إلكتروني: '.$buyer['email'] : null,
            filled($buyer['fax'] ?? null) ? 'فاكس: '.$buyer['fax'] : null,
        ]));

        if ($footerParts !== [] || $contactParts !== []) {
            $rows[] = [];
            $this->footerStartRow = count($rows) + 1;
            if ($footerParts !== []) {
                $rows[] = [implode(' | ', $footerParts), '', '', '', '', '', ''];
            }
            if ($contactParts !== []) {
                $rows[] = [implode(' | ', $contactParts), '', '', '', '', '', ''];
            }
            $this->footerEndRow = count($rows);
        }

        return $rows;
    }

    /**
     * @return list<mixed>
     */
    private function mapItemRow(InvoiceItem $line): array
    {
        $zone = $this->projectZoneResolver->zoneForInvoiceItem($line, $this->poItemsById);
        $zone = $zone !== null && $zone !== '' ? $zone : '—';

        $unit = trim((string) ($line->unit ?? ''));
        if ($unit === '') {
            $sourcePoItem = collect($line->source_purchase_order_item_ids ?? [])
                ->map(fn ($id) => $this->poItemsById->get((int) $id))
                ->filter()
                ->first();

            if ($sourcePoItem) {
                $unit = trim((string) (ProcurementRequestLineUnitLookup::resolveForPurchaseOrderItem(
                    $sourcePoItem,
                    $this->unitsByLineCode,
                ) ?? ''));
            }
        }

        $quantity = (float) $line->quantity;
        $lineTotal = (float) $line->line_total;
        $unitPrice = $quantity > 0 ? round($lineTotal / $quantity, 2) : 0.0;

        return [
            $line->line_number,
            $zone,
            $line->description,
            round($quantity, 3),
            $unit !== '' ? $unit : '—',
            $unitPrice,
            round($lineTotal, 2),
        ];
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function splitLabelValue(string $text): array
    {
        $text = trim($text);
        $colonPos = mb_strpos($text, ':');
        if ($colonPos === false) {
            return [$text, ''];
        }

        return [
            trim(mb_substr($text, 0, $colonPos)),
            trim(mb_substr($text, $colonPos + 1)),
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 22,
            'B' => 28,
            'C' => 42,
            'D' => 12,
            'E' => 14,
            'F' => 18,
            'G' => 16,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            $this->titleRow => [
                'font' => ['bold' => true, 'size' => 18],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->setRightToLeft(true);
                $sheet->getRowDimension($this->titleRow)->setRowHeight(52);
                $sheet->mergeCells('B1:G1');
                $sheet->getStyle('B1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 18],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                if ($this->logoPath !== null) {
                    $drawing = new Drawing;
                    $drawing->setName('Logo');
                    $drawing->setDescription('Company logo');
                    $drawing->setPath($this->logoPath);
                    $drawing->setHeight(48);
                    $drawing->setCoordinates('A1');
                    $drawing->setOffsetX(6);
                    $drawing->setOffsetY(4);
                    $drawing->setWorksheet($sheet);
                }

                for ($row = $this->metaStartRow; $row <= $this->metaEndRow; $row++) {
                    $sheet->mergeCells("B{$row}:G{$row}");
                    $sheet->getStyle("A{$row}")->getFont()->setBold(true);
                    $sheet->getStyle("A{$row}:G{$row}")->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_RIGHT)
                        ->setVertical(Alignment::VERTICAL_CENTER);
                }

                $tableRange = "A{$this->tableHeaderRow}:G{$this->totalRow}";
                $sheet->getStyle($tableRange)->getBorders()->getAllBorders()->applyFromArray([
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '64748B'],
                ]);
                $sheet->getStyle("A{$this->tableHeaderRow}:G{$this->tableHeaderRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'E2E8F0'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                ]);

                if ($this->firstDataRow <= $this->lastDataRow) {
                    $sheet->getStyle("A{$this->firstDataRow}:A{$this->lastDataRow}")
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("D{$this->firstDataRow}:G{$this->lastDataRow}")
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("B{$this->firstDataRow}:C{$this->lastDataRow}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_RIGHT)
                        ->setWrapText(true);
                    $sheet->getStyle("D{$this->firstDataRow}:D{$this->lastDataRow}")
                        ->getNumberFormat()->setFormatCode('#,##0.000');
                    $sheet->getStyle("F{$this->firstDataRow}:G{$this->totalRow}")
                        ->getNumberFormat()->setFormatCode('#,##0.00');
                }

                $sheet->mergeCells("A{$this->totalRow}:F{$this->totalRow}");
                $sheet->getStyle("A{$this->totalRow}:G{$this->totalRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'CBD5E1'],
                    ],
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getStyle("A{$this->totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle("G{$this->totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                if ($this->notesTitleRow > 0) {
                    $sheet->mergeCells("A{$this->notesTitleRow}:G{$this->notesTitleRow}");
                    $sheet->getStyle("A{$this->notesTitleRow}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 12],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'F8FAFC'],
                        ],
                    ]);
                    foreach ($this->noteRows as $row) {
                        $sheet->mergeCells("A{$row}:G{$row}");
                        $sheet->getStyle("A{$row}")->getAlignment()
                            ->setWrapText(true)
                            ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                        $sheet->getRowDimension($row)->setRowHeight(-1);
                        $sheet->getStyle("A{$row}:G{$row}")->getBorders()->getOutline()->applyFromArray([
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CBD5E1'],
                        ]);
                    }
                }

                if ($this->bankTitleRow > 0) {
                    $sheet->mergeCells("A{$this->bankTitleRow}:G{$this->bankTitleRow}");
                    $sheet->getStyle("A{$this->bankTitleRow}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 12],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'F8FAFC'],
                        ],
                    ]);
                    foreach ($this->bankRows as $row) {
                        $sheet->mergeCells("B{$row}:G{$row}");
                        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
                        $sheet->getStyle("A{$row}:G{$row}")->getAlignment()
                            ->setHorizontal(Alignment::HORIZONTAL_RIGHT)
                            ->setWrapText(true);
                        $sheet->getStyle("A{$row}:G{$row}")->getBorders()->getAllBorders()->applyFromArray([
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CBD5E1'],
                        ]);
                    }
                }

                if ($this->signaturesTitleRow > 0) {
                    $sheet->mergeCells("A{$this->signaturesTitleRow}:G{$this->signaturesTitleRow}");
                    $sheet->getStyle("A{$this->signaturesTitleRow}")->applyFromArray([
                        'font' => ['bold' => true, 'size' => 12],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'F8FAFC'],
                        ],
                    ]);

                    $labelRow = $this->signaturesLabelRow;
                    $sheet->mergeCells("A{$labelRow}:B{$labelRow}");
                    $sheet->mergeCells("C{$labelRow}:D{$labelRow}");
                    $sheet->mergeCells("E{$labelRow}:G{$labelRow}");
                    $sheet->getStyle("A{$labelRow}:G{$labelRow}")->applyFromArray([
                        'font' => ['bold' => true],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                    ]);

                    for ($row = $labelRow + 1; $row <= $this->signaturesPadEndRow; $row++) {
                        $sheet->mergeCells("A{$row}:B{$row}");
                        $sheet->mergeCells("C{$row}:D{$row}");
                        $sheet->mergeCells("E{$row}:G{$row}");
                        $sheet->getRowDimension($row)->setRowHeight(22);
                    }

                    $padStart = $labelRow + 1;
                    $padEnd = $this->signaturesPadEndRow;
                    foreach (['A', 'C', 'E'] as $col) {
                        $endCol = $col === 'A' ? 'B' : ($col === 'C' ? 'D' : 'G');
                        $sheet->getStyle("{$col}{$padStart}:{$endCol}{$padEnd}")->getBorders()->getOutline()->applyFromArray([
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '94A3B8'],
                        ]);
                        $sheet->getStyle("{$col}{$padEnd}:{$endCol}{$padEnd}")->getBorders()->getBottom()->applyFromArray([
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '334155'],
                        ]);
                    }
                }

                if ($this->footerStartRow > 0) {
                    for ($row = $this->footerStartRow; $row <= $this->footerEndRow; $row++) {
                        $sheet->mergeCells("A{$row}:G{$row}");
                        $sheet->getStyle("A{$row}")->applyFromArray([
                            'font' => ['size' => 9, 'color' => ['rgb' => '475569']],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_CENTER,
                                'wrapText' => true,
                            ],
                        ]);
                    }
                }
            },
        ];
    }

    public function title(): string
    {
        return 'فاتورة';
    }
}
