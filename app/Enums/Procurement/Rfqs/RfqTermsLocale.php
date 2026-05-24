<?php

namespace App\Enums\Procurement\Rfqs;

enum RfqTermsLocale: string
{
    case Ar = 'ar';
    case En = 'en';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case) => $case->value, self::cases());
    }

    public static function default(): self
    {
        return self::En;
    }

    public function label(): string
    {
        return match ($this) {
            self::Ar => 'Arabic',
            self::En => 'English',
        };
    }
}
