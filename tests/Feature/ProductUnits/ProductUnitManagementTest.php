<?php

namespace Tests\Feature\ProductUnits;

use App\Models\ProductUnit;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductUnitManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_units_page_requires_authentication(): void
    {
        $this->get(route('product-units.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_view_product_units_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('product-units.index'))
            ->assertOk()
            ->assertSee('商品單位')
            ->assertSee('單位');
    }

    public function test_authenticated_users_can_view_create_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('product-units.create'))
            ->assertOk()
            ->assertSee('新增商品單位')
            ->assertSee('單位名稱')
            ->assertSee('簡稱');
    }

    public function test_authenticated_users_can_view_edit_page(): void
    {
        $user = User::factory()->create();
        $productUnit = ProductUnit::factory()->create([
            'name' => '公斤',
        ]);

        $this->actingAs($user)
            ->get(route('product-units.edit', $productUnit))
            ->assertOk()
            ->assertSee('編輯商品單位')
            ->assertSee('公斤')
            ->assertSee('系統編號')
            ->assertSee($productUnit->fresh()->code);
    }

    public function test_product_units_can_be_listed_via_api(): void
    {
        $user = User::factory()->create();
        $productUnit = ProductUnit::factory()->create([
            'name' => '箱',
        ]);

        $this->actingAs($user)
            ->getJson('/api/product-units')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonFragment(['code' => $productUnit->fresh()->code]);
    }

    public function test_product_units_can_be_searched_via_api(): void
    {
        $user = User::factory()->create();
        ProductUnit::factory()->create([
            'name' => '公斤',
            'symbol' => 'kg',
        ]);
        ProductUnit::factory()->create([
            'name' => '公升',
            'symbol' => 'L',
        ]);

        $this->actingAs($user)
            ->getJson('/api/product-units?search=公升')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', '公升');
    }

    public function test_product_units_can_be_created_via_api_with_auto_system_code(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/product-units', [
                'name' => '打',
                'symbol' => 'doz',
                'notes' => '12 個為一打',
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('data.symbol', 'doz');

        $productUnitId = $response->json('data.id');
        $expectedCode = 'U'.str_pad((string) $productUnitId, 6, '0', STR_PAD_LEFT);

        $response->assertJsonPath('data.code', $expectedCode);

        $this->assertDatabaseHas('product_units', [
            'id' => $productUnitId,
            'code' => $expectedCode,
            'name' => '打',
            'symbol' => 'doz',
        ]);
    }

    public function test_product_units_ignore_client_provided_code_on_create(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/product-units', [
                'name' => '包',
                'code' => 'CLIENT-CODE',
            ])
            ->assertCreated();

        $productUnitId = $response->json('data.id');
        $expectedCode = 'U'.str_pad((string) $productUnitId, 6, '0', STR_PAD_LEFT);

        $response->assertJsonPath('data.code', $expectedCode);
        $this->assertDatabaseMissing('product_units', ['code' => 'CLIENT-CODE']);
    }

    public function test_product_units_can_be_updated_via_api_without_changing_system_code(): void
    {
        $user = User::factory()->create();
        $productUnit = ProductUnit::factory()->create([
            'name' => '舊單位',
            'is_active' => true,
        ]);
        $originalCode = $productUnit->fresh()->code;

        $this->actingAs($user)
            ->putJson("/api/product-units/{$productUnit->id}", [
                'name' => '新單位',
                'code' => 'SHOULD-NOT-CHANGE',
                'symbol' => 'nu',
                'notes' => null,
                'is_active' => false,
            ])
            ->assertOk()
            ->assertJsonPath('data.name', '新單位')
            ->assertJsonPath('data.code', $originalCode)
            ->assertJsonPath('data.symbol', 'nu')
            ->assertJsonPath('data.is_active', false);

        $this->assertDatabaseHas('product_units', [
            'id' => $productUnit->id,
            'name' => '新單位',
            'code' => $originalCode,
            'symbol' => 'nu',
            'is_active' => false,
        ]);
    }

    public function test_product_units_can_be_deleted_via_api(): void
    {
        $user = User::factory()->create();
        $productUnit = ProductUnit::factory()->create();

        $this->actingAs($user)
            ->deleteJson("/api/product-units/{$productUnit->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('product_units', [
            'id' => $productUnit->id,
        ]);
    }

    public function test_product_unit_name_must_be_unique(): void
    {
        $user = User::factory()->create();
        ProductUnit::factory()->create(['name' => '個']);

        $this->actingAs($user)
            ->postJson('/api/product-units', [
                'name' => '個',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name'])
            ->assertJsonPath('errors.name.0', '此單位名稱已存在。');
    }

    public function test_product_unit_symbol_must_be_unique(): void
    {
        $user = User::factory()->create();
        ProductUnit::factory()->create(['symbol' => 'kg']);

        $this->actingAs($user)
            ->postJson('/api/product-units', [
                'name' => '千克',
                'symbol' => 'kg',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['symbol'])
            ->assertJsonPath('errors.symbol.0', '此簡稱已存在。');
    }
}
