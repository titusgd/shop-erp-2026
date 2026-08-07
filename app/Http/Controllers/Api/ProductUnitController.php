<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductUnits\StoreProductUnitRequest;
use App\Http\Requests\ProductUnits\UpdateProductUnitRequest;
use App\Http\Resources\ProductUnitResource;
use App\Models\ProductUnit;
use App\Services\ProductUnitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ProductUnitController extends Controller
{
    public function __construct(private ProductUnitService $productUnits) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $paginator = $this->productUnits->paginate([
            'search' => $request->string('search')->toString(),
            'per_page' => $request->integer('per_page', 10),
        ]);

        return ProductUnitResource::collection($paginator);
    }

    public function store(StoreProductUnitRequest $request): JsonResponse
    {
        $productUnit = $this->productUnits->create($request->validated());

        return (new ProductUnitResource($productUnit))
            ->response()
            ->setStatusCode(201);
    }

    public function show(ProductUnit $productUnit): ProductUnitResource
    {
        return new ProductUnitResource($productUnit);
    }

    public function update(UpdateProductUnitRequest $request, ProductUnit $productUnit): ProductUnitResource
    {
        $productUnit = $this->productUnits->update($productUnit, $request->validated());

        return new ProductUnitResource($productUnit);
    }

    public function destroy(ProductUnit $productUnit): Response
    {
        $this->productUnits->delete($productUnit);

        return response()->noContent();
    }
}
