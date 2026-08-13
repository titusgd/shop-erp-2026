<?php

namespace Tests\Feature\Products;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductUnit;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_products_page_requires_authentication(): void
    {
        $this->get(route('products.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_view_products_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('products.index'))
            ->assertOk()
            ->assertSee('商品管理')
            ->assertSee('商品');
    }

    public function test_authenticated_users_can_view_create_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('products.create'))
            ->assertOk()
            ->assertSee('新增商品')
            ->assertSee('基本資料')
            ->assertSee('價格')
            ->assertSee('預計進價')
            ->assertSee('預計售價')
            ->assertSee('商品分類')
            ->assertSee('商品單位')
            ->assertSee('商品名稱')
            ->assertSee('供應商');
    }

    public function test_authenticated_users_can_view_show_page(): void
    {
        $user = User::factory()->create();
        $category = ProductCategory::factory()->create(['name' => '飲料']);
        $unit = ProductUnit::factory()->create(['name' => '個']);
        $product = Product::factory()->create([
            'product_category_id' => $category->id,
            'product_unit_id' => $unit->id,
            'name' => '礦泉水 600ml',
        ]);

        $this->actingAs($user)
            ->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('檢視商品')
            ->assertSee('查看商品主檔明細')
            ->assertSee('data-product-show-page', false)
            ->assertSee('基本資料')
            ->assertSee('價格')
            ->assertSee('價格設定歷史')
            ->assertSee('data-open-price-histories', false)
            ->assertSee('data-price-histories-modal', false)
            ->assertSee('編輯');
    }

    public function test_authenticated_users_can_view_edit_page(): void
    {
        $user = User::factory()->create();
        $category = ProductCategory::factory()->create(['name' => '飲料']);
        $unit = ProductUnit::factory()->create(['name' => '個']);
        $vendor = Vendor::factory()->create(['name' => '泉源企業']);
        $product = Product::factory()->create([
            'product_category_id' => $category->id,
            'product_unit_id' => $unit->id,
            'name' => '礦泉水 600ml',
        ]);
        $product->vendors()->attach($vendor->id);

        $this->actingAs($user)
            ->get(route('products.edit', $product))
            ->assertOk()
            ->assertSee('編輯商品')
            ->assertSee('基本資料')
            ->assertSee('價格')
            ->assertSee('預計進價')
            ->assertSee('預計售價')
            ->assertSee('歷史列表')
            ->assertSee('data-price-histories-pagination', false)
            ->assertSee('歷史高價')
            ->assertSee('歷史低價')
            ->assertSee('區間範圍')
            ->assertSee('起始日期')
            ->assertSee('結束日期')
            ->assertSee('data-open-price-histories', false)
            ->assertSee('data-price-histories-modal', false)
            ->assertSee('礦泉水 600ml')
            ->assertSee('商品分類')
            ->assertSee('供應商')
            ->assertSee('泉源企業')
            ->assertSee('data-multi-select-selected', false)
            ->assertSee('系統編號')
            ->assertSee($product->fresh()->code);
    }

    public function test_products_can_be_shown_via_api(): void
    {
        $user = User::factory()->create();
        $category = ProductCategory::factory()->create(['name' => '飲料']);
        $unit = ProductUnit::factory()->create(['name' => '個']);
        $vendor = Vendor::factory()->create(['name' => '泉源企業']);
        $product = Product::factory()->create([
            'product_category_id' => $category->id,
            'product_unit_id' => $unit->id,
            'name' => '礦泉水 600ml',
        ]);
        $product->vendors()->attach($vendor->id);

        $this->actingAs($user)
            ->getJson("/api/products/{$product->id}")
            ->assertOk()
            ->assertJsonPath('data.name', '礦泉水 600ml')
            ->assertJsonPath('data.category.name', '飲料')
            ->assertJsonPath('data.unit.name', '個')
            ->assertJsonPath('data.vendors.0.name', '泉源企業')
            ->assertJsonPath('data.code', $product->fresh()->code);
    }

    public function test_products_can_be_listed_via_api(): void
    {
        $user = User::factory()->create();
        $category = ProductCategory::factory()->create(['name' => '飲料']);
        $unit = ProductUnit::factory()->create(['name' => '個']);
        $product = Product::factory()->create([
            'product_category_id' => $category->id,
            'product_unit_id' => $unit->id,
            'name' => '礦泉水 600ml',
        ]);

        $this->actingAs($user)
            ->getJson('/api/products')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.category.name', '飲料')
            ->assertJsonPath('data.0.unit.name', '個')
            ->assertJsonFragment(['code' => $product->fresh()->code]);
    }

    public function test_products_can_be_searched_via_api(): void
    {
        $user = User::factory()->create();
        $category = ProductCategory::factory()->create(['name' => '飲料']);
        $unit = ProductUnit::factory()->create(['name' => '個']);
        Product::factory()->create([
            'product_category_id' => $category->id,
            'product_unit_id' => $unit->id,
            'name' => '礦泉水 600ml',
        ]);
        Product::factory()->create([
            'product_category_id' => $category->id,
            'product_unit_id' => $unit->id,
            'name' => '綠茶禮盒',
        ]);

        $this->actingAs($user)
            ->getJson('/api/products?search=綠茶')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', '綠茶禮盒');
    }

    public function test_products_require_category_and_unit(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/products', [
                'name' => '礦泉水 600ml',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['product_category_id', 'product_unit_id'])
            ->assertJsonPath('errors.product_category_id.0', '請選擇商品分類。')
            ->assertJsonPath('errors.product_unit_id.0', '請選擇商品單位。');
    }

    public function test_products_can_be_created_via_api_with_auto_system_code(): void
    {
        $user = User::factory()->create();
        $category = ProductCategory::factory()->create(['name' => '飲料']);
        $unit = ProductUnit::factory()->create(['name' => '個']);
        $vendorA = Vendor::factory()->create(['name' => '泉源企業']);
        $vendorB = Vendor::factory()->create(['name' => '綠葉貿易']);

        $response = $this->actingAs($user)
            ->postJson('/api/products', [
                'product_category_id' => $category->id,
                'product_unit_id' => $unit->id,
                'vendor_ids' => [$vendorA->id, $vendorB->id],
                'name' => '礦泉水 600ml',
                'notes' => '常溫保存',
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('data.name', '礦泉水 600ml')
            ->assertJsonPath('data.category.name', '飲料')
            ->assertJsonPath('data.unit.name', '個')
            ->assertJsonCount(2, 'data.vendors')
            ->assertJsonPath('data.vendors.0.name', '泉源企業')
            ->assertJsonPath('data.vendors.1.name', '綠葉貿易');

        $productId = $response->json('data.id');
        $expectedCode = 'P'.str_pad((string) $productId, 6, '0', STR_PAD_LEFT);

        $response->assertJsonPath('data.code', $expectedCode);

        $this->assertDatabaseHas('products', [
            'id' => $productId,
            'code' => $expectedCode,
            'name' => '礦泉水 600ml',
            'product_category_id' => $category->id,
            'product_unit_id' => $unit->id,
        ]);

        $this->assertDatabaseHas('product_vendor', [
            'product_id' => $productId,
            'vendor_id' => $vendorA->id,
        ]);
        $this->assertDatabaseHas('product_vendor', [
            'product_id' => $productId,
            'vendor_id' => $vendorB->id,
        ]);
    }

    public function test_product_vendors_are_optional(): void
    {
        $user = User::factory()->create();
        $category = ProductCategory::factory()->create();
        $unit = ProductUnit::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/products', [
                'product_category_id' => $category->id,
                'product_unit_id' => $unit->id,
                'vendor_ids' => [],
                'name' => '無供應商商品',
            ])
            ->assertCreated()
            ->assertJsonPath('data.vendor_ids', [])
            ->assertJsonPath('data.vendors', []);

        $this->assertDatabaseMissing('product_vendor', [
            'product_id' => $response->json('data.id'),
        ]);
    }

    public function test_products_ignore_client_provided_code_on_create(): void
    {
        $user = User::factory()->create();
        $category = ProductCategory::factory()->create();
        $unit = ProductUnit::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/products', [
                'product_category_id' => $category->id,
                'product_unit_id' => $unit->id,
                'name' => '精選白米',
                'code' => 'CLIENT-CODE',
            ])
            ->assertCreated();

        $productId = $response->json('data.id');
        $expectedCode = 'P'.str_pad((string) $productId, 6, '0', STR_PAD_LEFT);

        $response->assertJsonPath('data.code', $expectedCode);
        $this->assertDatabaseMissing('products', ['code' => 'CLIENT-CODE']);
    }

    public function test_products_can_be_updated_via_api_without_changing_system_code(): void
    {
        $user = User::factory()->create();
        $category = ProductCategory::factory()->create(['name' => '飲料']);
        $newCategory = ProductCategory::factory()->create(['name' => '食品']);
        $unit = ProductUnit::factory()->create(['name' => '個']);
        $newUnit = ProductUnit::factory()->create(['name' => '箱']);
        $vendor = Vendor::factory()->create(['name' => '舊供應商']);
        $newVendorA = Vendor::factory()->create(['name' => '新供應商甲']);
        $newVendorB = Vendor::factory()->create(['name' => '新供應商乙']);
        $product = Product::factory()->create([
            'product_category_id' => $category->id,
            'product_unit_id' => $unit->id,
            'name' => '舊商品',
            'is_active' => true,
        ]);
        $product->vendors()->attach($vendor->id);
        $originalCode = $product->fresh()->code;

        $this->actingAs($user)
            ->putJson("/api/products/{$product->id}", [
                'product_category_id' => $newCategory->id,
                'product_unit_id' => $newUnit->id,
                'vendor_ids' => [$newVendorA->id, $newVendorB->id],
                'name' => '新商品',
                'code' => 'SHOULD-NOT-CHANGE',
                'notes' => null,
                'is_active' => false,
            ])
            ->assertOk()
            ->assertJsonPath('data.name', '新商品')
            ->assertJsonPath('data.code', $originalCode)
            ->assertJsonPath('data.category.name', '食品')
            ->assertJsonPath('data.unit.name', '箱')
            ->assertJsonCount(2, 'data.vendors')
            ->assertJsonPath('data.is_active', false);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => '新商品',
            'code' => $originalCode,
            'product_category_id' => $newCategory->id,
            'product_unit_id' => $newUnit->id,
            'is_active' => false,
        ]);

        $this->assertDatabaseMissing('product_vendor', [
            'product_id' => $product->id,
            'vendor_id' => $vendor->id,
        ]);
        $this->assertDatabaseHas('product_vendor', [
            'product_id' => $product->id,
            'vendor_id' => $newVendorA->id,
        ]);
        $this->assertDatabaseHas('product_vendor', [
            'product_id' => $product->id,
            'vendor_id' => $newVendorB->id,
        ]);
    }

    public function test_products_can_be_deleted_via_api(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($user)
            ->deleteJson("/api/products/{$product->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('products', [
            'id' => $product->id,
        ]);
    }

    public function test_product_name_must_be_unique(): void
    {
        $user = User::factory()->create();
        $category = ProductCategory::factory()->create();
        $unit = ProductUnit::factory()->create();
        Product::factory()->create([
            'product_category_id' => $category->id,
            'product_unit_id' => $unit->id,
            'name' => '礦泉水 600ml',
        ]);

        $this->actingAs($user)
            ->postJson('/api/products', [
                'product_category_id' => $category->id,
                'product_unit_id' => $unit->id,
                'name' => '礦泉水 600ml',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name'])
            ->assertJsonPath('errors.name.0', '此商品名稱已存在。');
    }

    public function test_products_can_be_created_with_estimated_prices_and_history(): void
    {
        $user = User::factory()->create(['name' => '商品小明']);
        $category = ProductCategory::factory()->create();
        $unit = ProductUnit::factory()->create();
        $vendorA = Vendor::factory()->create(['name' => '泉源企業']);
        $vendorB = Vendor::factory()->create(['name' => '綠葉貿易']);

        $response = $this->actingAs($user)
            ->postJson('/api/products', [
                'product_category_id' => $category->id,
                'product_unit_id' => $unit->id,
                'vendor_ids' => [$vendorA->id, $vendorB->id],
                'vendor_purchase_prices' => [
                    $vendorA->id => 8.5,
                    $vendorB->id => 9,
                ],
                'name' => '氣泡水 500ml',
                'estimated_selling_price' => 15,
            ])
            ->assertCreated()
            ->assertJsonPath('data.estimated_selling_price', '15.00')
            ->assertJsonCount(2, 'data.vendors');

        $productId = $response->json('data.id');
        $vendors = collect($response->json('data.vendors'))->keyBy('id');

        $this->assertSame('8.50', $vendors[$vendorA->id]['estimated_purchase_price']);
        $this->assertSame('9.00', $vendors[$vendorB->id]['estimated_purchase_price']);

        $this->assertDatabaseHas('products', [
            'id' => $productId,
            'estimated_selling_price' => '15.00',
        ]);
        $this->assertDatabaseHas('product_vendor', [
            'product_id' => $productId,
            'vendor_id' => $vendorA->id,
            'estimated_purchase_price' => '8.50',
        ]);
        $this->assertDatabaseHas('product_vendor', [
            'product_id' => $productId,
            'vendor_id' => $vendorB->id,
            'estimated_purchase_price' => '9.00',
        ]);

        $this->assertDatabaseHas('product_price_histories', [
            'product_id' => $productId,
            'user_id' => $user->id,
            'action' => 'created',
        ]);

        $this->actingAs($user)
            ->getJson("/api/products/{$productId}/price-histories")
            ->assertOk()
            ->assertJsonPath('data.0.action', 'created')
            ->assertJsonPath('data.0.action_label', '設定')
            ->assertJsonPath('data.0.user.name', '商品小明')
            ->assertJsonFragment([
                'field' => 'estimated_purchase_price',
                'vendor_id' => $vendorA->id,
                'vendor_name' => '泉源企業',
                'label' => '預計進價（泉源企業）',
                'old' => null,
                'new' => '8.50',
            ])
            ->assertJsonFragment([
                'field' => 'estimated_purchase_price',
                'vendor_id' => $vendorB->id,
                'vendor_name' => '綠葉貿易',
                'label' => '預計進價（綠葉貿易）',
                'old' => null,
                'new' => '9.00',
            ])
            ->assertJsonFragment([
                'field' => 'estimated_selling_price',
                'label' => '預計售價',
                'old' => null,
                'new' => '15.00',
            ]);
    }

    public function test_product_create_without_prices_does_not_record_history(): void
    {
        $user = User::factory()->create();
        $category = ProductCategory::factory()->create();
        $unit = ProductUnit::factory()->create();

        $productId = $this->actingAs($user)
            ->postJson('/api/products', [
                'product_category_id' => $category->id,
                'product_unit_id' => $unit->id,
                'name' => '無價格商品',
            ])
            ->assertCreated()
            ->assertJsonPath('data.estimated_selling_price', null)
            ->assertJsonPath('data.vendors', [])
            ->json('data.id');

        $this->assertDatabaseMissing('product_price_histories', [
            'product_id' => $productId,
        ]);
    }

    public function test_product_price_update_records_history_changes(): void
    {
        $user = User::factory()->create(['name' => '修改者']);
        $category = ProductCategory::factory()->create();
        $unit = ProductUnit::factory()->create();
        $vendor = Vendor::factory()->create(['name' => '泉源企業']);
        $product = Product::factory()->create([
            'product_category_id' => $category->id,
            'product_unit_id' => $unit->id,
            'name' => '價格商品',
            'estimated_selling_price' => 20,
        ]);
        $product->vendors()->attach($vendor->id, ['estimated_purchase_price' => 10]);

        $this->actingAs($user)
            ->putJson("/api/products/{$product->id}", [
                'product_category_id' => $category->id,
                'product_unit_id' => $unit->id,
                'vendor_ids' => [$vendor->id],
                'vendor_purchase_prices' => [
                    $vendor->id => 12,
                ],
                'name' => '價格商品',
                'estimated_selling_price' => 20,
            ])
            ->assertOk()
            ->assertJsonPath('data.estimated_selling_price', '20.00')
            ->assertJsonPath('data.vendors.0.estimated_purchase_price', '12.00');

        $this->assertDatabaseHas('product_price_histories', [
            'product_id' => $product->id,
            'user_id' => $user->id,
            'action' => 'updated',
        ]);

        $this->actingAs($user)
            ->getJson("/api/products/{$product->id}/price-histories")
            ->assertOk()
            ->assertJsonPath('data.0.action', 'updated')
            ->assertJsonPath('data.0.action_label', '調整')
            ->assertJsonPath('data.0.user.name', '修改者')
            ->assertJsonFragment([
                'field' => 'estimated_purchase_price',
                'vendor_id' => $vendor->id,
                'label' => '預計進價（泉源企業）',
                'old' => '10.00',
                'new' => '12.00',
            ])
            ->assertJsonMissing([
                'field' => 'estimated_selling_price',
            ]);
    }

    public function test_product_update_without_price_change_does_not_record_history(): void
    {
        $user = User::factory()->create();
        $category = ProductCategory::factory()->create();
        $unit = ProductUnit::factory()->create();
        $vendor = Vendor::factory()->create();
        $product = Product::factory()->create([
            'product_category_id' => $category->id,
            'product_unit_id' => $unit->id,
            'name' => '原商品名稱',
            'estimated_selling_price' => 18,
        ]);
        $product->vendors()->attach($vendor->id, ['estimated_purchase_price' => 10]);

        $this->actingAs($user)
            ->putJson("/api/products/{$product->id}", [
                'product_category_id' => $category->id,
                'product_unit_id' => $unit->id,
                'vendor_ids' => [$vendor->id],
                'vendor_purchase_prices' => [
                    $vendor->id => 10,
                ],
                'name' => '新商品名稱',
                'estimated_selling_price' => 18,
            ])
            ->assertOk()
            ->assertJsonPath('data.name', '新商品名稱');

        $this->assertDatabaseMissing('product_price_histories', [
            'product_id' => $product->id,
        ]);
    }

    public function test_estimated_prices_cannot_be_negative(): void
    {
        $user = User::factory()->create();
        $category = ProductCategory::factory()->create();
        $unit = ProductUnit::factory()->create();
        $vendor = Vendor::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/products', [
                'product_category_id' => $category->id,
                'product_unit_id' => $unit->id,
                'vendor_ids' => [$vendor->id],
                'vendor_purchase_prices' => [
                    $vendor->id => -1,
                ],
                'name' => '負價格商品',
                'estimated_selling_price' => -5,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                "vendor_purchase_prices.{$vendor->id}",
                'estimated_selling_price',
            ])
            ->assertJsonFragment(['預計進價不可為負數。'])
            ->assertJsonPath('errors.estimated_selling_price.0', '預計售價不可為負數。');
    }

    public function test_price_histories_can_be_filtered_by_field(): void
    {
        $user = User::factory()->create();
        $category = ProductCategory::factory()->create();
        $unit = ProductUnit::factory()->create();
        $vendor = Vendor::factory()->create(['name' => '泉源企業']);

        $productId = $this->actingAs($user)
            ->postJson('/api/products', [
                'product_category_id' => $category->id,
                'product_unit_id' => $unit->id,
                'vendor_ids' => [$vendor->id],
                'vendor_purchase_prices' => [
                    $vendor->id => 8,
                ],
                'name' => '分欄歷史商品',
                'estimated_selling_price' => 16,
            ])
            ->assertCreated()
            ->json('data.id');

        $this->actingAs($user)
            ->putJson("/api/products/{$productId}", [
                'product_category_id' => $category->id,
                'product_unit_id' => $unit->id,
                'vendor_ids' => [$vendor->id],
                'vendor_purchase_prices' => [
                    $vendor->id => 9,
                ],
                'name' => '分欄歷史商品',
                'estimated_selling_price' => 16,
            ])
            ->assertOk();

        $this->actingAs($user)
            ->getJson("/api/products/{$productId}/price-histories?field=estimated_purchase_price")
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment([
                'field' => 'estimated_purchase_price',
                'label' => '預計進價（泉源企業）',
            ])
            ->assertJsonMissing([
                'field' => 'estimated_selling_price',
            ]);

        $this->actingAs($user)
            ->getJson("/api/products/{$productId}/price-histories?field=estimated_selling_price")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'field' => 'estimated_selling_price',
                'label' => '預計售價',
                'old' => null,
                'new' => '16.00',
            ])
            ->assertJsonMissing([
                'field' => 'estimated_purchase_price',
            ]);
    }
}
