<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRequisition;
use Illuminate\View\View;

class PurchaseRequisitionController extends Controller
{
    public function index(): View
    {
        return view('purchase-requisitions.index');
    }

    public function create(): View
    {
        return view('purchase-requisitions.create');
    }

    public function show(PurchaseRequisition $purchaseRequisition): View
    {
        return view('purchase-requisitions.show', [
            'purchaseRequisition' => $purchaseRequisition,
        ]);
    }

    public function edit(PurchaseRequisition $purchaseRequisition): View
    {
        $purchaseRequisition->load(['requester', 'warehouse', 'items.product.unit']);

        return view('purchase-requisitions.edit', [
            'purchaseRequisition' => $purchaseRequisition,
        ]);
    }
}
