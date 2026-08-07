<?php

namespace Database\Factories;

use App\Models\City;
use App\Models\District;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<District>
 */
class DistrictFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'city_id' => City::factory(),
            'name' => fake()->unique()->streetName(),
            'code' => null,
            'notes' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (District $district) {
            if ($district->code === null) {
                $district->forceFill([
                    'code' => 'DT'.str_pad((string) $district->id, 6, '0', STR_PAD_LEFT),
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
