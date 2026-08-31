<?php

namespace App\Http\Controllers\Api\V1\Geo;

use App\Http\Controllers\Controller;
use App\Http\Requests\Geo\StoreCountryRequest;
use App\Http\Requests\Geo\UpdateCountryRequest;
use App\Http\Resources\Api\V1\Geo\CountryResource;
use App\Models\Geo\Country;
use App\Support\TableSort;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CountryController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
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

        return CountryResource::collection($query->paginate($perPage)->withQueryString())->additional([
            'statuses' => ['active', 'inactive'],
        ]);
    }

    public function show(Country $country): CountryResource
    {
        $country->loadCount('cities');
        $country->loadExists('vendorLocations');
        $country->load(['cities' => fn ($q) => $q->withExists('vendorLocations')->orderBy('name_ar')->orderBy('id')]);

        return new CountryResource($country);
    }

    public function store(StoreCountryRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['name'] = $data['name_en'];

        $country = Country::query()->create($data);

        return (new CountryResource($country))
            ->additional(['message' => 'Country created successfully.'])
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateCountryRequest $request, Country $country): JsonResponse
    {
        $data = $request->validated();
        $data['name'] = $data['name_en'];

        $country->update($data);

        return (new CountryResource($country->fresh()))
            ->additional(['message' => 'Country updated successfully.'])
            ->response();
    }

    public function destroy(Country $country): JsonResponse
    {
        $hasCities = $country->cities()->exists();
        $usedInVendorLocations = $country->vendorLocations()->exists();

        if ($hasCities || $usedInVendorLocations) {
            return response()->json([
                'message' => 'Cannot delete this country because it is used in vendor locations or has cities.',
            ], 422);
        }

        $country->delete();

        return response()->json([
            'message' => 'Country deleted successfully.',
        ]);
    }
}
