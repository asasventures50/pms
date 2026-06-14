<?php

namespace App\DataTransferObjects\Procurement;

class SubcategoryMoveImpact
{
    public function __construct(
        public int $vendorLinks = 0,
        public int $brochures = 0,
        public int $procurementRequests = 0,
        public bool $hasNameConflict = false,
        public bool $hasSlugConflict = false,
    ) {}

    public function hasConflicts(): bool
    {
        return $this->hasNameConflict || $this->hasSlugConflict;
    }

    public function hasAffectedRecords(): bool
    {
        return $this->vendorLinks > 0
            || $this->brochures > 0
            || $this->procurementRequests > 0;
    }
}
