<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use App\Services\ProductCategoryService;
use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = app(ProductCategoryService::class);

        $defaults = [
            ['name' => '飲料', 'notes' => '各式飲品'],
            ['name' => '食品', 'notes' => '一般食品與零食'],
            ['name' => '日用品', 'notes' => '生活日用商品'],
            ['name' => '冷凍食品', 'notes' => '需冷凍保存商品'],
            ['name' => '生鮮', 'notes' => '生鮮食材'],
        ];

        foreach ($defaults as $category) {
            $categories->create([
                ...$category,
                'is_active' => true,
            ]);
        }

        ProductCategory::factory()->count(2)->create();
    }
}
