<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customers\StoreCustomerRequest;
use App\Http\Requests\Customers\UpdateCustomerRequest;
use App\Http\Resources\CustomerResource;
use App\Models\Customer;
use App\Services\CustomerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class CustomerController extends Controller
{
    public function __construct(private CustomerService $customers) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $paginator = $this->customers->paginate([
            'search' => $request->string('search')->toString(),
            'per_page' => $request->integer('per_page', 10),
        ]);

        return CustomerResource::collection($paginator);
    }

    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $customer = $this->customers->create($request->validated());

        return (new CustomerResource($customer))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Customer $customer): CustomerResource
    {
        $customer->load(['city', 'district']);

        return new CustomerResource($customer);
    }

    public function update(UpdateCustomerRequest $request, Customer $customer): CustomerResource
    {
        $customer = $this->customers->update($customer, $request->validated());

        return new CustomerResource($customer);
    }

    public function destroy(Customer $customer): Response
    {
        $this->customers->delete($customer);

        return response()->noContent();
    }
}
