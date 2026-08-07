<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\View\View;

class WarehouseController extends Controller
{
    public function index(): View
    {
        return view('warehouses.index');
    }

    public function create(): View
    {
        return view('warehouses.create');
    }

    public function edit(Warehouse $warehouse): View
    {
        $warehouse->load(['warehouseTypes', 'city', 'district', 'creator', 'updater']);

        return view('warehouses.edit', [
            'warehouse' => $warehouse,
        ]);
    }

    public function histories(Warehouse $warehouse): View
    {
        return view('warehouses.histories', [
            'warehouse' => $warehouse,
        ]);
    }
}
