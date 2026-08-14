<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductUnit;
use App\Models\Vendor;
use App\Services\ProductService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    private const TARGET_COUNT = 500;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = app(ProductService::class);
        $vendors = Vendor::query()->orderBy('id')->get();

        $categories = $this->resolveCategories();
        $units = $this->resolveUnits();

        $primaryVendor = $vendors->firstWhere('name', '台塑企業') ?? $vendors->first();
        $secondaryVendor = $vendors->first(
            fn (Vendor $vendor) => $primaryVendor && $vendor->id !== $primaryVendor->id,
        );

        $defaults = [
            [
                'product_category_id' => $categories['飲料']->id,
                'product_unit_id' => $units['個']->id,
                'name' => '礦泉水 600ml',
                'notes' => '常溫保存',
                'estimated_selling_price' => 18,
                'vendor_ids' => $this->vendorIds($primaryVendor),
                'vendor_purchase_prices' => $this->purchasePrices([
                    $primaryVendor?->id => 10,
                ]),
            ],
            [
                'product_category_id' => $categories['飲料']->id,
                'product_unit_id' => $units['箱']->id,
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
                'product_category_id' => $categories['食品']->id,
                'product_unit_id' => $units['個']->id,
                'name' => '原味洋芋片',
                'notes' => null,
                'estimated_selling_price' => 35,
                'vendor_ids' => [],
                'vendor_purchase_prices' => [],
            ],
            [
                'product_category_id' => $categories['食品']->id,
                'product_unit_id' => $units['公斤']->id,
                'name' => '精選白米',
                'notes' => '散裝計重',
                'estimated_selling_price' => 42,
                'vendor_ids' => $this->vendorIds($secondaryVendor ?? $primaryVendor),
                'vendor_purchase_prices' => $this->purchasePrices([
                    ($secondaryVendor ?? $primaryVendor)?->id => 28,
                ]),
            ],
            [
                'product_category_id' => $categories['日用品']->id,
                'product_unit_id' => $units['個']->id,
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

        $needed = self::TARGET_COUNT - Product::query()->count();

        if ($needed <= 0) {
            return;
        }

        $usedNames = Product::query()->pluck('name')->all();
        $extras = $this->extraProducts($categories, $units, $needed, $usedNames);
        $vendorList = $vendors->values();

        DB::transaction(function () use ($products, $extras, $vendorList, $primaryVendor, $secondaryVendor) {
            foreach ($extras as $index => $product) {
                $created = Product::query()->create([
                    'product_category_id' => $product['product_category_id'],
                    'product_unit_id' => $product['product_unit_id'],
                    'name' => $product['name'],
                    'code' => null,
                    'notes' => $product['notes'],
                    'estimated_selling_price' => $product['estimated_selling_price'],
                    'is_active' => $product['is_active'],
                ]);

                $created->forceFill([
                    'code' => $products->formatSystemCode((int) $created->id),
                ])->save();

                $sync = $this->vendorSyncForIndex(
                    $index,
                    (float) $product['estimated_selling_price'],
                    $vendorList,
                    $primaryVendor,
                    $secondaryVendor,
                );

                if ($sync !== []) {
                    $created->vendors()->sync($sync);
                }
            }
        });
    }

    /**
     * @return array<string, ProductCategory>
     */
    private function resolveCategories(): array
    {
        $defaults = [
            '飲料' => '各式飲品',
            '食品' => '一般食品與零食',
            '日用品' => '生活日用商品',
            '冷凍食品' => '需冷凍保存商品',
            '生鮮' => '生鮮食材',
        ];

        $resolved = [];

        foreach ($defaults as $name => $notes) {
            $resolved[$name] = ProductCategory::query()->where('name', $name)->first()
                ?? ProductCategory::factory()->create([
                    'name' => $name,
                    'notes' => $notes,
                ]);
        }

        return $resolved;
    }

    /**
     * @return array<string, ProductUnit>
     */
    private function resolveUnits(): array
    {
        $defaults = [
            '個' => ['symbol' => 'pcs', 'notes' => '計數單位'],
            '箱' => ['symbol' => 'ctn', 'notes' => '包裝單位'],
            '公斤' => ['symbol' => 'kg', 'notes' => '重量單位'],
            '公升' => ['symbol' => 'L', 'notes' => '容量單位'],
            '打' => ['symbol' => 'doz', 'notes' => '12 個為一打'],
            '包' => ['symbol' => 'pk', 'notes' => '包裝計數'],
        ];

        $resolved = [];

        foreach ($defaults as $name => $meta) {
            $resolved[$name] = ProductUnit::query()->where('name', $name)->first()
                ?? ProductUnit::factory()->create([
                    'name' => $name,
                    'symbol' => $meta['symbol'],
                    'notes' => $meta['notes'],
                ]);
        }

        return $resolved;
    }

    /**
     * @param  array<string, ProductCategory>  $categories
     * @param  array<string, ProductUnit>  $units
     * @param  list<string>  $usedNames
     * @return list<array{
     *     product_category_id: int,
     *     product_unit_id: int,
     *     name: string,
     *     notes: string|null,
     *     estimated_selling_price: int,
     *     is_active: bool
     * }>
     */
    private function extraProducts(array $categories, array $units, int $needed, array $usedNames): array
    {
        $used = array_fill_keys($usedNames, true);
        $queues = [];

        foreach ($this->catalog() as $categoryName => $definition) {
            $queues[$categoryName] = [];
            $unitNames = $definition['units'];
            $comboIndex = 0;

            foreach ($definition['products'] as $productName) {
                foreach ($definition['variants'] as $variant) {
                    foreach ($definition['sizes'] as $size) {
                        $name = trim(preg_replace('/\s+/', ' ', "{$productName} {$variant} {$size}") ?? '');

                        if ($name === '' || isset($used[$name])) {
                            continue;
                        }

                        $used[$name] = true;
                        $unitName = $unitNames[$comboIndex % count($unitNames)];
                        $priceMin = $definition['price'][0];
                        $priceMax = $definition['price'][1];
                        $price = $priceMin + (($comboIndex * 17) % ($priceMax - $priceMin + 1));
                        $notes = $definition['notes'][$comboIndex % count($definition['notes'])];

                        $queues[$categoryName][] = [
                            'product_category_id' => $categories[$categoryName]->id,
                            'product_unit_id' => $units[$unitName]->id,
                            'name' => $name,
                            'notes' => $notes,
                            'estimated_selling_price' => $price,
                            'is_active' => ($comboIndex % 11) !== 0,
                        ];

                        $comboIndex++;
                    }
                }
            }
        }

        $picked = [];
        $categoryNames = array_keys($queues);
        $offsets = array_fill_keys($categoryNames, 0);

        while (count($picked) < $needed) {
            $added = false;

            foreach ($categoryNames as $categoryName) {
                $offset = $offsets[$categoryName];

                if (! isset($queues[$categoryName][$offset])) {
                    continue;
                }

                $picked[] = $queues[$categoryName][$offset];
                $offsets[$categoryName]++;
                $added = true;

                if (count($picked) >= $needed) {
                    break;
                }
            }

            if (! $added) {
                break;
            }
        }

        return $picked;
    }

    /**
     * @return array<string, array{
     *     products: list<string>,
     *     variants: list<string>,
     *     sizes: list<string>,
     *     units: list<string>,
     *     notes: list<string|null>,
     *     price: array{0: int, 1: int}
     * }>
     */
    private function catalog(): array
    {
        return [
            '飲料' => [
                'products' => [
                    '礦泉水', '氣泡水', '蘇打水', '綠茶', '紅茶', '烏龍茶', '青茶', '茉莉花茶',
                    '奶茶', '豆漿', '可可亞', '美式咖啡', '拿鐵', '可樂', '汽水', '運動飲料',
                    '蘋果汁', '柳橙汁', '葡萄汁', '番茄汁', '乳酸飲料', '優酪乳', '鮮乳',
                    '調味乳', '植物奶', '能量飲料', '蜂蜜檸檬水', '薏仁水', '仙草茶', '冬瓜茶',
                    '楊桃汁', '芭樂汁', '芒果汁', '椰奶', '氣泡綠茶', '麥茶',
                ],
                'variants' => ['', '原味', '無糖', '微糖', '零糖', '低糖', '蜂蜜', '檸檬'],
                'sizes' => ['', '330ml', '500ml', '600ml', '1250ml', '2L', '6入', '禮盒'],
                'units' => ['個', '箱', '公升', '打'],
                'notes' => ['常溫保存', '冷藏保存', null, '熱銷品'],
                'price' => [12, 320],
            ],
            '食品' => [
                'products' => [
                    '洋芋片', '白米', '吐司', '麵包', '餅乾', '巧克力', '糖果', '泡麵',
                    '義大利麵', '燕麥', '玉米片', '堅果', '海苔', '肉乾', '牛肉乾', '豬肉乾',
                    '醬油', '沙拉油', '橄欖油', '醋', '味噌', '咖哩塊', '番茄醬', '辣椒醬',
                    '花生醬', '蜂蜜', '砂糖', '鹽', '胡椒', '雞精', '高湯塊', '鮪魚罐頭',
                    '玉米罐頭', '麵粉', '糯米粉', '冬粉', '米粉', '糙米', '十穀米', '綠豆',
                    '紅豆', '花生', '腰果', '杏仁', '核桃', '果醬',
                ],
                'variants' => ['', '原味', '辣味', '起司', '精選', '量販'],
                'sizes' => ['', '隨手包', '分享包', '家庭號', '箱裝'],
                'units' => ['個', '箱', '公斤', '包'],
                'notes' => ['常溫保存', '家庭常備', null, '熱銷品'],
                'price' => [18, 240],
            ],
            '日用品' => [
                'products' => [
                    '抽取式衛生紙', '捲筒衛生紙', '濕紙巾', '洗衣精', '洗衣球', '洗衣粉',
                    '柔軟精', '洗碗精', '漂白水', '垃圾袋', '保鮮膜', '鋁箔紙', '廚房紙巾',
                    '牙刷', '牙膏', '漱口水', '沐浴乳', '洗髮精', '潤髮乳', '洗面乳',
                    '洗手乳', '棉花棒', '牙線', '衛生棉', '護墊', '拖把', '海綿',
                    '浴室清潔劑', '玻璃清潔劑', '地板清潔劑', '芳香劑', '除濕劑', '口罩',
                    '手套', '電池', '肥皂',
                ],
                'variants' => ['', '清香', '無香', '抗菌', '補充包'],
                'sizes' => ['', '單入', '6入', '12入', '家庭號'],
                'units' => ['個', '箱', '包', '打'],
                'notes' => ['家庭常備', null, '熱銷品'],
                'price' => [29, 399],
            ],
            '冷凍食品' => [
                'products' => [
                    '冷凍水餃', '冷凍鍋貼', '冷凍包子', '冷凍饅頭', '冷凍玉米', '冷凍毛豆',
                    '冷凍花椰菜', '冷凍薯條', '冷凍雞塊', '冷凍雞翅', '冷凍魚排', '冷凍蝦仁',
                    '冰淇淋', '雪糕', '冷凍披薩', '冷凍炒飯', '冷凍湯圓', '冷凍小籠包',
                    '冷凍花枝丸', '冷凍貢丸', '冷凍火鍋料', '冷凍青豆', '冷凍綜合蔬菜',
                    '冷凍草莓', '冷凍芒果', '冷凍藍莓',
                ],
                'variants' => ['', '原味', '高麗菜', '韭菜', '玉米'],
                'sizes' => ['', '小包', '家庭號', '箱裝'],
                'units' => ['個', '箱', '公斤', '包'],
                'notes' => ['冷凍保存', '需冷凍保存'],
                'price' => [49, 289],
            ],
            '生鮮' => [
                'products' => [
                    '雞蛋', '土雞蛋', '雞胸肉', '雞腿', '豬絞肉', '豬排', '五花肉', '牛肉片',
                    '羊肉', '鮭魚', '鯛魚', '白蝦', '草蝦', '蛤蜊', '豆腐', '油豆腐',
                    '高麗菜', '菠菜', '青江菜', '空心菜', '白菜', '番茄', '小黃瓜', '胡蘿蔔',
                    '馬鈴薯', '洋蔥', '蒜頭', '薑', '香菇', '金針菇', '蘋果', '香蕉',
                    '橘子', '葡萄', '西瓜', '鳳梨', '芒果', '草莓', '酪梨', '檸檬',
                ],
                'variants' => ['', '特選', '有機', '當季'],
                'sizes' => ['', '小盒', '家庭號'],
                'units' => ['個', '公斤', '包', '箱'],
                'notes' => ['冷藏保存', '當日配送', '生鮮食材'],
                'price' => [35, 360],
            ],
        ];
    }

    /**
     * @param  \Illuminate\Support\Collection<int, Vendor>  $vendorList
     * @return array<int, array{estimated_purchase_price: string}>
     */
    private function vendorSyncForIndex(
        int $index,
        float $sellingPrice,
        $vendorList,
        ?Vendor $primaryVendor,
        ?Vendor $secondaryVendor,
    ): array {
        $mode = $index % 4;
        $purchasePrice = number_format($sellingPrice * 0.65, 2, '.', '');

        if ($mode === 0 || $vendorList->isEmpty()) {
            return [];
        }

        if ($mode === 1) {
            $vendor = $primaryVendor ?? $vendorList->first();

            return $vendor ? [(int) $vendor->id => ['estimated_purchase_price' => $purchasePrice]] : [];
        }

        if ($mode === 2) {
            $vendor = $secondaryVendor ?? $vendorList->get(1) ?? $vendorList->first();

            return $vendor ? [(int) $vendor->id => ['estimated_purchase_price' => $purchasePrice]] : [];
        }

        $sync = [];

        foreach ([$primaryVendor, $secondaryVendor] as $offset => $vendor) {
            $resolved = $vendor ?? $vendorList->get($offset);

            if (! $resolved) {
                continue;
            }

            $ratio = $offset === 0 ? 0.68 : 0.62;
            $sync[(int) $resolved->id] = [
                'estimated_purchase_price' => number_format($sellingPrice * $ratio, 2, '.', ''),
            ];
        }

        return $sync;
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
