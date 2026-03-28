<?php

namespace App\Imports\Procurement;

use App\DataTransferObjects\Procurement\CategoriesImportResult;
use App\Services\Procurement\Categories\CategoryImportProcessor;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CategoriesImport implements ToCollection, WithHeadingRow
{
    public CategoriesImportResult $result;

    public function __construct(
        private CategoryImportProcessor $processor
    ) {
        $this->result = new CategoriesImportResult;
    }

    public function collection(Collection $collection): void
    {
        $this->result = $this->processor->process($collection);
    }
}
