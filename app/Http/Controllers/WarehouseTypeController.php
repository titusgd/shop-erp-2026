<?php

namespace App\Http\Controllers;

use App\Models\WarehouseType;
use Illuminate\View\View;

class WarehouseTypeController extends Controller
{
    public function index(): View
    {
        return view('warehouse-types.index');
    }

    public function create(): View
    {
        return view('warehouse-types.create');
    }

    public function edit(WarehouseType $warehouseType): View
    {
        return view('warehouse-types.edit', [
            'warehouseType' => $warehouseType,
        ]);
    }
}
