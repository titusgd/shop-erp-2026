<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
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
            'postal_code' => null,
            'city_id' => null,
            'district_id' => null,
            'address' => fake()->streetAddress(),
            'notes' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Customer $customer) {
            if ($customer->code === null) {
                $customer->forceFill([
                    'code' => 'C'.str_pad((string) $customer->id, 6, '0', STR_PAD_LEFT),
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
