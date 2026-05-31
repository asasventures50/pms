<?php

namespace App\Enums\Procurement\VendorQuotations;

enum QuotationCompliance: string
{
    case Compliant = 'compliant';
    case PartialCompliance = 'partial_compliance';
    case NotCompliant = 'not_compliant';

    public function label(): string
    {
        return match ($this) {
            self::Compliant => 'Compliant',
            self::PartialCompliance => 'Partial compliance',
            self::NotCompliant => 'Not compliant',
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
