<?php

namespace App\Services\Procurement\Vendors;

use App\Models\Procurement\Vendors\Vendor;
use Illuminate\Support\Collection;

/**
 * Finds likely duplicate vendor groups for review (soft-deleted excluded).
 */
class VendorDuplicateFinder
{
    public const MATCH_PHONE = 'phone';

    public const MATCH_EMAIL = 'email';

    public const MATCH_NAME = 'name';

    /**
     * @return list<array{match_type: string, match_key: string, match_label: string, vendors: Collection<int, Vendor>}>
     */
    public function groups(?string $matchType = null): array
    {
        $matchType = $matchType ?: self::MATCH_PHONE;
        if (! in_array($matchType, [self::MATCH_PHONE, self::MATCH_EMAIL, self::MATCH_NAME], true)) {
            $matchType = self::MATCH_PHONE;
        }

        $vendors = Vendor::query()
            ->with('creator')
            ->orderBy('id')
            ->get([
                'id',
                'vendor_code',
                'name',
                'phone',
                'whatsapp',
                'email',
                'status',
                'created_by',
                'created_at',
            ]);

        return match ($matchType) {
            self::MATCH_EMAIL => $this->groupByEmail($vendors),
            self::MATCH_NAME => $this->groupByNormalizedName($vendors),
            default => $this->groupByPhone($vendors),
        };
    }

    /**
     * @param  Collection<int, Vendor>  $vendors
     * @return list<array{match_type: string, match_key: string, match_label: string, vendors: Collection<int, Vendor>}>
     */
    private function groupByPhone(Collection $vendors): array
    {
        $buckets = [];

        foreach ($vendors as $vendor) {
            foreach (['phone', 'whatsapp'] as $field) {
                $key = $this->normalizePhone((string) $vendor->{$field});
                if ($key === '') {
                    continue;
                }
                $buckets[$key][$vendor->id] = $vendor;
            }
        }

        return $this->toGroups(self::MATCH_PHONE, $buckets, fn (string $key) => 'Phone ~ '.$key);
    }

    /**
     * @param  Collection<int, Vendor>  $vendors
     * @return list<array{match_type: string, match_key: string, match_label: string, vendors: Collection<int, Vendor>}>
     */
    private function groupByEmail(Collection $vendors): array
    {
        $buckets = [];

        foreach ($vendors as $vendor) {
            $key = $this->normalizeEmail((string) $vendor->email);
            if ($key === '') {
                continue;
            }
            $buckets[$key][$vendor->id] = $vendor;
        }

        return $this->toGroups(self::MATCH_EMAIL, $buckets, fn (string $key) => 'Email: '.$key);
    }

    /**
     * @param  Collection<int, Vendor>  $vendors
     * @return list<array{match_type: string, match_key: string, match_label: string, vendors: Collection<int, Vendor>}>
     */
    private function groupByNormalizedName(Collection $vendors): array
    {
        $buckets = [];

        foreach ($vendors as $vendor) {
            $key = $this->normalizeName((string) $vendor->name);
            if ($key === '') {
                continue;
            }
            $buckets[$key][$vendor->id] = $vendor;
        }

        return $this->toGroups(self::MATCH_NAME, $buckets, fn (string $key) => 'Name ~ '.$key);
    }

    /**
     * @param  array<string, array<int, Vendor>>  $buckets
     * @param  callable(string): string  $label
     * @return list<array{match_type: string, match_key: string, match_label: string, vendors: Collection<int, Vendor>}>
     */
    private function toGroups(string $matchType, array $buckets, callable $label): array
    {
        $groups = [];

        foreach ($buckets as $key => $map) {
            if (count($map) < 2) {
                continue;
            }

            $collection = collect(array_values($map))->sortBy('id')->values();

            $groups[] = [
                'match_type' => $matchType,
                'match_key' => $key,
                'match_label' => $label($key),
                'vendors' => $collection,
            ];
        }

        usort($groups, function (array $a, array $b): int {
            $size = $b['vendors']->count() <=> $a['vendors']->count();
            if ($size !== 0) {
                return $size;
            }

            return strcmp($a['match_key'], $b['match_key']);
        });

        return $groups;
    }

    public function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if ($digits === '' || strlen($digits) < 7) {
            return '';
        }

        return strlen($digits) > 9 ? substr($digits, -9) : $digits;
    }

    public function normalizeEmail(string $email): string
    {
        $email = mb_strtolower(trim($email));
        if ($email === '' || ! str_contains($email, '@')) {
            return '';
        }

        return $email;
    }

    public function normalizeName(string $name): string
    {
        $name = mb_strtolower(trim($name));
        $name = preg_replace('/[^\p{L}\p{N}\s]+/u', ' ', $name) ?? '';
        $name = preg_replace('/\s+/u', ' ', $name) ?? '';
        $name = preg_replace(
            '/\b(llc|ltd|co|company|corp|inc|شركة|مؤسسة|مكتب|تجارة|للتجارة|وشركاه)\b/u',
            ' ',
            $name
        ) ?? '';

        return preg_replace('/\s+/u', ' ', trim($name)) ?? '';
    }
}
