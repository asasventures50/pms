<?php

namespace App\Exports\Procurement\VendorQuotationInvite;

use App\Models\Procurement\Rfqs\RfqVendorQuotationInvite;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PublicVendorQuotationExcelExport implements WithMultipleSheets
{
    public function __construct(
        private readonly RfqVendorQuotationInvite $invite,
    ) {}

    /**
     * @return list<object>
     */
    public function sheets(): array
    {
        return [
            new PublicVendorQuotationItemsSheet($this->invite),
            new PublicVendorQuotationContactSheet($this->invite),
            new PublicVendorQuotationInstructionsSheet,
        ];
    }
}
