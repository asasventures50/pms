<?php

namespace App\Enums\Procurement\Vendors;

enum LeadTimeRange: string
{
    case UpToOneWeek = 'up_to_1_week';
    case OneToTwoWeeks = 'one_to_two_weeks';
    case OneMonth = 'one_month';
    case MoreThanOneMonth = 'more_than_one_month';

    public function label(): string
    {
        return match ($this) {
            self::UpToOneWeek => 'Up to 1 Week',
            self::OneToTwoWeeks => '1 to 2 Weeks',
            self::OneMonth => '1 Month',
            self::MoreThanOneMonth => 'More than 1 Month',
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
