<?php

namespace App\Http\Controllers\Procurement\Vendors;

use App\Http\Controllers\Controller;
use App\Http\Requests\Procurement\Vendors\StoreVendorRequest;
use App\Http\Requests\Procurement\Vendors\UpdateVendorRequest;
use App\Models\Procurement\Vendors\Vendor;
use App\Services\Procurement\Vendors\VendorPayloadResolver;
use App\Services\Procurement\Vendors\VendorPersistenceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VendorController extends Controller
{
    public function __construct(
        protected VendorPersistenceService $persistence
    ) {}

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->query('per_page', 15);
        $perPage = max(1, min($perPage, 100));

        $query = Vendor::query()
            ->with(['categories', 'businessTypes', 'locations.country', 'locations.city'])
            ->withCount('brochures')
            ->latest();

        if ($request->filled('language')) {
            $query->where('language', $request->string('language'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return response()->json($query->paginate($perPage));
    }

    public function store(StoreVendorRequest $request): JsonResponse
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

        $vendor->load(['categories', 'subcategories', 'businessTypes', 'brochures', 'locations.country', 'locations.city']);

        return response()->json($vendor, 201);
    }

    public function show(Vendor $vendor): JsonResponse
    {
        $vendor->load(['categories', 'subcategories', 'businessTypes', 'brochures.category', 'brochures.subcategory', 'locations.country', 'locations.city']);

        return response()->json($vendor);
    }

    public function update(UpdateVendorRequest $request, Vendor $vendor): JsonResponse
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

        $vendor->load(['categories', 'subcategories', 'businessTypes', 'brochures.category', 'brochures.subcategory', 'locations.country', 'locations.city']);

        return response()->json($vendor);
    }
}
