<?php

namespace App\Http\Controllers\Procurement\Vendors;

use App\Http\Controllers\Controller;
use App\Http\Requests\Procurement\Vendors\StoreVendorRequest;
use App\Http\Requests\Procurement\Vendors\ImportVendorsRequest;
use App\Http\Requests\Procurement\Vendors\UpdateVendorRequest;
use App\Imports\Procurement\VendorsImport;
use App\Models\Geo\City;
use App\Models\Geo\Country;
use App\Models\Procurement\Vendors\Category;
use App\Models\Procurement\Vendors\Subcategory;
use App\Models\Procurement\Vendors\Vendor;
use App\Services\Procurement\Vendors\VendorCodeGenerator;
use App\Services\Procurement\Vendors\VendorPayloadResolver;
use App\Services\Procurement\Vendors\VendorPersistenceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class VendorWebController extends Controller
{
    public function __construct(
        protected VendorPersistenceService $persistence
    ) {}

    public function index(Request $request): View
    {
        $perPage = (int) $request->query('per_page', 15);
        $perPage = max(1, min($perPage, 100));

        $query = Vendor::query()
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

        $vendors = $query->paginate($perPage)->withQueryString();

        $filterCategories = Category::query()
            ->with(['subcategories' => fn ($q) => $q->orderBy('name_en')])
            ->orderBy('name_en')
            ->get();

        $subcategoriesByCategory = $filterCategories->mapWithKeys(fn (Category $c) => [
            $c->id => $c->subcategories->map(fn (Subcategory $s) => [
                'id' => $s->id,
                'name_ar' => $s->name_ar,
                'name_en' => $s->name_en,
            ])->values(),
        ]);

        $filterCountries = Country::query()
            ->active()
            ->with(['cities' => fn ($q) => $q->active()->orderBy('name_ar')])
            ->orderBy('name_ar')
            ->get();

        $citiesByCountry = $filterCountries->mapWithKeys(fn (Country $c) => [
            $c->id => $c->cities->map(fn (City $city) => [
                'id'      => $city->id,
                'name_ar' => $city->name_ar,
                'name_en' => $city->name_en,
            ])->values(),
        ]);

        return view('procurement.vendors.index', [
            'vendors'              => $vendors,
            'filterCategories'     => $filterCategories,
            'subcategoriesByCategory' => $subcategoriesByCategory,
            'filterCountries'      => $filterCountries,
            'citiesByCountry'      => $citiesByCountry,
        ]);
    }

    public function create(): View
    {
        $categories = Category::query()
            ->with(['subcategories' => fn ($q) => $q->orderBy('name_en')])
            ->orderBy('name_en')
            ->get();

        $countries = Country::query()
            ->active()
            ->with(['cities' => fn ($q) => $q->active()->orderBy('name_ar')->orderBy('name_en')])
            ->orderBy('name_ar')
            ->get();

        $syria = Country::query()->active()->where('iso_code', 'SY')->first();
        $damascus = $syria
            ? City::query()->active()->where('country_id', $syria->id)->where(function ($q) {
                $q->where('name_en', 'Damascus')->orWhere('name', 'Damascus');
            })->first()
            : null;

        $suggestedVendorCode = app(VendorCodeGenerator::class)->next();

        return view('procurement.vendors.create', [
            'categories' => $categories,
            'countries' => $countries,
            'defaultCountryId' => $syria?->id,
            'defaultCityId' => $damascus?->id,
            'suggestedVendorCode' => $suggestedVendorCode,
        ]);
    }

    public function importForm(): View
    {
        return view('procurement.vendors.import');
    }

    public function import(ImportVendorsRequest $request): RedirectResponse
    {
        /** @var VendorsImport $import */
        $import = app(VendorsImport::class);

        Excel::import($import, $request->file('file'));

        $result = $import->result;

        if ($result->failedRows > 0 || count($result->errors) > 0) {
            $detail = count($result->errors) > 10
                ? array_merge(array_slice($result->errors, 0, 10), ['Additional errors omitted.'])
                : $result->errors;

            return redirect()
                ->route('vendors.import.form')
                ->with('error', $result->summaryLine())
                ->with('import_errors', $detail);
        }

        return redirect()
            ->route('vendors.index')
            ->with('success', 'Import completed successfully.');
    }

    public function store(StoreVendorRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $categories = $validated['categories'] ?? null;
        $businessTypes = $validated['business_types'] ?? null;
        $brochureRows = $validated['brochure_rows'] ?? null;
        $locations = $validated['locations'] ?? null;

        unset(
            $validated['categories'],
            $validated['business_types'],
            $validated['brochures'],
            $validated['brochure_rows'],
            $validated['locations'],
        );

        VendorPayloadResolver::finalizeForStore($validated);

        $vendor = null;

        DB::transaction(function () use ($validated, $categories, $businessTypes, $brochureRows, $locations, $request, &$vendor) {
            $validated['created_by'] = $request->user()->id;
            $vendor = Vendor::query()->create($validated);

            $this->persistence->replaceLocations($vendor, $locations);
            $this->persistence->replaceCategories($vendor, $categories);
            $this->persistence->replaceBusinessTypes($vendor, $businessTypes);

            $this->persistence->appendBrochureRows($vendor, $brochureRows);
            $this->persistence->appendBrochures($vendor, $request);
        });

        if (! $vendor instanceof Vendor) {
            throw new \RuntimeException('Vendor could not be created.');
        }

        return redirect()
            ->route('vendors.show', $vendor)
            ->with('success', 'Vendor created successfully.');
    }

    public function show(Vendor $vendor): View
    {
        $vendor->load([
            'creator',
            'locations.country',
            'locations.city',
            'vendorCategories.category',
            'vendorCategories.subcategory',
            'businessTypes',
            'brochures.category',
            'brochures.subcategory',
        ]);

        return view('procurement.vendors.show', [
            'vendor' => $vendor,
        ]);
    }

    public function edit(Vendor $vendor): View
    {
        $vendor->load([
            'locations.country',
            'locations.city',
            'vendorCategories.category',
            'vendorCategories.subcategory',
            'businessTypes',
            'brochures.category',
            'brochures.subcategory',
        ]);

        $categories = Category::query()
            ->with(['subcategories' => fn ($q) => $q->orderBy('name_en')])
            ->orderBy('name_en')
            ->get();

        $countries = Country::query()
            ->active()
            ->with(['cities' => fn ($q) => $q->active()->orderBy('name_ar')->orderBy('name_en')])
            ->orderBy('name_ar')
            ->get();

        $syria = Country::query()->active()->where('iso_code', 'SY')->first();
        $damascus = $syria
            ? City::query()->active()->where('country_id', $syria->id)->where(function ($q) {
                $q->where('name_en', 'Damascus')->orWhere('name', 'Damascus');
            })->first()
            : null;

        return view('procurement.vendors.edit', [
            'vendor' => $vendor,
            'categories' => $categories,
            'countries' => $countries,
            'defaultCountryId' => $syria?->id,
            'defaultCityId' => $damascus?->id,
            'suggestedVendorCode' => null,
        ]);
    }

    public function update(UpdateVendorRequest $request, Vendor $vendor): RedirectResponse
    {
        $validated = $request->validated();

        $categoriesProvided = array_key_exists('categories', $validated) || $request->has('categories_sync');
        $businessTypesProvided = array_key_exists('business_types', $validated);
        $locationsProvided = array_key_exists('locations', $validated) || $request->has('locations_sync');

        $categories = $validated['categories'] ?? null;
        $businessTypes = $validated['business_types'] ?? null;
        $brochureRows = $validated['brochure_rows'] ?? null;
        $locations = $validated['locations'] ?? null;
        $removeBrochureIds = $validated['remove_brochure_ids'] ?? null;

        unset(
            $validated['categories'],
            $validated['business_types'],
            $validated['brochures'],
            $validated['brochure_rows'],
            $validated['locations'],
            $validated['remove_brochure_ids'],
        );

        VendorPayloadResolver::finalizeForUpdate($validated);

        DB::transaction(function () use ($vendor, $validated, $categoriesProvided, $businessTypesProvided, $locationsProvided, $categories, $businessTypes, $locations, $brochureRows, $removeBrochureIds, $request) {
            if (is_array($removeBrochureIds) && $removeBrochureIds !== []) {
                $this->persistence->removeBrochuresByIds($vendor, $removeBrochureIds);
            }

            $vendor->fill($validated);
            $vendor->save();

            if ($locationsProvided) {
                $this->persistence->replaceLocations($vendor, $locations);
            }

            if ($businessTypesProvided) {
                $this->persistence->replaceBusinessTypes($vendor, $businessTypes);
            }

            if ($categoriesProvided) {
                $this->persistence->replaceCategories($vendor, $categories);
            }

            $this->persistence->appendBrochureRows($vendor, $brochureRows);
            $this->persistence->appendBrochures($vendor, $request);
        });

        return redirect()
            ->route('vendors.show', $vendor)
            ->with('success', 'Vendor updated successfully.');
    }
}
