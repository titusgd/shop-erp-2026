<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\WarehouseTypes\StoreWarehouseTypeRequest;
use App\Http\Requests\WarehouseTypes\UpdateWarehouseTypeRequest;
use App\Http\Resources\WarehouseTypeResource;
use App\Models\WarehouseType;
use App\Services\WarehouseTypeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class WarehouseTypeController extends Controller
{
    public function __construct(private WarehouseTypeService $warehouseTypes) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $paginator = $this->warehouseTypes->paginate([
            'search' => $request->string('search')->toString(),
            'per_page' => $request->integer('per_page', 10),
            'active_only' => $request->boolean('active_only'),
        ]);

        return WarehouseTypeResource::collection($paginator);
    }

    public function store(StoreWarehouseTypeRequest $request): JsonResponse
    {
        $warehouseType = $this->warehouseTypes->create($request->validated());

        return (new WarehouseTypeResource($warehouseType))
            ->response()
            ->setStatusCode(201);
    }

    public function show(WarehouseType $warehouseType): WarehouseTypeResource
    {
        return new WarehouseTypeResource($warehouseType);
    }

    public function update(UpdateWarehouseTypeRequest $request, WarehouseType $warehouseType): WarehouseTypeResource
    {
        $warehouseType = $this->warehouseTypes->update($warehouseType, $request->validated());

        return new WarehouseTypeResource($warehouseType);
    }

    public function destroy(WarehouseType $warehouseType): Response
    {
        $this->warehouseTypes->delete($warehouseType);

        return response()->noContent();
    }
}
