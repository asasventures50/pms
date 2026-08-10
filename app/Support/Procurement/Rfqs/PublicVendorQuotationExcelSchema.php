<?php

namespace App\Support\Procurement\Rfqs;

final class PublicVendorQuotationExcelSchema
{
    public const SHEET_ITEMS = 'Items';

    public const SHEET_CONTACT = 'Contact';

    public const SHEET_INSTRUCTIONS = 'Instructions';

    /**
     * Stable English headings used for import (locale-independent).
     *
     * @return list<string>
     */
    public static function itemHeadings(): array
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
        ];
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
