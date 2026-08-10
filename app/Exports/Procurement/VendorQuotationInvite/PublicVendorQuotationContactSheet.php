<?php

namespace App\Exports\Procurement\VendorQuotationInvite;

use App\Models\Procurement\Rfqs\RfqVendorQuotationInvite;
use App\Models\Procurement\Vendors\Vendor;
use App\Support\Procurement\Rfqs\PublicVendorQuotationExcelSchema;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PublicVendorQuotationContactSheet implements FromCollection, WithColumnWidths, WithEvents, WithHeadings, WithStyles, WithTitle
{
    public function __construct(
        private readonly RfqVendorQuotationInvite $invite,
    ) {}

    public function title(): string
    {
        return PublicVendorQuotationExcelSchema::SHEET_CONTACT;
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return [
            'key',
            (string) __('vendor_quotation_invite.excel.contact_headers.label'),
            (string) __('vendor_quotation_invite.excel.contact_headers.value'),
        ];
    }

    public function collection(): Collection
    {
        $this->invite->loadMissing('vendor');
        $vendor = $this->invite->vendor;

        $defaults = [
            'vendor_rep_name' => $vendor instanceof Vendor
                ? (string) ($vendor->primary_contact_name ?: '')
                : '',
            'vendor_rep_email' => $vendor instanceof Vendor
                ? (string) ($vendor->primary_contact_email ?: $vendor->email ?: '')
                : '',
            'vendor_rep_phone' => $vendor instanceof Vendor
                ? (string) ($vendor->primary_contact_phone ?: $vendor->phone ?: '')
                : '',
            'notes' => '',
        ];

        return collect(PublicVendorQuotationExcelSchema::contactKeys())->map(fn (string $key) => [
            $key,
            __('vendor_quotation_invite.excel.contact_labels.'.$key),
            $defaults[$key] ?? '',
        ]);
    }

    /**
     * @return array<string, float>
     */
    public function columnWidths(): array
    {
        return [
            'A' => 22,
            'B' => 36,
            'C' => 42,
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
                $sheet->getRowDimension(1)->setRowHeight(30);
                $sheet->getStyle('A1:B5')->getFill()->applyFromArray([
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E2E8F0'],
                ]);
                $sheet->getStyle('C1:C5')->getFill()->applyFromArray([
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FEF3C7'],
                ]);
                $sheet->getStyle('A2:C5')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
                $sheet->freezePane('A2');
            },
        ];
    }
}
