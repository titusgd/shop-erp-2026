<?php

namespace App\Http\Controllers;

use App\Models\ProductCategory;
use Illuminate\View\View;

class ProductCategoryController extends Controller
{
    public function index(): View
    {
        return view('product-categories.index');
    }

    public function create(): View
    {
        return view('product-categories.create');
    }

    public function edit(ProductCategory $productCategory): View
    {
        return view('product-categories.edit', [
            'productCategory' => $productCategory,
        ]);
    }
}
