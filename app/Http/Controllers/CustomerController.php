<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(): View
    {
        return view('customers.index');
    }

    public function create(): View
    {
        return view('customers.create');
    }

    public function show(Customer $customer): View
    {
        return view('customers.show', [
            'customer' => $customer,
        ]);
    }

    public function edit(Customer $customer): View
    {
        $customer->load(['city', 'district']);

        return view('customers.edit', [
            'customer' => $customer,
        ]);
    }
}
