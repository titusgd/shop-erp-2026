<?php

namespace Database\Factories;

use App\Models\WarehouseType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WarehouseType>
 */
class WarehouseTypeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'code' => null,
            'notes' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (WarehouseType $warehouseType) {
            if ($warehouseType->code === null) {
                $warehouseType->forceFill([
                    'code' => 'WT'.str_pad((string) $warehouseType->id, 6, '0', STR_PAD_LEFT),
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
}
