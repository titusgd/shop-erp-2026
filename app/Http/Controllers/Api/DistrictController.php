<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Districts\StoreDistrictRequest;
use App\Http\Requests\Districts\UpdateDistrictRequest;
use App\Http\Resources\DistrictResource;
use App\Models\District;
use App\Services\DistrictService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class DistrictController extends Controller
{
    public function __construct(private DistrictService $districts) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $paginator = $this->districts->paginate([
            'search' => $request->string('search')->toString(),
            'city_id' => $request->filled('city_id') ? $request->integer('city_id') : null,
            'per_page' => $request->integer('per_page', 10),
            'active_only' => $request->boolean('active_only'),
        ]);

        return DistrictResource::collection($paginator);
    }

    public function store(StoreDistrictRequest $request): JsonResponse
    {
        $district = $this->districts->create($request->validated());

        return (new DistrictResource($district))
            ->response()
            ->setStatusCode(201);
    }

    public function show(District $district): DistrictResource
    {
        $district->load('city');

        return new DistrictResource($district);
    }

    public function update(UpdateDistrictRequest $request, District $district): DistrictResource
    {
        $district = $this->districts->update($district, $request->validated());

        return new DistrictResource($district);
    }

    public function destroy(District $district): Response
    {
        $this->districts->delete($district);

        return response()->noContent();
    }
}
