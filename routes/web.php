<?php

use App\Http\Controllers\Api\ProductUnitController as ApiProductUnitController;
use App\Http\Controllers\Api\UserController as ApiUserController;
use App\Http\Controllers\Api\VendorController as ApiVendorController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductUnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VendorController;
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
    Route::get('vendors/{vendor}/edit', [VendorController::class, 'edit'])->name('vendors.edit');

    Route::get('product-units', [ProductUnitController::class, 'index'])->name('product-units.index');
    Route::get('product-units/create', [ProductUnitController::class, 'create'])->name('product-units.create');
    Route::get('product-units/{product_unit}/edit', [ProductUnitController::class, 'edit'])->name('product-units.edit');

    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    Route::prefix('api')->group(function () {
        Route::apiResource('users', ApiUserController::class)->names('api.users');
        Route::apiResource('vendors', ApiVendorController::class)->names('api.vendors');
        Route::apiResource('product-units', ApiProductUnitController::class)->names('api.product-units');
    });
});
