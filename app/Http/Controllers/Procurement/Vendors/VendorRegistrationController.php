<?php

namespace App\Http\Controllers\Procurement\Vendors;

use App\Enums\Procurement\Vendors\VendorStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Procurement\Vendors\StoreVendorRegistrationRequest;
use App\Models\Geo\City;
use App\Models\Geo\Country;
use App\Models\Procurement\Vendors\Category;
use App\Models\Procurement\Vendors\Vendor;
use App\Services\Procurement\Vendors\VendorPayloadResolver;
use App\Services\Procurement\Vendors\VendorPersistenceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VendorRegistrationController extends Controller
{
    public function __construct(
        protected VendorPersistenceService $persistence
    ) {}

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

        return view('procurement.vendors.register', [
            'categories' => $categories,
            'countries' => $countries,
            'defaultCountryId' => $syria?->id,
            'defaultCityId' => $damascus?->id,
        ]);
    }

    public function store(StoreVendorRegistrationRequest $request): RedirectResponse
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

        $validated['status'] = VendorStatus::PendingReview->value;

        VendorPayloadResolver::finalizeForStore($validated);

        $vendor = null;

        DB::transaction(function () use ($validated, $categories, $businessTypes, $brochureRows, $locations, $request, &$vendor) {
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
            ->route('vendor-registration.thanks')
            ->with('vendor_registration_name', $vendor->name);
    }

    public function thanks(): View
    {
        return view('procurement.vendors.register-thanks', [
            'vendorName' => session('vendor_registration_name'),
        ]);
    }
}
