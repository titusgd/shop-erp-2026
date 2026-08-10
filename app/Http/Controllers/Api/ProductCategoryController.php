<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductCategories\StoreProductCategoryRequest;
use App\Http\Requests\ProductCategories\UpdateProductCategoryRequest;
use App\Http\Resources\ProductCategoryResource;
use App\Models\ProductCategory;
use App\Services\ProductCategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ProductCategoryController extends Controller
{
    public function __construct(private ProductCategoryService $productCategories) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $paginator = $this->productCategories->paginate([
            'search' => $request->string('search')->toString(),
            'per_page' => $request->integer('per_page', 10),
            'active_only' => $request->boolean('active_only'),
        ]);

        return ProductCategoryResource::collection($paginator);
    }

    public function store(StoreProductCategoryRequest $request): JsonResponse
    {
        $productCategory = $this->productCategories->create($request->validated());

        return (new ProductCategoryResource($productCategory))
            ->response()
            ->setStatusCode(201);
    }

    public function show(ProductCategory $productCategory): ProductCategoryResource
    {
        return new ProductCategoryResource($productCategory);
    }

    public function update(UpdateProductCategoryRequest $request, ProductCategory $productCategory): ProductCategoryResource
    {
        $productCategory = $this->productCategories->update($productCategory, $request->validated());

        return new ProductCategoryResource($productCategory);
    }

    public function destroy(ProductCategory $productCategory): Response
    {
        $this->productCategories->delete($productCategory);

        return response()->noContent();
    }
}
