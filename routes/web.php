<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DistrictController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductUnitController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\WarehouseTypeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store']);
});

Route::middleware('auth')->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');

    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::get('users/create', [UserController::class, 'create'])->name('users.create');
    Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');

    Route::get('vendors', [VendorController::class, 'index'])->name('vendors.index');
    Route::get('vendors/create', [VendorController::class, 'create'])->name('vendors.create');
    Route::get('vendors/{vendor}', [VendorController::class, 'show'])->name('vendors.show');
    Route::get('vendors/{vendor}/edit', [VendorController::class, 'edit'])->name('vendors.edit');

    Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('customers/create', [CustomerController::class, 'create'])->name('customers.create');
    Route::get('customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    Route::get('customers/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');

    Route::get('products', [ProductController::class, 'index'])->name('products.index');
    Route::get('products/create', [ProductController::class, 'create'])->name('products.create');
    Route::get('products/{product}', [ProductController::class, 'show'])->name('products.show');
    Route::get('products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');

    Route::get('product-categories', [ProductCategoryController::class, 'index'])->name('product-categories.index');
    Route::get('product-categories/create', [ProductCategoryController::class, 'create'])->name('product-categories.create');
    Route::get('product-categories/{product_category}/edit', [ProductCategoryController::class, 'edit'])->name('product-categories.edit');

    Route::get('product-units', [ProductUnitController::class, 'index'])->name('product-units.index');
    Route::get('product-units/create', [ProductUnitController::class, 'create'])->name('product-units.create');
    Route::get('product-units/{product_unit}/edit', [ProductUnitController::class, 'edit'])->name('product-units.edit');

    Route::get('warehouse-types', [WarehouseTypeController::class, 'index'])->name('warehouse-types.index');
    Route::get('warehouse-types/create', [WarehouseTypeController::class, 'create'])->name('warehouse-types.create');
    Route::get('warehouse-types/{warehouse_type}/edit', [WarehouseTypeController::class, 'edit'])->name('warehouse-types.edit');

    Route::get('warehouses', [WarehouseController::class, 'index'])->name('warehouses.index');
    Route::get('warehouses/create', [WarehouseController::class, 'create'])->name('warehouses.create');
    Route::get('warehouses/{warehouse}/edit', [WarehouseController::class, 'edit'])->name('warehouses.edit');
    Route::get('warehouses/{warehouse}/histories', [WarehouseController::class, 'histories'])->name('warehouses.histories');

    Route::get('purchase-orders', [PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
    Route::get('purchase-orders/create', [PurchaseOrderController::class, 'create'])->name('purchase-orders.create');
    Route::get('purchase-orders/{purchase_order}', [PurchaseOrderController::class, 'show'])->name('purchase-orders.show');
    Route::get('purchase-orders/{purchase_order}/edit', [PurchaseOrderController::class, 'edit'])->name('purchase-orders.edit');

    Route::get('cities', [CityController::class, 'index'])->name('cities.index');
    Route::get('cities/create', [CityController::class, 'create'])->name('cities.create');
    Route::get('cities/{city}/edit', [CityController::class, 'edit'])->name('cities.edit');

    Route::get('districts', [DistrictController::class, 'index'])->name('districts.index');
    Route::get('districts/create', [DistrictController::class, 'create'])->name('districts.create');
    Route::get('districts/{district}/edit', [DistrictController::class, 'edit'])->name('districts.edit');

    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');
});
