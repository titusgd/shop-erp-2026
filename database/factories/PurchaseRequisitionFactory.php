<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\PurchaseRequisition;
use App\Models\PurchaseRequisitionItem;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PurchaseRequisition>
 */
class PurchaseRequisitionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'requester_id' => User::factory(),
            'warehouse_id' => Warehouse::factory(),
            'code' => null,
            'request_date' => now()->toDateString(),
            'required_date' => now()->addDays(7)->toDateString(),
            'status' => PurchaseRequisition::STATUS_DRAFT,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (PurchaseRequisition $purchaseRequisition) {
            if ($purchaseRequisition->code === null) {
                $purchaseRequisition->forceFill([
                    'code' => 'PR'.str_pad((string) $purchaseRequisition->id, 6, '0', STR_PAD_LEFT),
                ])->save();
            }
        });
    }

    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PurchaseRequisition::STATUS_CONFIRMED,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => PurchaseRequisition::STATUS_CANCELLED,
        ]);
    }

    public function withItems(int $count = 1): static
    {
        return $this->afterCreating(function (PurchaseRequisition $purchaseRequisition) use ($count) {
            for ($index = 0; $index < $count; $index++) {
                $quantity = fake()->randomFloat(3, 1, 20);
                $product = Product::factory()->create();

                PurchaseRequisitionItem::query()->create([
                    'purchase_requisition_id' => $purchaseRequisition->id,
                    'product_id' => $product->id,
                    'quantity' => number_format($quantity, 3, '.', ''),
                    'notes' => null,
                    'sort_order' => $index,
                ]);
            }
        });
    }
}
