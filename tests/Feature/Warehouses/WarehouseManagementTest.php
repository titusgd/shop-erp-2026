<?php

namespace Tests\Feature\Warehouses;

use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WarehouseManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_warehouses_page_requires_authentication(): void
    {
        $this->get(route('warehouses.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_view_warehouses_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('warehouses.index'))
            ->assertOk()
            ->assertSee('倉庫管理')
            ->assertSee('倉庫');
    }

    public function test_authenticated_users_can_view_create_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('warehouses.create'))
            ->assertOk()
            ->assertSee('新增倉庫')
            ->assertSee('倉庫名稱')
            ->assertSee('聯絡人');
    }

    public function test_authenticated_users_can_view_edit_page(): void
    {
        $user = User::factory()->create();
        $warehouse = Warehouse::factory()->create([
            'name' => '測試倉庫',
        ]);

        $this->actingAs($user)
            ->get(route('warehouses.edit', $warehouse))
            ->assertOk()
            ->assertSee('編輯倉庫')
            ->assertSee('測試倉庫')
            ->assertSee('系統編號')
            ->assertSee($warehouse->fresh()->code);
    }

    public function test_warehouses_can_be_listed_via_api(): void
    {
        $user = User::factory()->create();
        $warehouse = Warehouse::factory()->create([
            'name' => '總倉',
        ]);

        $this->actingAs($user)
            ->getJson('/api/warehouses')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonFragment(['code' => $warehouse->fresh()->code]);
    }

    public function test_warehouses_can_be_searched_via_api(): void
    {
        $user = User::factory()->create();
        Warehouse::factory()->create([
            'name' => '總倉',
        ]);
        Warehouse::factory()->create([
            'name' => '南區物流倉',
        ]);

        $this->actingAs($user)
            ->getJson('/api/warehouses?search=南區')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', '南區物流倉');
    }

    public function test_warehouses_can_be_created_via_api_with_auto_system_code(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/warehouses', [
                'name' => '新進倉庫',
                'contact_name' => '陳倉管',
                'phone' => '0987654321',
                'email' => 'warehouse@example.com',
                'address' => '台中市西屯區',
                'notes' => '測試備註',
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('data.contact_name', '陳倉管');

        $warehouseId = $response->json('data.id');
        $expectedCode = 'W'.str_pad((string) $warehouseId, 6, '0', STR_PAD_LEFT);

        $response->assertJsonPath('data.code', $expectedCode);

        $this->assertDatabaseHas('warehouses', [
            'id' => $warehouseId,
            'code' => $expectedCode,
            'name' => '新進倉庫',
            'contact_name' => '陳倉管',
        ]);
    }

    public function test_warehouses_ignore_client_provided_code_on_create(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/warehouses', [
                'name' => '系統編號倉庫',
                'code' => 'CLIENT-CODE',
            ])
            ->assertCreated();

        $warehouseId = $response->json('data.id');
        $expectedCode = 'W'.str_pad((string) $warehouseId, 6, '0', STR_PAD_LEFT);

        $response->assertJsonPath('data.code', $expectedCode);
        $this->assertDatabaseMissing('warehouses', ['code' => 'CLIENT-CODE']);
    }

    public function test_warehouses_can_be_updated_via_api_without_changing_system_code(): void
    {
        $user = User::factory()->create();
        $warehouse = Warehouse::factory()->create([
            'name' => '舊倉庫',
            'is_active' => true,
        ]);
        $originalCode = $warehouse->fresh()->code;

        $this->actingAs($user)
            ->putJson("/api/warehouses/{$warehouse->id}", [
                'name' => '更新倉庫',
                'code' => 'SHOULD-NOT-CHANGE',
                'contact_name' => '李小姐',
                'phone' => '0911111111',
                'email' => 'updated@example.com',
                'address' => '高雄市',
                'notes' => null,
                'is_active' => false,
            ])
            ->assertOk()
            ->assertJsonPath('data.name', '更新倉庫')
            ->assertJsonPath('data.code', $originalCode)
            ->assertJsonPath('data.contact_name', '李小姐')
            ->assertJsonPath('data.is_active', false);

        $this->assertDatabaseHas('warehouses', [
            'id' => $warehouse->id,
            'name' => '更新倉庫',
            'code' => $originalCode,
            'contact_name' => '李小姐',
            'is_active' => false,
        ]);
    }

    public function test_warehouses_can_be_deleted_via_api(): void
    {
        $user = User::factory()->create();
        $warehouse = Warehouse::factory()->create();

        $this->actingAs($user)
            ->deleteJson("/api/warehouses/{$warehouse->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('warehouses', [
            'id' => $warehouse->id,
        ]);
    }

    public function test_warehouse_name_is_required(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/warehouses', [
                'name' => '',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name']);
    }
}
