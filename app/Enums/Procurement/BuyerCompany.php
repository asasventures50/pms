<?php

namespace App\Enums\Procurement;

/**
 * Buyer (our company) on RFQ, vendor quotation, and purchase order documents.
 * Edit the constants below — values are snapshotted when each document is saved.
 */
final class BuyerCompany
{
    public const NAME = 'fadi';

    public const ADDRESS = 'fadi';

    public const PHONE = '0938';

    public const EMAIL = 'fadi@gmail';

    public const WEBSITE = 'asdfghjk';

    /**
     * @return array{name: string|null, phone: string|null, email: string|null, address: string|null, website: string|null}
     */
    public static function defaults(): array
    {
        return [
            'name' => self::nullable(self::NAME),
            'phone' => self::nullable(self::PHONE),
            'email' => self::nullable(self::EMAIL),
            'address' => self::nullable(self::ADDRESS),
            'website' => self::nullable(self::WEBSITE),
        ];
    }

    public static function hasConfiguredDefaults(): bool
    {
        $defaults = self::defaults();

        return $defaults['name'] !== null
            || $defaults['address'] !== null
            || $defaults['phone'] !== null
            || $defaults['email'] !== null
            || $defaults['website'] !== null;
    }

    /**
     * @param  object{company_name?: mixed, company_phone?: mixed, company_email?: mixed, company_address?: mixed, company_website?: mixed}|null  $document
     * @return array{name: string|null, phone: string|null, email: string|null, address: string|null, website: string|null}
     */
    public static function forDisplay(?object $document = null): array
    {
        $defaults = self::defaults();

        if ($document === null) {
            return $defaults;
        }

        return [
            'name' => self::field($document->company_name ?? null, $defaults['name']),
            'phone' => self::field($document->company_phone ?? null, $defaults['phone']),
            'email' => self::field($document->company_email ?? null, $defaults['email']),
            'address' => self::field($document->company_address ?? null, $defaults['address']),
            'website' => self::field($document->company_website ?? null, $defaults['website']),
        ];
    }

    /**
     * @param  array<string, mixed>  $header
     */
    public static function applyToHeader(array &$header): void
    {
        $defaults = self::defaults();

        foreach ([
            'company_name' => 'name',
            'company_phone' => 'phone',
            'company_email' => 'email',
            'company_address' => 'address',
            'company_website' => 'website',
        ] as $column => $key) {
            $header[$column] = $defaults[$key];
        }
    }

    private static function field(mixed $stored, ?string $fallback): ?string
    {
        $text = trim((string) ($stored ?? ''));

        return $text !== '' ? $text : $fallback;
    }

    private static function nullable(string $value): ?string
    {
        $text = trim($value);

        return $text !== '' ? $text : null;
    }
}
