<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductUnit;
use App\Models\Vendor;
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
        $vendors = Vendor::query()->orderBy('id')->get();

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

        $primaryVendor = $vendors->firstWhere('name', '台塑企業') ?? $vendors->first();
        $secondaryVendor = $vendors->first(
            fn (Vendor $vendor) => $primaryVendor && $vendor->id !== $primaryVendor->id,
        );

        $defaults = [
            [
                'product_category_id' => $beverage->id,
                'product_unit_id' => $piece->id,
                'name' => '礦泉水 600ml',
                'notes' => '常溫保存',
                'estimated_selling_price' => 18,
                'vendor_ids' => $this->vendorIds($primaryVendor),
                'vendor_purchase_prices' => $this->purchasePrices([
                    $primaryVendor?->id => 10,
                ]),
            ],
            [
                'product_category_id' => $beverage->id,
                'product_unit_id' => $box->id,
                'name' => '綠茶禮盒',
                'notes' => '12 瓶／箱',
                'estimated_selling_price' => 280,
                'vendor_ids' => $this->vendorIds($primaryVendor, $secondaryVendor),
                'vendor_purchase_prices' => $this->purchasePrices([
                    $primaryVendor?->id => 180,
                    $secondaryVendor?->id => 175,
                ]),
            ],
            [
                'product_category_id' => $food->id,
                'product_unit_id' => $piece->id,
                'name' => '原味洋芋片',
                'notes' => null,
                'estimated_selling_price' => 35,
                'vendor_ids' => [],
                'vendor_purchase_prices' => [],
            ],
            [
                'product_category_id' => $food->id,
                'product_unit_id' => $kg->id,
                'name' => '精選白米',
                'notes' => '散裝計重',
                'estimated_selling_price' => 42,
                'vendor_ids' => $this->vendorIds($secondaryVendor ?? $primaryVendor),
                'vendor_purchase_prices' => $this->purchasePrices([
                    ($secondaryVendor ?? $primaryVendor)?->id => 28,
                ]),
            ],
            [
                'product_category_id' => $daily->id,
                'product_unit_id' => $piece->id,
                'name' => '抽取式衛生紙',
                'notes' => '家庭常備',
                'estimated_selling_price' => 89,
                'vendor_ids' => $this->vendorIds($primaryVendor, $secondaryVendor),
                'vendor_purchase_prices' => $this->purchasePrices([
                    $primaryVendor?->id => 62,
                    $secondaryVendor?->id => 58,
                ]),
            ],
        ];

        foreach ($defaults as $product) {
            $existing = Product::query()->where('name', $product['name'])->first();

            if ($existing) {
                $products->update($existing, [
                    ...$product,
                    'is_active' => $existing->is_active,
                ]);

                continue;
            }

            $products->create([
                ...$product,
                'is_active' => true,
            ]);
        }

        $extraCount = Product::query()
            ->whereNotIn('name', collect($defaults)->pluck('name'))
            ->count();

        if ($extraCount < 2) {
            Product::factory()->withVendors(2)->create([
                'estimated_selling_price' => 120,
            ]);
            Product::factory()->create();
        }
    }

    /**
     * @return list<int>
     */
    private function vendorIds(?Vendor ...$vendors): array
    {
        return collect($vendors)
            ->filter()
            ->unique('id')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    /**
     * @param  array<int|string|null, int|float|null>  $prices
     * @return array<int, int|float>
     */
    private function purchasePrices(array $prices): array
    {
        $normalized = [];

        foreach ($prices as $vendorId => $price) {
            if ($vendorId === null || $vendorId === '' || $price === null) {
                continue;
            }

            $normalized[(int) $vendorId] = $price;
        }

        return $normalized;
    }
}
