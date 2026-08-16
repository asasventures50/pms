<?php

namespace App\DataTransferObjects\Procurement;

class CategoryRebuildApplyResult
{
    public function __construct(
        public int $categoriesCreated = 0,
        public int $categoriesUpdated = 0,
        public int $subcategoriesCreated = 0,
        public int $subcategoriesUpdated = 0,
        public int $categoriesMapped = 0,
        public int $subcategoriesMapped = 0,
        public int $procurementRequestsUpdated = 0,
        public int $vendorLinksUpdated = 0,
        public int $brochuresUpdated = 0,
        public int $quickReceiptsUpdated = 0,
        public int $oldSubcategoriesRetired = 0,
        public int $oldCategoriesRetired = 0,
    ) {}

    public function summaryLine(): string
    {
        return sprintf(
            'New catalog: %d categories created, %d updated; %d subcategories created, %d updated. Mapped %d categor(y/ies) and %d subcategor(y/ies). Moved %d PR line(s), %d vendor link(s), %d brochure(s), %d quick receipt(s). Retired %d old subcategor(y/ies) and %d old categor(y/ies).',
            $this->categoriesCreated,
            $this->categoriesUpdated,
            $this->subcategoriesCreated,
            $this->subcategoriesUpdated,
            $this->categoriesMapped,
            $this->subcategoriesMapped,
            $this->procurementRequestsUpdated,
            $this->vendorLinksUpdated,
            $this->brochuresUpdated,
            $this->quickReceiptsUpdated,
            $this->oldSubcategoriesRetired,
            $this->oldCategoriesRetired,
        );
    }
}
