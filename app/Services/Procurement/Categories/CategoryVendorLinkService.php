<?php

namespace App\Services\Procurement\Categories;

use App\Models\Procurement\Vendors\Subcategory;
use App\Models\Procurement\Vendors\VendorBrochure;
use App\Models\Procurement\Vendors\VendorCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CategoryVendorLinkService
{
    public function countMatchingBrochures(VendorCategory $link): int
    {
        return $this->brochuresMatchingLinkQuery($link)->count();
    }

    /**
     * @return array{brochures_updated: int}
     */
    public function reassign(
        VendorCategory $link,
        int $targetCategoryId,
        ?int $targetSubcategoryId,
        bool $updateBrochures = false,
    ): array {
        if ($targetSubcategoryId !== null) {
            $belongs = Subcategory::query()
                ->whereKey($targetSubcategoryId)
                ->where('category_id', $targetCategoryId)
                ->exists();

            if (! $belongs) {
                throw ValidationException::withMessages([
                    'target_subcategory_id' => 'The selected subcategory does not belong to the target category.',
                ]);
            }
        }

        if ((int) $link->category_id === $targetCategoryId
            && ($link->subcategory_id === null ? $targetSubcategoryId === null : (int) $link->subcategory_id === $targetSubcategoryId)
        ) {
            throw ValidationException::withMessages([
                'target_category_id' => 'Choose a different category or subcategory.',
            ]);
        }

        $duplicate = VendorCategory::query()
            ->where('vendor_id', $link->vendor_id)
            ->where('category_id', $targetCategoryId)
            ->when(
                $targetSubcategoryId === null,
                fn ($query) => $query->whereNull('subcategory_id'),
                fn ($query) => $query->where('subcategory_id', $targetSubcategoryId),
            )
            ->whereKeyNot($link->id)
            ->exists();

        if ($duplicate) {
            return $this->mergeIntoExistingLink($link, $targetCategoryId, $targetSubcategoryId, $updateBrochures);
        }

        $brochuresUpdated = 0;

        DB::transaction(function () use ($link, $targetCategoryId, $targetSubcategoryId, $updateBrochures, &$brochuresUpdated) {
            if ($updateBrochures) {
                $brochuresUpdated = $this->brochuresMatchingLinkQuery($link)->update([
                    'category_id' => $targetCategoryId,
                    'subcategory_id' => $targetSubcategoryId,
                ]);
            }

            $link->update([
                'category_id' => $targetCategoryId,
                'subcategory_id' => $targetSubcategoryId,
            ]);
        });

        return ['brochures_updated' => $brochuresUpdated, 'merged' => false, 'removed' => false];
    }

    /**
     * @return array{brochures_updated: int, merged: bool, removed: bool}
     */
    public function removeLink(VendorCategory $link, bool $updateBrochures = false): array
    {
        $brochuresUpdated = 0;

        DB::transaction(function () use ($link, $updateBrochures, &$brochuresUpdated) {
            if ($updateBrochures) {
                $brochuresUpdated = $this->brochuresMatchingLinkQuery($link)->update([
                    'category_id' => null,
                    'subcategory_id' => null,
                ]);
            }

            $link->delete();
        });

        return ['brochures_updated' => $brochuresUpdated, 'merged' => false, 'removed' => true];
    }

    /**
     * @return array{brochures_updated: int, merged: bool, removed: bool}
     */
    private function mergeIntoExistingLink(
        VendorCategory $link,
        int $targetCategoryId,
        ?int $targetSubcategoryId,
        bool $updateBrochures,
    ): array {
        $brochuresUpdated = 0;

        DB::transaction(function () use ($link, $targetCategoryId, $targetSubcategoryId, $updateBrochures, &$brochuresUpdated) {
            if ($updateBrochures) {
                $brochuresUpdated = $this->brochuresMatchingLinkQuery($link)->update([
                    'category_id' => $targetCategoryId,
                    'subcategory_id' => $targetSubcategoryId,
                ]);
            }

            $link->delete();
        });

        return ['brochures_updated' => $brochuresUpdated, 'merged' => true, 'removed' => false];
    }

    private function brochuresMatchingLinkQuery(VendorCategory $link)
    {
        return VendorBrochure::query()
            ->where('vendor_id', $link->vendor_id)
            ->where('category_id', $link->category_id)
            ->when(
                $link->subcategory_id === null,
                fn ($query) => $query->whereNull('subcategory_id'),
                fn ($query) => $query->where('subcategory_id', $link->subcategory_id),
            );
    }
}
