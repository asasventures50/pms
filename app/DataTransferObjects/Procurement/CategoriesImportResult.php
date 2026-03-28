<?php

namespace App\DataTransferObjects\Procurement;

class CategoriesImportResult
{
    public function __construct(
        public int $categoriesCreated = 0,
        public int $categoriesUpdated = 0,
        public int $subcategoriesCreated = 0,
        public int $subcategoriesUpdated = 0,
        public int $failedRows = 0,
        /** @var list<string> */
        public array $errors = [],
    ) {}

    public function summaryLine(): string
    {
        return sprintf(
            'Categories: %d created, %d updated. Subcategories: %d created, %d updated. Failed rows: %d.',
            $this->categoriesCreated,
            $this->categoriesUpdated,
            $this->subcategoriesCreated,
            $this->subcategoriesUpdated,
            $this->failedRows
        );
    }
}
