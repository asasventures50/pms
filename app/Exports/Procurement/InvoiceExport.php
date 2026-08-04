<?php

namespace App\Exports\Procurement;

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
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Read-only Excel mirror of the invoice print lines table (no notes/bank).
 */
class InvoiceExport implements FromArray, WithColumnWidths, WithEvents, WithStyles, WithTitle
{
    private int $headerRowsCount = 0;

    private int $tableHeaderRow = 0;

    private int $firstDataRow = 0;

    private int $lastDataRow = 0;

    private int $totalRow = 0;

    /**
     * @param  Collection<int|string, mixed>  $poItemsById
     * @param  array<string, string>  $unitsByLineCode
     */
    public function __construct(
        private Invoice $invoice,
        private Collection $poItemsById,
        private InvoiceProjectZoneResolver $projectZoneResolver,
        private array $unitsByLineCode = [],
    ) {}

    public function array(): array
    {
        $currency = $this->invoice->displayCurrency();
        $currencySuffix = $currency ? ' ('.$currency.')' : '';
        $projectLabel = $this->projectZoneResolver->uniqueProjectsLabelForInvoice(
            $this->invoice,
            $this->poItemsById,
        );

        $rows = [
            ['فاتورة'],
            ['رقم الفاتورة', $this->invoice->invoice_number],
            ['التاريخ', $this->invoice->invoiced_at?->format('d-m-Y')],
            ['السيد / السادة', $this->invoice->recipient_name],
        ];

        if ($projectLabel !== null && $projectLabel !== '') {
            $rows[] = ['المشروع', $projectLabel];
        }

        $this->headerRowsCount = count($rows);
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

        $rows[] = [
            'المجموع الكلي',
            '',
            '',
            '',
            '',
            '',
            round((float) $this->invoice->total_price, 2),
        ];

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

    public function columnWidths(): array
    {
        return [
            'A' => 10,
            'B' => 28,
            'C' => 48,
            'D' => 12,
            'E' => 10,
            'F' => 18,
            'G' => 16,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 16],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $sheet->setRightToLeft(true);

                $sheet->mergeCells('A1:G1');

                for ($row = 2; $row <= $this->headerRowsCount; $row++) {
                    $sheet->getStyle("A{$row}")->getFont()->setBold(true);
                    $sheet->getStyle("A{$row}:B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }

                $tableStart = $this->tableHeaderRow;
                $tableEnd = $this->totalRow;
                $range = "A{$tableStart}:G{$tableEnd}";

                $sheet->getStyle($range)->getBorders()->getAllBorders()->applyFromArray([
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '94A3B8'],
                ]);

                $sheet->getStyle("A{$tableStart}:G{$tableStart}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F1F5F9'],
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
                    $sheet->getStyle("F{$this->firstDataRow}:G{$this->totalRow}")
                        ->getNumberFormat()->setFormatCode('#,##0.00');
                    $sheet->getStyle("D{$this->firstDataRow}:D{$this->lastDataRow}")
                        ->getNumberFormat()->setFormatCode('#,##0.000');
                }

                $sheet->mergeCells("A{$this->totalRow}:F{$this->totalRow}");
                $sheet->getStyle("A{$this->totalRow}:G{$this->totalRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'E2E8F0'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $sheet->getStyle("A{$this->totalRow}")
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            },
        ];
    }

    public function title(): string
    {
        return 'فاتورة';
    }
}
