<?php

namespace App\Services\Procurement\Categories;

use App\DataTransferObjects\Procurement\SubcategoryMoveImpact;
use App\DataTransferObjects\Procurement\SubcategoryMoveResult;
use App\Models\Procurement\ProcurementRequests\ProcurementRequestItem;
use App\Models\Procurement\Vendors\Category;
use App\Models\Procurement\Vendors\Subcategory;
use App\Models\Procurement\Vendors\VendorBrochure;
use App\Models\Procurement\Vendors\VendorCategory;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SubcategoryMoveService
{
    public function preview(Subcategory $subcategory, Category $target): SubcategoryMoveImpact
    {
        if ($target->id === $subcategory->category_id) {
            return new SubcategoryMoveImpact;
        }

        return new SubcategoryMoveImpact(
            vendorLinks: $this->countVendorLinks($subcategory),
            brochures: $this->countBrochures($subcategory),
            procurementRequests: $this->countProcurementRequests($subcategory),
            hasNameConflict: $this->hasNameConflict($subcategory, $target),
            hasSlugConflict: $this->hasSlugConflict($subcategory, $target),
        );
    }

    public function move(Subcategory $subcategory, Category $target): SubcategoryMoveResult
    {
        if ($target->id === $subcategory->category_id) {
            throw new InvalidArgumentException('Subcategory is already under the selected category.');
        }

        $impact = $this->preview($subcategory, $target);

        if ($impact->hasNameConflict) {
            throw new InvalidArgumentException(
                'The subcategory English name is already used in the target category.'
            );
        }

        if ($impact->hasSlugConflict) {
            throw new InvalidArgumentException(
                'The subcategory slug is already used in the target category.'
            );
        }

        $vendorLinksUpdated = 0;
        $brochuresUpdated = 0;
        $procurementRequestsUpdated = 0;

        DB::transaction(function () use (
            $subcategory,
            $target,
            &$vendorLinksUpdated,
            &$brochuresUpdated,
            &$procurementRequestsUpdated,
        ) {
            $vendorLinksUpdated = $this->reassignVendorLinks($subcategory, $target);
            $brochuresUpdated = VendorBrochure::query()
                ->where('subcategory_id', $subcategory->id)
                ->update(['category_id' => $target->id]);
            $procurementRequestsUpdated = ProcurementRequestItem::query()
                ->where('subcategory_id', $subcategory->id)
                ->update(['category_id' => $target->id]);

            $subcategory->update(['category_id' => $target->id]);
        });

        return new SubcategoryMoveResult(
            subcategoryNameEn: $subcategory->name_en,
            targetCategoryNameEn: $target->name_en,
            vendorLinksUpdated: $vendorLinksUpdated,
            brochuresUpdated: $brochuresUpdated,
            procurementRequestsUpdated: $procurementRequestsUpdated,
        );
    }

    private function countVendorLinks(Subcategory $subcategory): int
    {
        return VendorCategory::query()
            ->where('subcategory_id', $subcategory->id)
            ->count();
    }

    private function countBrochures(Subcategory $subcategory): int
    {
        return VendorBrochure::query()
            ->where('subcategory_id', $subcategory->id)
            ->count();
    }

    private function countProcurementRequests(Subcategory $subcategory): int
    {
        return ProcurementRequestItem::query()
            ->where('subcategory_id', $subcategory->id)
            ->count();
    }

    private function hasNameConflict(Subcategory $subcategory, Category $target): bool
    {
        return Subcategory::query()
            ->where('category_id', $target->id)
            ->where('name_en', $subcategory->name_en)
            ->whereKeyNot($subcategory->id)
            ->exists();
    }

    private function hasSlugConflict(Subcategory $subcategory, Category $target): bool
    {
        return Subcategory::query()
            ->where('category_id', $target->id)
            ->where('slug', $subcategory->slug)
            ->whereKeyNot($subcategory->id)
            ->exists();
    }

    private function reassignVendorLinks(Subcategory $subcategory, Category $target): int
    {
        $updated = 0;

        VendorCategory::query()
            ->where('subcategory_id', $subcategory->id)
            ->orderBy('id')
            ->get()
            ->each(function (VendorCategory $row) use ($subcategory, $target, &$updated) {
                $duplicate = VendorCategory::query()
                    ->where('vendor_id', $row->vendor_id)
                    ->where('category_id', $target->id)
                    ->where('subcategory_id', $subcategory->id)
                    ->whereKeyNot($row->id)
                    ->first();

                if ($duplicate) {
                    $row->delete();

                    return;
                }

                if ((int) $row->category_id === (int) $target->id) {
                    return;
                }

                $row->update(['category_id' => $target->id]);
                $updated++;
            });

        return $updated;
    }
}
