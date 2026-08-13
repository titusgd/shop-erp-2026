<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    public function index(): View
    {
        return view('purchase-orders.index');
    }

    public function create(): View
    {
        return view('purchase-orders.create');
    }

    public function show(PurchaseOrder $purchaseOrder): View
    {
        return view('purchase-orders.show', [
            'purchaseOrder' => $purchaseOrder,
        ]);
    }

    public function edit(PurchaseOrder $purchaseOrder): View
    {
        $purchaseOrder->load(['vendor', 'warehouse', 'items.product.unit']);

        return view('purchase-orders.edit', [
            'purchaseOrder' => $purchaseOrder,
        ]);
    }
}
