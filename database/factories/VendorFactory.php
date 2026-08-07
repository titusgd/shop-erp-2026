<?php

namespace Database\Factories;

use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vendor>
 */
class VendorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'code' => null,
            'tax_id' => fake()->unique()->numerify('########'),
            'contact_name' => fake()->name(),
            'phone' => fake()->numerify('09########'),
            'email' => fake()->unique()->companyEmail(),
            'address' => fake()->address(),
            'notes' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Vendor $vendor) {
            if ($vendor->code === null) {
                $vendor->forceFill([
                    'code' => 'V'.str_pad((string) $vendor->id, 6, '0', STR_PAD_LEFT),
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
