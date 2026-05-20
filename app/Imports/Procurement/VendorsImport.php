<?php

namespace App\Imports\Procurement;

use App\DataTransferObjects\Procurement\VendorsImportResult;
use App\Services\Procurement\Vendors\VendorImportProcessor;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class VendorsImport implements ToCollection
{
    public VendorsImportResult $result;

    public function __construct(
        private VendorImportProcessor $processor
    ) {
        $this->result = new VendorsImportResult;
    }

    public function collection(Collection $collection): void
    {
        $this->result = $this->processor->process($collection);
    }
}
