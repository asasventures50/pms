<?php

namespace App\Http\Controllers\Api\V1\Geo;

use App\Http\Controllers\Controller;
use App\Http\Requests\Geo\StoreCityRequest;
use App\Http\Requests\Geo\UpdateCityRequest;
use App\Http\Resources\Api\V1\Geo\CityResource;
use App\Models\Geo\City;
use App\Support\TableSort;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CityController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = max(1, min(100, (int) $request->query('per_page', 15)));
        $sort = TableSort::resolve($request, ['name_ar', 'name_en', 'created_at'], 'name_ar', 'asc');

        $query = City::query()->with('country')->withExists('vendorLocations');

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

        return CityResource::collection($query->paginate($perPage)->withQueryString())->additional([
            'statuses' => ['active', 'inactive'],
        ]);
    }

    public function show(City $city): CityResource
    {
        $city->load('country');
        $city->loadExists('vendorLocations');

        return new CityResource($city);
    }

    public function store(StoreCityRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['name'] = $data['name_en'];

        $city = City::query()->create($data);
        $city->load('country');

        return (new CityResource($city))
            ->additional(['message' => 'City created successfully.'])
            ->response()
            ->setStatusCode(201);
    }

    public function update(UpdateCityRequest $request, City $city): JsonResponse
    {
        $data = $request->validated();
        $data['name'] = $data['name_en'];

        $city->update($data);
        $city->refresh()->load('country');

        return (new CityResource($city))
            ->additional(['message' => 'City updated successfully.'])
            ->response();
    }

    public function destroy(City $city): JsonResponse
    {
        if ($city->vendorLocations()->exists()) {
            return response()->json([
                'message' => 'Cannot delete this city because it is used in vendor locations.',
            ], 422);
        }

        $city->delete();

        return response()->json([
            'message' => 'City deleted successfully.',
        ]);
    }
}
