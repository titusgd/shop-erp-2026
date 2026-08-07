<?php

namespace App\Http\Controllers;

use App\Models\ProductUnit;
use Illuminate\View\View;

class ProductUnitController extends Controller
{
    public function index(): View
    {
        return view('product-units.index');
    }

    public function create(): View
    {
        return view('product-units.create');
    }

    public function edit(ProductUnit $productUnit): View
    {
        return view('product-units.edit', [
            'productUnit' => $productUnit,
        ]);
    }
}
