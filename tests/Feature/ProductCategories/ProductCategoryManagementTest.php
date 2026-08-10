<?php

namespace Tests\Feature\ProductCategories;

use App\Models\ProductCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_categories_page_requires_authentication(): void
    {
        $this->get(route('product-categories.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_view_product_categories_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('product-categories.index'))
            ->assertOk()
            ->assertSee('商品分類管理')
            ->assertSee('分類');
    }

    public function test_authenticated_users_can_view_create_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('product-categories.create'))
            ->assertOk()
            ->assertSee('新增商品分類')
            ->assertSee('分類名稱');
    }

    public function test_authenticated_users_can_view_edit_page(): void
    {
        $user = User::factory()->create();
        $productCategory = ProductCategory::factory()->create([
            'name' => '飲料',
        ]);

        $this->actingAs($user)
            ->get(route('product-categories.edit', $productCategory))
            ->assertOk()
            ->assertSee('編輯商品分類')
            ->assertSee('飲料')
            ->assertSee('系統編號')
            ->assertSee($productCategory->fresh()->code);
    }

    public function test_product_categories_can_be_listed_via_api(): void
    {
        $user = User::factory()->create();
        $productCategory = ProductCategory::factory()->create([
            'name' => '食品',
        ]);

        $this->actingAs($user)
            ->getJson('/api/product-categories')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonFragment(['code' => $productCategory->fresh()->code]);
    }

    public function test_product_categories_can_be_searched_via_api(): void
    {
        $user = User::factory()->create();
        ProductCategory::factory()->create([
            'name' => '飲料',
        ]);
        ProductCategory::factory()->create([
            'name' => '日用品',
        ]);

        $this->actingAs($user)
            ->getJson('/api/product-categories?search=日用品')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', '日用品');
    }

    public function test_product_categories_can_be_created_via_api_with_auto_system_code(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/product-categories', [
                'name' => '冷凍食品',
                'notes' => '需冷凍保存商品',
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('data.name', '冷凍食品');

        $productCategoryId = $response->json('data.id');
        $expectedCode = 'PC'.str_pad((string) $productCategoryId, 6, '0', STR_PAD_LEFT);

        $response->assertJsonPath('data.code', $expectedCode);

        $this->assertDatabaseHas('product_categories', [
            'id' => $productCategoryId,
            'code' => $expectedCode,
            'name' => '冷凍食品',
        ]);
    }

    public function test_product_categories_ignore_client_provided_code_on_create(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/product-categories', [
                'name' => '生鮮',
                'code' => 'CLIENT-CODE',
            ])
            ->assertCreated();

        $productCategoryId = $response->json('data.id');
        $expectedCode = 'PC'.str_pad((string) $productCategoryId, 6, '0', STR_PAD_LEFT);

        $response->assertJsonPath('data.code', $expectedCode);
        $this->assertDatabaseMissing('product_categories', ['code' => 'CLIENT-CODE']);
    }

    public function test_product_categories_can_be_updated_via_api_without_changing_system_code(): void
    {
        $user = User::factory()->create();
        $productCategory = ProductCategory::factory()->create([
            'name' => '舊分類',
            'is_active' => true,
        ]);
        $originalCode = $productCategory->fresh()->code;

        $this->actingAs($user)
            ->putJson("/api/product-categories/{$productCategory->id}", [
                'name' => '新分類',
                'code' => 'SHOULD-NOT-CHANGE',
                'notes' => null,
                'is_active' => false,
            ])
            ->assertOk()
            ->assertJsonPath('data.name', '新分類')
            ->assertJsonPath('data.code', $originalCode)
            ->assertJsonPath('data.is_active', false);

        $this->assertDatabaseHas('product_categories', [
            'id' => $productCategory->id,
            'name' => '新分類',
            'code' => $originalCode,
            'is_active' => false,
        ]);
    }

    public function test_product_categories_can_be_deleted_via_api(): void
    {
        $user = User::factory()->create();
        $productCategory = ProductCategory::factory()->create();

        $this->actingAs($user)
            ->deleteJson("/api/product-categories/{$productCategory->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('product_categories', [
            'id' => $productCategory->id,
        ]);
    }

    public function test_product_category_name_must_be_unique(): void
    {
        $user = User::factory()->create();
        ProductCategory::factory()->create(['name' => '飲料']);

        $this->actingAs($user)
            ->postJson('/api/product-categories', [
                'name' => '飲料',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name'])
            ->assertJsonPath('errors.name.0', '此分類名稱已存在。');
    }
}
