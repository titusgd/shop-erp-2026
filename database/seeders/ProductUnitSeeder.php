<?php

namespace Database\Seeders;

use App\Models\ProductUnit;
use App\Services\ProductUnitService;
use Illuminate\Database\Seeder;

class ProductUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $units = app(ProductUnitService::class);

        $defaults = [
            ['name' => '個', 'symbol' => 'pcs', 'notes' => '計數單位'],
            ['name' => '箱', 'symbol' => 'ctn', 'notes' => '包裝單位'],
            ['name' => '公斤', 'symbol' => 'kg', 'notes' => '重量單位'],
            ['name' => '公升', 'symbol' => 'L', 'notes' => '容量單位'],
            ['name' => '打', 'symbol' => 'doz', 'notes' => '12 個為一打'],
        ];

        foreach ($defaults as $unit) {
            $units->create([
                ...$unit,
                'is_active' => true,
            ]);
        }

        ProductUnit::factory()->count(3)->create();
    }
}
