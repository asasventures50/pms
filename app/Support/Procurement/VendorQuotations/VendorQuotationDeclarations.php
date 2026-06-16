<?php

namespace App\Support\Procurement\VendorQuotations;

final class VendorQuotationDeclarations
{
    /**
     * @return array<string, string>
     */
    public static function all(): array
    {
        return [
            'prices_include_costs' => 'I confirm that all prices quoted include all applicable costs unless clearly stated otherwise.',
            'deviations_declared' => 'I confirm that all deviations or alternatives have been declared in the quotation.',
            'valid_until_stated' => 'I confirm that the quotation is valid until the date stated above.',
            'information_accurate' => 'I confirm that the information submitted is accurate and complete.',
            'accept_terms' => 'I confirm acceptance of the RFQ terms and conditions, unless exceptions are declared.',
            'no_hidden_costs' => 'I confirm that there are no hidden costs or undisclosed conditions.',
            'no_conflict_of_interest' => 'I confirm that there is no conflict of interest, collusion, or improper arrangement related to this quotation.',
        ];
    }

    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return array_keys(self::all());
    }

    /**
     * @param  list<string>|null  $confirmed
     * @return list<string>
     */
    public static function normalize(?array $confirmed): array
    {
        if ($confirmed === null || $confirmed === []) {
            return [];
        }

        $allowed = array_flip(self::keys());
        $selected = [];

        foreach ($confirmed as $key) {
            $key = trim((string) $key);
            if ($key !== '' && isset($allowed[$key])) {
                $selected[$key] = true;
            }
        }

        return array_keys($selected);
    }
}
