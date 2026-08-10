<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Products\StoreProductRequest;
use App\Http\Requests\Products\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class ProductController extends Controller
{
    public function __construct(private ProductService $products) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $paginator = $this->products->paginate([
            'search' => $request->string('search')->toString(),
            'product_category_id' => $request->filled('product_category_id')
                ? $request->integer('product_category_id')
                : null,
            'product_unit_id' => $request->filled('product_unit_id')
                ? $request->integer('product_unit_id')
                : null,
            'vendor_id' => $request->filled('vendor_id')
                ? $request->integer('vendor_id')
                : null,
            'per_page' => $request->integer('per_page', 10),
            'active_only' => $request->boolean('active_only'),
        ]);

        return ProductResource::collection($paginator);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->products->create($request->validated());

        return (new ProductResource($product))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Product $product): ProductResource
    {
        $product->load(['category', 'unit', 'vendors']);

        return new ProductResource($product);
    }

    public function update(UpdateProductRequest $request, Product $product): ProductResource
    {
        $product = $this->products->update($product, $request->validated());

        return new ProductResource($product);
    }

    public function destroy(Product $product): Response
    {
        $this->products->delete($product);

        return response()->noContent();
    }
}
