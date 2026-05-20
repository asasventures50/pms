<?php

namespace App\DataTransferObjects\Procurement;

class VendorsImportResult
{
    public function __construct(
        public int $vendorsCreated = 0,
        public int $vendorsSkipped = 0,
        public int $failedRows = 0,
        /** @var list<string> */
        public array $errors = [],
    ) {}

    public function summaryLine(): string
    {
        return sprintf(
            'Vendors: %d created, %d skipped (existing names). Failed rows: %d.',
            $this->vendorsCreated,
            $this->vendorsSkipped,
            $this->failedRows
        );
    }
}
