<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use Illuminate\View\View;

class VendorController extends Controller
{
    public function index(): View
    {
        return view('vendors.index');
    }

    public function create(): View
    {
        return view('vendors.create');
    }

    public function show(Vendor $vendor): View
    {
        return view('vendors.show', [
            'vendor' => $vendor,
        ]);
    }

    public function edit(Vendor $vendor): View
    {
        $vendor->load(['city', 'district']);

        return view('vendors.edit', [
            'vendor' => $vendor,
        ]);
    }
}
