<?php

namespace App\Support\Procurement\Rfqs;

final class PublicVendorQuotationExcelSchema
{
    public const SHEET_ITEMS = 'Items';

    public const SHEET_CONTACT = 'Contact';

    public const SHEET_INSTRUCTIONS = 'Instructions';

    /**
     * Stable English keys used for import (locale-independent).
     * Column order is fixed and must stay stable.
     *
     * @return list<string>
     */
    public static function itemKeys(): array
    {
        return [
            'rfq_item_id',
            'item',
            'description',
            'quantity',
            'unit',
            'quantity_quoted',
            'currency',
            'brand',
            'model',
            'unit_price',
            'discount',
            'installation',
            'delivery_charges',
            'remarks',
            'line_total',
        ];
    }

    /**
     * Localized human labels shown on row 1 of the Items sheet.
     *
     * @return list<string>
     */
    public static function itemDisplayHeadings(): array
    {
        $labels = [];
        foreach (self::itemKeys() as $key) {
            $labels[] = (string) __('vendor_quotation_invite.excel.item_columns.'.$key);
        }

        return $labels;
    }

    /**
     * @deprecated Use itemKeys()
     *
     * @return list<string>
     */
    public static function itemHeadings(): array
    {
        return array_values(array_filter(
            self::itemKeys(),
            fn (string $key) => $key !== 'line_total'
        ));
    }

    /**
     * @return list<string>
     */
    public static function lockedItemKeys(): array
    {
        return [
            'rfq_item_id',
            'item',
            'description',
            'quantity',
            'unit',
        ];
    }

    /**
     * @return list<string>
     */
    public static function fillableItemKeys(): array
    {
        return [
            'quantity_quoted',
            'currency',
            'brand',
            'model',
            'unit_price',
            'discount',
            'installation',
            'delivery_charges',
            'remarks',
        ];
    }

    /**
     * @return list<string>
     */
    public static function contactKeys(): array
    {
        return [
            'vendor_rep_name',
            'vendor_rep_email',
            'vendor_rep_phone',
            'notes',
        ];
    }
}
