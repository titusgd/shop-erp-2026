<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PurchaseOrders\StorePurchaseOrderRequest;
use App\Http\Requests\PurchaseOrders\UpdatePurchaseOrderRequest;
use App\Http\Resources\PurchaseOrderResource;
use App\Models\PurchaseOrder;
use App\Services\PurchaseOrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class PurchaseOrderController extends Controller
{
    public function __construct(private PurchaseOrderService $purchaseOrders) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $paginator = $this->purchaseOrders->paginate([
            'search' => $request->string('search')->toString(),
            'vendor_id' => $request->filled('vendor_id')
                ? $request->integer('vendor_id')
                : null,
            'warehouse_id' => $request->filled('warehouse_id')
                ? $request->integer('warehouse_id')
                : null,
            'status' => $request->string('status')->toString(),
            'per_page' => $request->integer('per_page', 10),
        ]);

        return PurchaseOrderResource::collection($paginator);
    }

    public function store(StorePurchaseOrderRequest $request): JsonResponse
    {
        $order = $this->purchaseOrders->create($request->validated());

        return (new PurchaseOrderResource($order))
            ->response()
            ->setStatusCode(201);
    }

    public function show(PurchaseOrder $purchaseOrder): PurchaseOrderResource
    {
        $purchaseOrder->load(['vendor', 'warehouse', 'items.product.unit']);

        return new PurchaseOrderResource($purchaseOrder);
    }

    public function update(UpdatePurchaseOrderRequest $request, PurchaseOrder $purchaseOrder): PurchaseOrderResource
    {
        $order = $this->purchaseOrders->update($purchaseOrder, $request->validated());

        return new PurchaseOrderResource($order);
    }

    public function destroy(PurchaseOrder $purchaseOrder): Response
    {
        $this->purchaseOrders->delete($purchaseOrder);

        return response()->noContent();
    }
}
