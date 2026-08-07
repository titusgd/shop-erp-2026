<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vendors\StoreVendorRequest;
use App\Http\Requests\Vendors\UpdateVendorRequest;
use App\Http\Resources\VendorResource;
use App\Models\Vendor;
use App\Services\VendorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class VendorController extends Controller
{
    public function __construct(private VendorService $vendors) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $paginator = $this->vendors->paginate([
            'search' => $request->string('search')->toString(),
            'per_page' => $request->integer('per_page', 10),
        ]);

        return VendorResource::collection($paginator);
    }

    public function store(StoreVendorRequest $request): JsonResponse
    {
        $vendor = $this->vendors->create($request->validated());

        return (new VendorResource($vendor))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Vendor $vendor): VendorResource
    {
        return new VendorResource($vendor);
    }

    public function update(UpdateVendorRequest $request, Vendor $vendor): VendorResource
    {
        $vendor = $this->vendors->update($vendor, $request->validated());

        return new VendorResource($vendor);
    }

    public function destroy(Vendor $vendor): Response
    {
        $this->vendors->delete($vendor);

        return response()->noContent();
    }
}
