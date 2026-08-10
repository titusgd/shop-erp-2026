<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductUnit;
use App\Services\ProductService;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = app(ProductService::class);

        $beverage = ProductCategory::query()->where('name', '飲料')->first()
            ?? ProductCategory::factory()->create(['name' => '飲料']);
        $food = ProductCategory::query()->where('name', '食品')->first()
            ?? ProductCategory::factory()->create(['name' => '食品']);
        $daily = ProductCategory::query()->where('name', '日用品')->first()
            ?? ProductCategory::factory()->create(['name' => '日用品']);

        $piece = ProductUnit::query()->where('name', '個')->first()
            ?? ProductUnit::factory()->create(['name' => '個', 'symbol' => 'pcs']);
        $box = ProductUnit::query()->where('name', '箱')->first()
            ?? ProductUnit::factory()->create(['name' => '箱', 'symbol' => 'ctn']);
        $kg = ProductUnit::query()->where('name', '公斤')->first()
            ?? ProductUnit::factory()->create(['name' => '公斤', 'symbol' => 'kg']);

        $defaults = [
            [
                'product_category_id' => $beverage->id,
                'product_unit_id' => $piece->id,
                'name' => '礦泉水 600ml',
                'notes' => '常溫保存',
            ],
            [
                'product_category_id' => $beverage->id,
                'product_unit_id' => $box->id,
                'name' => '綠茶禮盒',
                'notes' => '12 瓶／箱',
            ],
            [
                'product_category_id' => $food->id,
                'product_unit_id' => $piece->id,
                'name' => '原味洋芋片',
                'notes' => null,
            ],
            [
                'product_category_id' => $food->id,
                'product_unit_id' => $kg->id,
                'name' => '精選白米',
                'notes' => '散裝計重',
            ],
            [
                'product_category_id' => $daily->id,
                'product_unit_id' => $piece->id,
                'name' => '抽取式衛生紙',
                'notes' => '家庭常備',
            ],
        ];

        foreach ($defaults as $product) {
            $products->create([
                ...$product,
                'is_active' => true,
            ]);
        }

        Product::factory()->count(2)->create();
    }
}
