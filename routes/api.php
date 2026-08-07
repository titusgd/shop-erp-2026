<?php

use App\Http\Controllers\Api\ProductUnitController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VendorController;
use App\Http\Controllers\Api\WarehouseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::apiResource('users', UserController::class)->names('api.users');
    Route::apiResource('vendors', VendorController::class)->names('api.vendors');
    Route::apiResource('product-units', ProductUnitController::class)->names('api.product-units');
    Route::apiResource('warehouses', WarehouseController::class)->names('api.warehouses');
});
