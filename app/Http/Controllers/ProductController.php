<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        return view('products.index');
    }

    public function create(): View
    {
        return view('products.create');
    }

    public function show(Product $product): View
    {
        return view('products.show', [
            'product' => $product,
        ]);
    }

    public function edit(Product $product): View
    {
        $product->load(['category', 'unit', 'vendors']);

        return view('products.edit', [
            'product' => $product,
        ]);
    }
}
