<?php

namespace App\Services\Procurement\PurchaseOrders;

use App\Models\Procurement\Vendors\Vendor;
use Illuminate\Support\Str;

class VendorPurchaseOrderSnapshot
{
    /**
     * @return array<string, string|null>
     */
    public static function fromVendor(Vendor $vendor): array
    {
        $vendor->loadMissing([
            'primaryLocation',
            'businessTypes',
            'vendorCategories.category',
            'vendorCategories.subcategory',
        ]);

        $location = $vendor->primaryLocation;
        $email = $vendor->primary_contact_email ?: $vendor->email;

        return [
            'vendor_company_name' => $vendor->name,
            'vendor_email' => $email ?: null,
            'vendor_phone' => $vendor->phone ?: $location?->phone,
            'vendor_whatsapp' => $vendor->whatsapp ?: $location?->whatsapp,
            'vendor_primary_contact_position' => $vendor->primary_contact_position,
            'vendor_classification' => self::classificationSummary($vendor),
            'payment_terms' => $vendor->payment_terms,
            'currency_code' => $vendor->currency_code ? strtoupper($vendor->currency_code) : null,
        ];
    }

    public static function classificationSummary(Vendor $vendor): ?string
    {
        $parts = array_filter([
            self::enumLabel($vendor->company_type),
            self::enumLabel($vendor->coverage_type),
            self::businessTypesSummary($vendor),
            self::categoriesSummary($vendor),
            filled($vendor->tax_number) ? 'Tax: '.$vendor->tax_number : null,
            filled($vendor->registration_number) ? 'Reg: '.$vendor->registration_number : null,
            filled($vendor->license_number) ? 'License: '.$vendor->license_number : null,
        ]);

        return $parts === [] ? null : implode(' · ', $parts);
    }

    private static function enumValue(mixed $value): ?string
    {
        if ($value instanceof \BackedEnum) {
            return $value->value;
        }

        $string = trim((string) ($value ?? ''));

        return $string !== '' ? $string : null;
    }

    private static function enumLabel(mixed $value): ?string
    {
        $raw = self::enumValue($value);

        return $raw !== null ? Str::headline(str_replace('_', ' ', $raw)) : null;
    }

    private static function businessTypesSummary(Vendor $vendor): ?string
    {
        $labels = $vendor->businessTypes
            ->map(fn ($row) => self::enumLabel($row->business_type))
            ->filter()
            ->values();

        return $labels->isEmpty() ? null : $labels->join(', ');
    }

    private static function categoriesSummary(Vendor $vendor): ?string
    {
        $groups = [];

        foreach ($vendor->vendorCategories->sortBy('id') as $vendorCategory) {
            $categoryId = (string) $vendorCategory->category_id;

            if (! isset($groups[$categoryId])) {
                $groups[$categoryId] = [
                    'label' => self::catalogLabel($vendorCategory->category),
                    'subs' => [],
                    'category_only' => false,
                ];
            }

            if ($vendorCategory->subcategory_id === null) {
                $groups[$categoryId]['category_only'] = true;
            } elseif ($vendorCategory->subcategory) {
                $groups[$categoryId]['subs'][] = self::catalogLabel($vendorCategory->subcategory);
            }
        }

        if ($groups === []) {
            return null;
        }

        $parts = [];

        foreach ($groups as $group) {
            $label = $group['label'] ?: '—';
            $subs = array_values(array_unique(array_filter($group['subs'])));

            if ($subs !== []) {
                $parts[] = $label.' ('.implode(', ', $subs).')';
            } elseif ($group['category_only']) {
                $parts[] = $label;
            } else {
                $parts[] = $label;
            }
        }

        return implode('; ', $parts);
    }

    private static function catalogLabel(?object $model): string
    {
        if (! $model) {
            return '';
        }

        $ar = trim((string) ($model->name_ar ?? ''));
        $en = trim((string) ($model->name_en ?? ''));

        if ($ar && $en) {
            return $ar.' — '.$en;
        }

        return $ar ?: $en;
    }
}
