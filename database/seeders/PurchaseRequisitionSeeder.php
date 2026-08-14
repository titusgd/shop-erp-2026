<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\PurchaseRequisitionService;
use Illuminate\Database\Seeder;

class PurchaseRequisitionSeeder extends Seeder
{
    public function run(): void
    {
        $service = app(PurchaseRequisitionService::class);
        $requester = User::query()->orderBy('id')->first();
        $warehouse = Warehouse::query()->orderBy('id')->first();
        $products = Product::query()->orderBy('id')->limit(3)->get();

        if (! $requester || ! $warehouse || $products->isEmpty()) {
            return;
        }

        $items = $products->values()->map(function (Product $product, int $index) {
            return [
                'product_id' => $product->id,
                'quantity' => 5 + $index,
                'notes' => null,
            ];
        })->all();

        $service->create([
            'requester_id' => $requester->id,
            'warehouse_id' => $warehouse->id,
            'request_date' => now()->toDateString(),
            'required_date' => now()->addDays(5)->toDateString(),
            'status' => 'draft',
            'notes' => '示範請購單',
            'items' => $items,
        ]);
    }
}
