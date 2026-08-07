<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cities\StoreCityRequest;
use App\Http\Requests\Cities\UpdateCityRequest;
use App\Http\Resources\CityResource;
use App\Models\City;
use App\Services\CityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CityController extends Controller
{
    public function __construct(private CityService $cities) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $paginator = $this->cities->paginate([
            'search' => $request->string('search')->toString(),
            'per_page' => $request->integer('per_page', 10),
            'active_only' => $request->boolean('active_only'),
        ]);

        return CityResource::collection($paginator);
    }

    public function store(StoreCityRequest $request): JsonResponse
    {
        $city = $this->cities->create($request->validated());

        return (new CityResource($city))
            ->response()
            ->setStatusCode(201);
    }

    public function show(City $city): CityResource
    {
        return new CityResource($city);
    }

    public function update(UpdateCityRequest $request, City $city): CityResource
    {
        $city = $this->cities->update($city, $request->validated());

        return new CityResource($city);
    }

    public function destroy(City $city): Response
    {
        $this->cities->delete($city);

        return response()->noContent();
    }
}
