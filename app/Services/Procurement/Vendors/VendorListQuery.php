<?php

namespace App\Services\Procurement\Vendors;

use App\Models\Geo\City;
use App\Models\Procurement\Vendors\Subcategory;
use App\Models\Procurement\Vendors\Vendor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class VendorListQuery
{
    /**
     * @return Builder<Vendor>
     */
    public function base(): Builder
    {
        return Vendor::query()
            ->with([
                'creator',
                'vendorCategories.category',
                'vendorCategories.subcategory',
                'businessTypes',
                'locations.country',
                'locations.city',
            ])
            ->withCount('brochures')
            ->latest();
    }

    /**
     * @return Builder<Vendor>
     */
    public function filtered(Request $request): Builder
    {
        $query = $this->base();

        if ($request->filled('q')) {
            $term = '%'.$request->string('q').'%';
            $query->where(function ($q) use ($term) {
                $q->where('vendor_code', 'like', $term)
                    ->orWhere('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('phone', 'like', $term);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('language')) {
            $query->where('language', $request->string('language'));
        }

        if ($request->filled('category_id')) {
            $categoryId = $request->integer('category_id');

            $subcategoryIds = collect((array) $request->input('subcategory_ids', []))
                ->map(fn ($v) => (int) $v)
                ->filter(fn (int $id) => $id > 0)
                ->unique()
                ->values()
                ->all();

            if (count($subcategoryIds) > 0) {
                $validSubcategoryIds = Subcategory::query()
                    ->where('category_id', $categoryId)
                    ->whereIn('id', $subcategoryIds)
                    ->pluck('id')
                    ->all();

                if (count($validSubcategoryIds) > 0) {
                    $query->whereHas('vendorCategories', function ($q) use ($categoryId, $validSubcategoryIds) {
                        $q->where('category_id', $categoryId)
                            ->whereIn('subcategory_id', $validSubcategoryIds);
                    });
                } else {
                    $query->whereHas('vendorCategories', function ($q) use ($categoryId) {
                        $q->where('category_id', $categoryId);
                    });
                }
            } else {
                $query->whereHas('vendorCategories', function ($q) use ($categoryId) {
                    $q->where('category_id', $categoryId);
                });
            }
        }

        if ($request->filled('company_type')) {
            $query->where('company_type', $request->string('company_type'));
        }

        if ($request->filled('coverage_type')) {
            $query->where('coverage_type', $request->string('coverage_type'));
        }

        if ($request->filled('business_type')) {
            $query->whereHas('businessTypes', function ($q) use ($request) {
                $q->where('business_type', $request->string('business_type'));
            });
        }

        if ($request->filled('country_id')) {
            $countryId = $request->integer('country_id');

            $cityIds = collect((array) $request->input('city_ids', []))
                ->map(fn ($v) => (int) $v)
                ->filter(fn (int $id) => $id > 0)
                ->unique()
                ->values()
                ->all();

            if (count($cityIds) > 0) {
                $validCityIds = City::query()
                    ->where('country_id', $countryId)
                    ->whereIn('id', $cityIds)
                    ->pluck('id')
                    ->all();

                if (count($validCityIds) > 0) {
                    $query->whereHas('locations', function ($q) use ($countryId, $validCityIds) {
                        $q->where('country_id', $countryId)
                            ->whereIn('city_id', $validCityIds);
                    });
                } else {
                    $query->whereHas('locations', function ($q) use ($countryId) {
                        $q->where('country_id', $countryId);
                    });
                }
            } else {
                $query->whereHas('locations', function ($q) use ($countryId) {
                    $q->where('country_id', $countryId);
                });
            }
        }

        return $query;
    }
}
