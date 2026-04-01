<?php

namespace App\Http\Controllers\Geo;

use App\Http\Controllers\Controller;
use App\Http\Requests\Geo\StoreCountryRequest;
use App\Http\Requests\Geo\UpdateCountryRequest;
use App\Models\Geo\Country;
use App\Support\TableSort;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class CountryController extends Controller
{
    private function locationsRouteName(): string
    {
        return Route::has('locations.index') ? 'locations.index' : 'countries.index';
    }

    public function index(Request $request): View
    {
        $perPage = max(1, min(100, (int) $request->query('per_page', 15)));
        $sort = TableSort::resolve($request, ['name_ar', 'name_en', 'created_at'], 'name_ar', 'asc');

        $query = Country::query()
            ->withCount('cities')
            ->withExists('vendorLocations')
            ->with(['cities' => fn ($q) => $q->withExists('vendorLocations')->orderBy('name_ar')->orderBy('id')]);

        if ($request->filled('q')) {
            $term = '%'.$request->string('q').'%';
            $query->where(function ($q) use ($term) {
                $q->where('name_ar', 'like', $term)
                    ->orWhere('name_en', 'like', $term)
                    ->orWhere('iso_code', 'like', $term);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $query->orderBy($sort['column'], $sort['direction'])->orderBy('id');

        return view('geo.countries.index', [
            'countries' => $query->paginate($perPage)->appends($request->query()),
            'sortColumn' => $sort['column'],
            'sortDirection' => $sort['direction'],
        ]);
    }

    public function create(): View
    {
        return view('geo.countries.create', [
            'country' => new Country(['status' => 'active']),
        ]);
    }

    public function store(StoreCountryRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['name'] = $data['name_en'];

        Country::query()->create($data);

        return redirect()
            ->route($this->locationsRouteName())
            ->with('success', 'Country created successfully.');
    }

    public function edit(Country $country): View
    {
        $country->load(['cities' => fn ($q) => $q->orderBy('name_ar')->orderBy('id')]);

        return view('geo.countries.edit', [
            'country' => $country,
        ]);
    }

    public function update(UpdateCountryRequest $request, Country $country): RedirectResponse
    {
        $data = $request->validated();
        $data['name'] = $data['name_en'];

        $country->update($data);

        return redirect()
            ->route($this->locationsRouteName(), ['country_id' => $country->id])
            ->with('success', 'Country updated successfully.');
    }

    public function destroy(Country $country): RedirectResponse
    {
        $hasCities = $country->cities()->exists();
        $usedInVendorLocations = $country->vendorLocations()->exists();

        if ($hasCities || $usedInVendorLocations) {
            return redirect()
                ->route($this->locationsRouteName())
                ->with('error', 'Cannot delete this country because it is used in vendor locations or has cities.');
        }

        $country->delete();

        return redirect()
            ->route($this->locationsRouteName())
            ->with('success', 'Country deleted successfully.');
    }
}
