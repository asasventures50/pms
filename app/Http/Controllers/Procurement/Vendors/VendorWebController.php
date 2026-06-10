<?php

namespace App\Http\Controllers\Procurement\Vendors;

use App\Exports\Procurement\VendorsExport;
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
use App\Services\Procurement\Vendors\VendorListQuery;
use App\Services\Procurement\Vendors\VendorPayloadResolver;
use App\Services\Procurement\Vendors\VendorPersistenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class VendorWebController extends Controller
{
    public function __construct(
        protected VendorPersistenceService $persistence,
        protected VendorListQuery $listQuery
    ) {}

    public function index(Request $request): View
    {
        $perPage = (int) $request->query('per_page', 15);
        $perPage = max(1, min($perPage, 100));

        $vendors = $this->listQuery->filtered($request)->paginate($perPage)->withQueryString();

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

    public function searchForSelect(Request $request): JsonResponse
    {
        $term = trim($request->string('q'));

        $query = Vendor::query()->orderBy('name');

        if ($term !== '') {
            $like = '%'.$term.'%';
            $query->where(function ($builder) use ($like) {
                $builder->where('name', 'like', $like)
                    ->orWhere('vendor_code', 'like', $like);
            });
        }

        return response()->json(
            $query
                ->limit($term === '' ? 500 : 25)
                ->get(['id', 'vendor_code', 'name'])
                ->map(fn (Vendor $vendor) => [
                    'id' => $vendor->id,
                    'label' => trim($vendor->vendor_code.' — '.$vendor->name),
                ])
                ->values()
                ->all()
        );
    }

    public function export(Request $request): BinaryFileResponse
    {
        $filename = 'vendors-export-'.now()->format('Y-m-d_His').'.xlsx';

        return Excel::download(
            new VendorsExport($this->listQuery->filtered($request)),
            $filename
        );
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
            $validated['nda'],
        );

        VendorPayloadResolver::finalizeForStore($validated);

        $vendor = null;

        DB::transaction(function () use ($validated, $categories, $businessTypes, $brochureRows, $locations, $request, &$vendor) {
            $validated['created_by'] = $request->user()->id;
            $vendor = Vendor::query()->create($validated);

            $this->persistence->replaceLocations($vendor, $locations);
            $this->persistence->replaceCategories($vendor, $categories);
            $this->persistence->replaceBusinessTypes($vendor, $businessTypes);

            $this->persistence->storeNda($vendor, $request->file('nda'));
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

        $removeNda = filter_var($request->input('remove_nda'), FILTER_VALIDATE_BOOLEAN);

        unset(
            $validated['categories'],
            $validated['business_types'],
            $validated['brochures'],
            $validated['brochure_rows'],
            $validated['locations'],
            $validated['remove_brochure_ids'],
            $validated['nda'],
            $validated['remove_nda'],
        );

        VendorPayloadResolver::finalizeForUpdate($validated);

        DB::transaction(function () use ($vendor, $validated, $categoriesProvided, $businessTypesProvided, $locationsProvided, $categories, $businessTypes, $locations, $brochureRows, $removeBrochureIds, $removeNda, $request) {
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

            if ($removeNda) {
                $this->persistence->removeNda($vendor);
            }

            $this->persistence->storeNda($vendor, $request->file('nda'));
            $this->persistence->appendBrochureRows($vendor, $brochureRows);
            $this->persistence->appendBrochures($vendor, $request);
        });

        return redirect()
            ->route('vendors.show', $vendor)
            ->with('success', 'Vendor updated successfully.');
    }
}
