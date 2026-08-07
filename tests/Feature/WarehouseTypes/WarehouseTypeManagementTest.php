<?php

namespace Tests\Feature\WarehouseTypes;

use App\Models\User;
use App\Models\WarehouseType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WarehouseTypeManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_warehouse_types_page_requires_authentication(): void
    {
        $this->get(route('warehouse-types.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_view_warehouse_types_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('warehouse-types.index'))
            ->assertOk()
            ->assertSee('倉庫類型管理')
            ->assertSee('類型');
    }

    public function test_authenticated_users_can_view_create_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('warehouse-types.create'))
            ->assertOk()
            ->assertSee('新增倉庫類型')
            ->assertSee('類型名稱');
    }

    public function test_authenticated_users_can_view_edit_page(): void
    {
        $user = User::factory()->create();
        $warehouseType = WarehouseType::factory()->create([
            'name' => '總倉',
        ]);

        $this->actingAs($user)
            ->get(route('warehouse-types.edit', $warehouseType))
            ->assertOk()
            ->assertSee('編輯倉庫類型')
            ->assertSee('總倉')
            ->assertSee('系統編號')
            ->assertSee($warehouseType->fresh()->code);
    }

    public function test_warehouse_types_can_be_listed_via_api(): void
    {
        $user = User::factory()->create();
        $warehouseType = WarehouseType::factory()->create([
            'name' => '門市倉',
        ]);

        $this->actingAs($user)
            ->getJson('/api/warehouse-types')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonFragment(['code' => $warehouseType->fresh()->code]);
    }

    public function test_warehouse_types_can_be_searched_via_api(): void
    {
        $user = User::factory()->create();
        WarehouseType::factory()->create([
            'name' => '總倉',
        ]);
        WarehouseType::factory()->create([
            'name' => '退貨倉',
        ]);

        $this->actingAs($user)
            ->getJson('/api/warehouse-types?search=退貨倉')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', '退貨倉');
    }

    public function test_warehouse_types_can_be_created_via_api_with_auto_system_code(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/warehouse-types', [
                'name' => '寄售倉',
                'notes' => '寄售商品存放',
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('data.name', '寄售倉');

        $warehouseTypeId = $response->json('data.id');
        $expectedCode = 'WT'.str_pad((string) $warehouseTypeId, 6, '0', STR_PAD_LEFT);

        $response->assertJsonPath('data.code', $expectedCode);

        $this->assertDatabaseHas('warehouse_types', [
            'id' => $warehouseTypeId,
            'code' => $expectedCode,
            'name' => '寄售倉',
        ]);
    }

    public function test_warehouse_types_ignore_client_provided_code_on_create(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/warehouse-types', [
                'name' => '暫存倉',
                'code' => 'CLIENT-CODE',
            ])
            ->assertCreated();

        $warehouseTypeId = $response->json('data.id');
        $expectedCode = 'WT'.str_pad((string) $warehouseTypeId, 6, '0', STR_PAD_LEFT);

        $response->assertJsonPath('data.code', $expectedCode);
        $this->assertDatabaseMissing('warehouse_types', ['code' => 'CLIENT-CODE']);
    }

    public function test_warehouse_types_can_be_updated_via_api_without_changing_system_code(): void
    {
        $user = User::factory()->create();
        $warehouseType = WarehouseType::factory()->create([
            'name' => '舊類型',
            'is_active' => true,
        ]);
        $originalCode = $warehouseType->fresh()->code;

        $this->actingAs($user)
            ->putJson("/api/warehouse-types/{$warehouseType->id}", [
                'name' => '新類型',
                'code' => 'SHOULD-NOT-CHANGE',
                'notes' => null,
                'is_active' => false,
            ])
            ->assertOk()
            ->assertJsonPath('data.name', '新類型')
            ->assertJsonPath('data.code', $originalCode)
            ->assertJsonPath('data.is_active', false);

        $this->assertDatabaseHas('warehouse_types', [
            'id' => $warehouseType->id,
            'name' => '新類型',
            'code' => $originalCode,
            'is_active' => false,
        ]);
    }

    public function test_warehouse_types_can_be_deleted_via_api(): void
    {
        $user = User::factory()->create();
        $warehouseType = WarehouseType::factory()->create();

        $this->actingAs($user)
            ->deleteJson("/api/warehouse-types/{$warehouseType->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('warehouse_types', [
            'id' => $warehouseType->id,
        ]);
    }

    public function test_warehouse_type_name_must_be_unique(): void
    {
        $user = User::factory()->create();
        WarehouseType::factory()->create(['name' => '總倉']);

        $this->actingAs($user)
            ->postJson('/api/warehouse-types', [
                'name' => '總倉',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name'])
            ->assertJsonPath('errors.name.0', '此類型名稱已存在。');
    }
}
