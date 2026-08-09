<?php

namespace App\Enums\Procurement\Rfqs;

enum RfqVendorQuotationInviteStatus: string
{
    case Pending = 'pending';
    case Submitted = 'submitted';
    case Revoked = 'revoked';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Submitted => 'Submitted',
            self::Revoked => 'Revoked',
        };
    }
}
