<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PurchaseRequisitions\StorePurchaseRequisitionRequest;
use App\Http\Requests\PurchaseRequisitions\UpdatePurchaseRequisitionRequest;
use App\Http\Resources\PurchaseRequisitionResource;
use App\Models\PurchaseRequisition;
use App\Services\PurchaseRequisitionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class PurchaseRequisitionController extends Controller
{
    public function __construct(private PurchaseRequisitionService $purchaseRequisitions) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $paginator = $this->purchaseRequisitions->paginate([
            'search' => $request->string('search')->toString(),
            'requester_id' => $request->filled('requester_id')
                ? $request->integer('requester_id')
                : null,
            'warehouse_id' => $request->filled('warehouse_id')
                ? $request->integer('warehouse_id')
                : null,
            'status' => $request->string('status')->toString(),
            'per_page' => $request->integer('per_page', 10),
        ]);

        return PurchaseRequisitionResource::collection($paginator);
    }

    public function store(StorePurchaseRequisitionRequest $request): JsonResponse
    {
        $requisition = $this->purchaseRequisitions->create($request->validated());

        return (new PurchaseRequisitionResource($requisition))
            ->response()
            ->setStatusCode(201);
    }

    public function show(PurchaseRequisition $purchaseRequisition): PurchaseRequisitionResource
    {
        $purchaseRequisition->load(['requester', 'warehouse', 'items.product.unit']);

        return new PurchaseRequisitionResource($purchaseRequisition);
    }

    public function update(
        UpdatePurchaseRequisitionRequest $request,
        PurchaseRequisition $purchaseRequisition,
    ): PurchaseRequisitionResource {
        $requisition = $this->purchaseRequisitions->update($purchaseRequisition, $request->validated());

        return new PurchaseRequisitionResource($requisition);
    }

    public function destroy(PurchaseRequisition $purchaseRequisition): Response
    {
        $this->purchaseRequisitions->delete($purchaseRequisition);

        return response()->noContent();
    }
}
