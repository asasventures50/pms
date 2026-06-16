<?php

namespace App\Enums\Procurement\VendorQuotations;

enum VendorQuotationDocumentType: string
{
    case CommercialRegistration = 'commercial_registration';
    case TaxCertificate = 'tax_certificate';
    case CompanyProfile = 'company_profile';
    case TechnicalDatasheet = 'technical_datasheet';
    case ComplianceSheet = 'compliance_sheet';
    case WarrantyCertificate = 'warranty_certificate';
    case OtherSupportingDocuments = 'other_supporting_documents';

    public function label(): string
    {
        return match ($this) {
            self::CommercialRegistration => 'Commercial registration',
            self::TaxCertificate => 'Tax / VAT certificate',
            self::CompanyProfile => 'Company profile',
            self::TechnicalDatasheet => 'Technical datasheet (if applicable)',
            self::ComplianceSheet => 'Compliance sheet',
            self::WarrantyCertificate => 'Warranty certificate',
            self::OtherSupportingDocuments => 'Other supporting documents',
        };
    }

    public function inputName(): string
    {
        return 'document_'.$this->value;
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
