<?php

namespace App\Enums\Procurement\PurchaseOrders;

use App\Models\Procurement\PurchaseOrders\PurchaseOrder;

/**
 * Buyer (our company) details on purchase orders.
 * Edit the constants below — values are applied automatically on the web form and saved on each PO.
 */
final class BuyerCompany
{
    public const NAME = 'fadi';

    public const ADDRESS = 'fadi';

    public const PHONE = '0938';

    public const EMAIL = 'fadi@gmail';

    /**
     * @return array{name: string|null, address: string|null, phone: string|null, email: string|null}
     */
    public static function defaults(): array
    {
        return [
            'name' => self::nullable(self::NAME),
            'address' => self::nullable(self::ADDRESS),
            'phone' => self::nullable(self::PHONE),
            'email' => self::nullable(self::EMAIL),
        ];
    }

    public static function hasConfiguredDefaults(): bool
    {
        $defaults = self::defaults();

        return $defaults['name'] !== null
            || $defaults['address'] !== null
            || $defaults['phone'] !== null
            || $defaults['email'] !== null;
    }

    /**
     * Values for display on form/show: saved PO snapshot, else BuyerCompany constants.
     *
     * @return array{name: string|null, address: string|null, phone: string|null, email: string|null}
     */
    public static function forDisplay(?PurchaseOrder $purchaseOrder = null): array
    {
        $defaults = self::defaults();

        if ($purchaseOrder === null) {
            return $defaults;
        }

        return [
            'name' => self::field($purchaseOrder->company_name, $defaults['name']),
            'address' => self::field($purchaseOrder->company_address, $defaults['address']),
            'phone' => self::field($purchaseOrder->company_phone, $defaults['phone']),
            'email' => self::field($purchaseOrder->company_email, $defaults['email']),
        ];
    }

    /**
     * Always snapshot current organization details onto the PO record.
     *
     * @param  array<string, mixed>  $header
     */
    public static function applyToHeader(array &$header): void
    {
        $defaults = self::defaults();

        foreach ([
            'company_name' => 'name',
            'company_address' => 'address',
            'company_phone' => 'phone',
            'company_email' => 'email',
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
