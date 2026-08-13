<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Vendor;
use App\Models\Warehouse;
use App\Services\PurchaseOrderService;
use Illuminate\Database\Seeder;

class PurchaseOrderSeeder extends Seeder
{
    public function run(): void
    {
        $service = app(PurchaseOrderService::class);
        $vendor = Vendor::query()->orderBy('id')->first();
        $warehouse = Warehouse::query()->orderBy('id')->first();
        $products = Product::query()->orderBy('id')->limit(3)->get();

        if (! $vendor || ! $warehouse || $products->isEmpty()) {
            return;
        }

        $items = $products->values()->map(function (Product $product, int $index) {
            return [
                'product_id' => $product->id,
                'quantity' => 10 + $index,
                'unit_price' => 50 + ($index * 10),
                'notes' => null,
            ];
        })->all();

        $service->create([
            'vendor_id' => $vendor->id,
            'warehouse_id' => $warehouse->id,
            'order_date' => now()->toDateString(),
            'expected_date' => now()->addDays(5)->toDateString(),
            'status' => 'draft',
            'notes' => '示範採購單',
            'items' => $items,
        ]);
    }
}
