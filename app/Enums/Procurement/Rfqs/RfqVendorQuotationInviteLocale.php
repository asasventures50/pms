<?php

namespace App\Enums\Procurement\Rfqs;

enum RfqVendorQuotationInviteLocale: string
{
    case Ar = 'ar';
    case En = 'en';
    case VendorChoice = 'vendor_choice';

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
            self::Ar => 'Arabic',
            self::En => 'English',
            self::VendorChoice => 'Default (vendor chooses)',
        };
    }

    public function locksLocale(): bool
    {
        return $this === self::Ar || $this === self::En;
    }

    public function lockedLocale(): ?string
    {
        return $this->locksLocale() ? $this->value : null;
    }
}
