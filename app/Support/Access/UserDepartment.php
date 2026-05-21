<?php

namespace App\Support\Access;

final class UserDepartment
{
    public const DEFAULT = 'general';

    /**
     * @return list<string>
     */
    public static function options(): array
    {
        return [
            self::DEFAULT => 'General',
            'procurement' => 'Procurement',
            'finance' => 'Finance',
            'operations' => 'Operations',
            'it' => 'IT',
            'hr' => 'HR',
        ];
    }

    public static function label(string $value): string
    {
        return self::options()[$value] ?? ucfirst(str_replace(['_', '-'], ' ', $value));
    }
}
