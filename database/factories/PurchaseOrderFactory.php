<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Vendor;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PurchaseOrder>
 */
class PurchaseOrderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'vendor_id' => Vendor::factory(),
            'warehouse_id' => Warehouse::factory(),
            'code' => null,
            'order_date' => now()->toDateString(),
            'expected_date' => now()->addDays(7)->toDateString(),
            'status' => PurchaseOrder::STATUS_DRAFT,
            'total_amount' => 0,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (PurchaseOrder $purchaseOrder) {
            if ($purchaseOrder->code === null) {
                $purchaseOrder->forceFill([
                    'code' => 'PO'.str_pad((string) $purchaseOrder->id, 6, '0', STR_PAD_LEFT),
                ])->save();
            }
        });
    }

    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PurchaseOrder::STATUS_CONFIRMED,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PurchaseOrder::STATUS_CANCELLED,
        ]);
    }

    public function withItems(int $count = 1): static
    {
        return $this->afterCreating(function (PurchaseOrder $purchaseOrder) use ($count) {
            $total = 0.0;

            for ($index = 0; $index < $count; $index++) {
                $quantity = fake()->randomFloat(3, 1, 20);
                $unitPrice = fake()->randomFloat(2, 10, 500);
                $amount = round($quantity * $unitPrice, 2);
                $total += $amount;

                $product = Product::factory()->create();
                $product->vendors()->attach($purchaseOrder->vendor_id);

                PurchaseOrderItem::query()->create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $product->id,
                    'quantity' => number_format($quantity, 3, '.', ''),
                    'unit_price' => number_format($unitPrice, 2, '.', ''),
                    'amount' => number_format($amount, 2, '.', ''),
                    'notes' => null,
                    'sort_order' => $index,
                ]);
            }

            $purchaseOrder->forceFill([
                'total_amount' => number_format(round($total, 2), 2, '.', ''),
            ])->save();
        });
    }
}
