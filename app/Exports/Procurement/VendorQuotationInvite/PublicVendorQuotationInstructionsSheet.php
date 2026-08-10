<?php

namespace App\Exports\Procurement\VendorQuotationInvite;

use App\Support\Procurement\Rfqs\PublicVendorQuotationExcelSchema;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PublicVendorQuotationInstructionsSheet implements FromCollection, WithColumnWidths, WithStyles, WithTitle
{
    public function title(): string
    {
        return PublicVendorQuotationExcelSchema::SHEET_INSTRUCTIONS;
    }

    public function collection(): Collection
    {
        /** @var list<string> $lines */
        $lines = __('vendor_quotation_invite.excel.instructions');

        if (! is_array($lines)) {
            $lines = [(string) $lines];
        }

        return collect($lines)->map(fn (string $line) => [$line]);
    }

    /**
     * @return array<string, float>
     */
    public function columnWidths(): array
    {
        return [
            'A' => 100,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'alignment' => ['wrapText' => true],
            ],
        ];
    }
}
