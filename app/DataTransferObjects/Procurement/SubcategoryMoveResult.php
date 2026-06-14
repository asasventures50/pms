<?php

namespace App\DataTransferObjects\Procurement;

class SubcategoryMoveResult
{
    public function __construct(
        public string $subcategoryNameEn,
        public string $targetCategoryNameEn,
        public int $vendorLinksUpdated = 0,
        public int $brochuresUpdated = 0,
        public int $procurementRequestsUpdated = 0,
    ) {}

    public function summaryLine(): string
    {
        return sprintf(
            'Subcategory "%s" moved to "%s". Warning: %d vendor link(s), %d brochure(s), and %d procurement request(s) were updated to reflect the new parent category.',
            $this->subcategoryNameEn,
            $this->targetCategoryNameEn,
            $this->vendorLinksUpdated,
            $this->brochuresUpdated,
            $this->procurementRequestsUpdated,
        );
    }
}
