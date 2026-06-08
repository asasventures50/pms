<?php

namespace App\Enums\Procurement\ProcurementRequests;

enum ProcurementVendorType: string
{
    case Contractor = 'contractor';
    case Supplier = 'supplier';
    case Studies = 'studies';

    public function label(): string
    {
        return match ($this) {
            self::Contractor => 'Contractor',
            self::Supplier => 'Supplier',
            self::Studies => 'Studies',
        };
    }

    /**
     * Maps to legacy {@see \App\Support\Procurement\ProcurementScopeType} labels.
     */
    public function legacyScopeLabel(): string
    {
        return match ($this) {
            self::Contractor => 'Contractor',
            self::Supplier => 'Supplier',
            self::Studies => 'Studies',
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case) => $case->value, self::cases());
    }
}
