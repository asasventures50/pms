<?php

namespace App\Enums\Procurement\Vendors;

enum RfqMethod: string
{
    case Email = 'email';
    case Portal = 'portal';
    case Whatsapp = 'whatsapp';
    case Phone = 'phone';
    case Telegram = 'telegram';
    case SocialMedia = 'social_media';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case) => $case->value, self::cases());
    }

    /**
     * Normalize request input to a unique list of allowed string values, or null if empty / invalid.
     *
     * @return list<string>|null
     */
    public static function normalizeRequestInput(mixed $value): ?array
    {
        if ($value === null) {
            return null;
        }

        if (! is_array($value)) {
            return null;
        }

        $allowed = array_flip(self::values());
        $out = [];
        foreach ($value as $item) {
            if (! is_string($item) || $item === '') {
                continue;
            }
            if (isset($allowed[$item])) {
                $out[$item] = true;
            }
        }

        $list = array_keys($out);

        return $list === [] ? null : $list;
    }
}
