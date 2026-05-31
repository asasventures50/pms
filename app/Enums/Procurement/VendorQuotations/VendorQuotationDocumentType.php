<?php

namespace App\Enums\Procurement\VendorQuotations;

enum VendorQuotationDocumentType: string
{
    case CommercialRegistration = 'commercial_registration';
    case CompanyProfile = 'company_profile';
    case TechnicalDatasheet = 'technical_datasheet';

    public function label(): string
    {
        return match ($this) {
            self::CommercialRegistration => 'Commercial registration',
            self::CompanyProfile => 'Company profile',
            self::TechnicalDatasheet => 'Technical datasheet',
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
