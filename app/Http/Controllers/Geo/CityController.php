<?php

namespace App\Http\Controllers\Geo;

use App\Http\Controllers\Controller;
use App\Http\Requests\Geo\StoreCityRequest;
use App\Http\Requests\Geo\UpdateCityRequest;
use App\Models\Geo\City;
use App\Models\Geo\Country;
use App\Support\TableSort;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\View\View;

class CityController extends Controller
{
    private function locationsRouteName(): string
    {
        if (Route::has('locations.index')) {
            return 'locations.index';
        }

        if (Route::has('countries.index')) {
            return 'countries.index';
        }

        return 'dashboard';
    }

    public function index(Request $request): View
    {
        $perPage = max(1, min(100, (int) $request->query('per_page', 15)));
        $sort = TableSort::resolve($request, ['name_ar', 'name_en', 'created_at'], 'name_ar', 'asc');

        $query = City::query()->with('country');

        if ($request->filled('q')) {
            $term = '%'.$request->string('q').'%';
            $query->where(function ($q) use ($term) {
                $q->where('name_ar', 'like', $term)
                    ->orWhere('name_en', 'like', $term);
            });
        }

        if ($request->filled('country_id')) {
            $query->where('country_id', (int) $request->input('country_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $query->orderBy($sort['column'], $sort['direction'])->orderBy('id');

        return view('geo.cities.index', [
            'cities' => $query->paginate($perPage)->appends($request->query()),
            'countries' => Country::query()->orderBy('name_ar')->orderBy('id')->get(),
            'sortColumn' => $sort['column'],
            'sortDirection' => $sort['direction'],
        ]);
    }

    public function create(Request $request): View
    {
        return view('geo.cities.create', [
            'city' => new City([
                'country_id' => $request->integer('country_id') ?: null,
                'status' => 'active',
            ]),
            'countries' => Country::query()->active()->orderBy('name_ar')->orderBy('id')->get(),
        ]);
    }

    public function store(StoreCityRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['name'] = $data['name_en'];

        City::query()->create($data);

        return redirect()
            ->route($this->locationsRouteName(), ['country_id' => $data['country_id']])
            ->with('success', 'City created successfully.');
    }

    public function edit(City $city): View
    {
        return view('geo.cities.edit', [
            'city' => $city,
            'countries' => Country::query()->orderBy('name_ar')->orderBy('id')->get(),
        ]);
    }

    public function update(UpdateCityRequest $request, City $city): RedirectResponse
    {
        $data = $request->validated();
        $data['name'] = $data['name_en'];

        $city->update($data);

        return redirect()
            ->route($this->locationsRouteName(), ['country_id' => $data['country_id']])
            ->with('success', 'City updated successfully.');
    }

    public function destroy(City $city): RedirectResponse
    {
        $countryId = $city->country_id;

        if ($city->vendorLocations()->exists()) {
            return redirect()
                ->route($this->locationsRouteName(), ['country_id' => $countryId])
                ->with('error', 'Cannot delete this city because it is used in vendor locations.');
        }

        $city->delete();

        return redirect()
            ->route($this->locationsRouteName(), ['country_id' => $countryId])
            ->with('success', 'City deleted successfully.');
    }
}
