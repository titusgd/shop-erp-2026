<?php

use App\Http\Controllers\Api\CityController;
use App\Http\Controllers\Api\DistrictController;
use App\Http\Controllers\Api\ProductUnitController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VendorController;
use App\Http\Controllers\Api\WarehouseController;
use App\Http\Controllers\Api\WarehouseTypeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::apiResource('users', UserController::class)->names('api.users');
    Route::apiResource('vendors', VendorController::class)->names('api.vendors');
    Route::apiResource('product-units', ProductUnitController::class)->names('api.product-units');
    Route::apiResource('warehouse-types', WarehouseTypeController::class)->names('api.warehouse-types');
    Route::get('warehouses/{warehouse}/histories', [WarehouseController::class, 'histories'])
        ->name('api.warehouses.histories');
    Route::apiResource('warehouses', WarehouseController::class)->names('api.warehouses');
    Route::apiResource('cities', CityController::class)->names('api.cities');
    Route::apiResource('districts', DistrictController::class)->names('api.districts');
});
