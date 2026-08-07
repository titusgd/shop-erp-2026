<?php

namespace Tests\Feature\Warehouses;

use App\Models\City;
use App\Models\District;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseType;
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
            ->assertSee('基本資料')
            ->assertSee('聯絡資訊')
            ->assertSee('其他')
            ->assertSee('系統資訊')
            ->assertSee('倉庫名稱')
            ->assertSee('倉庫類型')
            ->assertSee('聯絡人')
            ->assertSee('郵遞區號')
            ->assertSee('縣市')
            ->assertSee('區域')
            ->assertSee('輸入關鍵字搜尋縣市')
            ->assertSee('系統資訊將於儲存後產生');
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
            ->assertSee('基本資料')
            ->assertSee('聯絡資訊')
            ->assertSee('其他')
            ->assertSee('系統資訊')
            ->assertSee('建立人員')
            ->assertSee('建立時間')
            ->assertSee('修改人員')
            ->assertSee('修改日期')
            ->assertSee('修改歷程')
            ->assertSee('檢視修改歷程')
            ->assertSee('data-open-histories-modal', false)
            ->assertSee('data-histories-modal', false)
            ->assertSee('測試倉庫')
            ->assertSee('系統編號')
            ->assertSee($warehouse->fresh()->code);
    }

    public function test_authenticated_users_can_view_histories_page(): void
    {
        $user = User::factory()->create();
        $warehouse = Warehouse::factory()->create([
            'name' => '歷程倉庫',
        ]);

        $this->actingAs($user)
            ->get(route('warehouses.histories', $warehouse))
            ->assertOk()
            ->assertSee('修改歷程')
            ->assertSee('歷程倉庫')
            ->assertSee('返回編輯');
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

    public function test_warehouses_can_be_created_with_multiple_types(): void
    {
        $user = User::factory()->create();
        $typeA = WarehouseType::factory()->create(['name' => '總倉']);
        $typeB = WarehouseType::factory()->create(['name' => '門市倉']);

        $response = $this->actingAs($user)
            ->postJson('/api/warehouses', [
                'name' => '多類型倉庫',
                'warehouse_type_ids' => [$typeA->id, $typeB->id],
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonCount(2, 'data.warehouse_types');

        $warehouseId = $response->json('data.id');

        $this->assertDatabaseHas('warehouse_warehouse_type', [
            'warehouse_id' => $warehouseId,
            'warehouse_type_id' => $typeA->id,
        ]);
        $this->assertDatabaseHas('warehouse_warehouse_type', [
            'warehouse_id' => $warehouseId,
            'warehouse_type_id' => $typeB->id,
        ]);
    }

    public function test_warehouses_can_sync_types_on_update(): void
    {
        $user = User::factory()->create();
        $typeA = WarehouseType::factory()->create(['name' => '總倉']);
        $typeB = WarehouseType::factory()->create(['name' => '退貨倉']);
        $warehouse = Warehouse::factory()->create(['name' => '同步倉庫']);
        $warehouse->warehouseTypes()->sync([$typeA->id]);

        $this->actingAs($user)
            ->putJson("/api/warehouses/{$warehouse->id}", [
                'name' => '同步倉庫',
                'warehouse_type_ids' => [$typeB->id],
                'is_active' => true,
            ])
            ->assertOk()
            ->assertJsonCount(1, 'data.warehouse_types')
            ->assertJsonPath('data.warehouse_types.0.id', $typeB->id);

        $this->assertDatabaseMissing('warehouse_warehouse_type', [
            'warehouse_id' => $warehouse->id,
            'warehouse_type_id' => $typeA->id,
        ]);
        $this->assertDatabaseHas('warehouse_warehouse_type', [
            'warehouse_id' => $warehouse->id,
            'warehouse_type_id' => $typeB->id,
        ]);
    }

    public function test_edit_page_shows_selected_warehouse_types(): void
    {
        $user = User::factory()->create();
        $type = WarehouseType::factory()->create(['name' => '寄售倉']);
        $warehouse = Warehouse::factory()->create(['name' => '編輯倉庫']);
        $warehouse->warehouseTypes()->sync([$type->id]);

        $this->actingAs($user)
            ->get(route('warehouses.edit', $warehouse))
            ->assertOk()
            ->assertSee('倉庫類型')
            ->assertSee('寄售倉');
    }

    public function test_warehouses_can_be_created_with_location_fields(): void
    {
        $user = User::factory()->create();
        $city = City::factory()->create(['name' => '臺北市']);
        $district = District::factory()->create([
            'city_id' => $city->id,
            'name' => '中正區',
        ]);

        $this->actingAs($user)
            ->postJson('/api/warehouses', [
                'name' => '地址倉庫',
                'postal_code' => '100',
                'city_id' => $city->id,
                'district_id' => $district->id,
                'address' => '重慶南路一段122號',
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('data.postal_code', '100')
            ->assertJsonPath('data.city_id', $city->id)
            ->assertJsonPath('data.district_id', $district->id)
            ->assertJsonPath('data.city.name', '臺北市')
            ->assertJsonPath('data.district.name', '中正區');

        $this->assertDatabaseHas('warehouses', [
            'name' => '地址倉庫',
            'postal_code' => '100',
            'city_id' => $city->id,
            'district_id' => $district->id,
            'address' => '重慶南路一段122號',
        ]);
    }

    public function test_warehouse_district_must_belong_to_selected_city(): void
    {
        $user = User::factory()->create();
        $cityA = City::factory()->create(['name' => '臺北市']);
        $cityB = City::factory()->create(['name' => '新北市']);
        $districtB = District::factory()->create([
            'city_id' => $cityB->id,
            'name' => '板橋區',
        ]);

        $this->actingAs($user)
            ->postJson('/api/warehouses', [
                'name' => '錯誤區域倉庫',
                'city_id' => $cityA->id,
                'district_id' => $districtB->id,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['district_id']);
    }

    public function test_warehouse_create_records_creator_and_history(): void
    {
        $user = User::factory()->create(['name' => '倉管小明']);

        $response = $this->actingAs($user)
            ->postJson('/api/warehouses', [
                'name' => '稽核倉庫',
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('data.creator.name', '倉管小明')
            ->assertJsonPath('data.updater.name', '倉管小明');

        $warehouseId = $response->json('data.id');

        $this->assertDatabaseHas('warehouses', [
            'id' => $warehouseId,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $this->assertDatabaseHas('warehouse_histories', [
            'warehouse_id' => $warehouseId,
            'user_id' => $user->id,
            'action' => 'created',
        ]);

        $this->actingAs($user)
            ->getJson("/api/warehouses/{$warehouseId}/histories")
            ->assertOk()
            ->assertJsonPath('data.0.action', 'created')
            ->assertJsonPath('data.0.user.name', '倉管小明');
    }

    public function test_warehouse_update_records_updater_and_history_changes(): void
    {
        $creator = User::factory()->create(['name' => '建立者']);
        $updater = User::factory()->create(['name' => '修改者']);

        $warehouseId = $this->actingAs($creator)
            ->postJson('/api/warehouses', [
                'name' => '原倉庫名稱',
                'is_active' => true,
            ])
            ->assertCreated()
            ->json('data.id');

        $this->actingAs($updater)
            ->putJson("/api/warehouses/{$warehouseId}", [
                'name' => '新倉庫名稱',
                'is_active' => true,
            ])
            ->assertOk()
            ->assertJsonPath('data.name', '新倉庫名稱')
            ->assertJsonPath('data.updater.name', '修改者');

        $this->assertDatabaseHas('warehouses', [
            'id' => $warehouseId,
            'created_by' => $creator->id,
            'updated_by' => $updater->id,
            'name' => '新倉庫名稱',
        ]);

        $this->actingAs($updater)
            ->getJson("/api/warehouses/{$warehouseId}/histories")
            ->assertOk()
            ->assertJsonPath('data.0.action', 'updated')
            ->assertJsonPath('data.0.user.name', '修改者')
            ->assertJsonFragment([
                'field' => 'name',
                'label' => '倉庫名稱',
                'old' => '原倉庫名稱',
                'new' => '新倉庫名稱',
            ]);
    }
}
