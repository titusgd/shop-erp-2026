<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductUnit;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_category_id' => ProductCategory::factory(),
            'product_unit_id' => ProductUnit::factory(),
            'name' => fake()->unique()->words(3, true),
            'code' => null,
            'notes' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Product $product) {
            if ($product->code === null) {
                $product->forceFill([
                    'code' => 'P'.str_pad((string) $product->id, 6, '0', STR_PAD_LEFT),
                ])->save();
            }
        });
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function withVendors(int $count = 1): static
    {
        return $this->afterCreating(function (Product $product) use ($count) {
            $vendors = Vendor::factory()->count($count)->create();
            $product->vendors()->attach($vendors->pluck('id'));
        });
    }
}
