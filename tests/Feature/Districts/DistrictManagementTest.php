<?php

namespace Tests\Feature\Districts;

use App\Models\City;
use App\Models\District;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DistrictManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_districts_page_requires_authentication(): void
    {
        $this->get(route('districts.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_view_districts_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('districts.index'))
            ->assertOk()
            ->assertSee('地區管理')
            ->assertSee('地區');
    }

    public function test_authenticated_users_can_view_create_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('districts.create'))
            ->assertOk()
            ->assertSee('新增地區')
            ->assertSee('縣市')
            ->assertSee('地區名稱');
    }

    public function test_authenticated_users_can_view_edit_page(): void
    {
        $user = User::factory()->create();
        $city = City::factory()->create(['name' => '臺北市']);
        $district = District::factory()->create([
            'city_id' => $city->id,
            'name' => '大安區',
        ]);

        $this->actingAs($user)
            ->get(route('districts.edit', $district))
            ->assertOk()
            ->assertSee('編輯地區')
            ->assertSee('大安區')
            ->assertSee('縣市')
            ->assertSee('系統編號')
            ->assertSee($district->fresh()->code);
    }

    public function test_districts_can_be_listed_via_api(): void
    {
        $user = User::factory()->create();
        $city = City::factory()->create(['name' => '臺北市']);
        $district = District::factory()->create([
            'city_id' => $city->id,
            'name' => '中正區',
        ]);

        $this->actingAs($user)
            ->getJson('/api/districts')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.city.name', '臺北市')
            ->assertJsonFragment(['code' => $district->fresh()->code]);
    }

    public function test_districts_can_be_searched_via_api(): void
    {
        $user = User::factory()->create();
        $city = City::factory()->create(['name' => '臺北市']);
        District::factory()->create([
            'city_id' => $city->id,
            'name' => '中正區',
        ]);
        District::factory()->create([
            'city_id' => $city->id,
            'name' => '大安區',
        ]);

        $this->actingAs($user)
            ->getJson('/api/districts?search=大安區')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', '大安區');
    }

    public function test_districts_require_city_id(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/districts', [
                'name' => '中正區',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['city_id'])
            ->assertJsonPath('errors.city_id.0', '請選擇縣市。');
    }

    public function test_districts_can_be_created_via_api_with_auto_system_code(): void
    {
        $user = User::factory()->create();
        $city = City::factory()->create(['name' => '臺北市']);

        $response = $this->actingAs($user)
            ->postJson('/api/districts', [
                'city_id' => $city->id,
                'name' => '信義區',
                'notes' => '示範地區',
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('data.name', '信義區')
            ->assertJsonPath('data.city_id', $city->id)
            ->assertJsonPath('data.city.name', '臺北市');

        $districtId = $response->json('data.id');
        $expectedCode = 'DT'.str_pad((string) $districtId, 6, '0', STR_PAD_LEFT);

        $response->assertJsonPath('data.code', $expectedCode);

        $this->assertDatabaseHas('districts', [
            'id' => $districtId,
            'city_id' => $city->id,
            'code' => $expectedCode,
            'name' => '信義區',
        ]);
    }

    public function test_districts_ignore_client_provided_code_on_create(): void
    {
        $user = User::factory()->create();
        $city = City::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/districts', [
                'city_id' => $city->id,
                'name' => '松山區',
                'code' => 'CLIENT-CODE',
            ])
            ->assertCreated();

        $districtId = $response->json('data.id');
        $expectedCode = 'DT'.str_pad((string) $districtId, 6, '0', STR_PAD_LEFT);

        $response->assertJsonPath('data.code', $expectedCode);
        $this->assertDatabaseMissing('districts', ['code' => 'CLIENT-CODE']);
    }

    public function test_districts_can_be_updated_via_api_without_changing_system_code(): void
    {
        $user = User::factory()->create();
        $city = City::factory()->create(['name' => '臺北市']);
        $newCity = City::factory()->create(['name' => '新北市']);
        $district = District::factory()->create([
            'city_id' => $city->id,
            'name' => '舊地區',
            'is_active' => true,
        ]);
        $originalCode = $district->fresh()->code;

        $this->actingAs($user)
            ->putJson("/api/districts/{$district->id}", [
                'city_id' => $newCity->id,
                'name' => '新地區',
                'code' => 'SHOULD-NOT-CHANGE',
                'notes' => null,
                'is_active' => false,
            ])
            ->assertOk()
            ->assertJsonPath('data.name', '新地區')
            ->assertJsonPath('data.city_id', $newCity->id)
            ->assertJsonPath('data.code', $originalCode)
            ->assertJsonPath('data.is_active', false);

        $this->assertDatabaseHas('districts', [
            'id' => $district->id,
            'city_id' => $newCity->id,
            'name' => '新地區',
            'code' => $originalCode,
            'is_active' => false,
        ]);
    }

    public function test_districts_can_be_deleted_via_api(): void
    {
        $user = User::factory()->create();
        $district = District::factory()->create();

        $this->actingAs($user)
            ->deleteJson("/api/districts/{$district->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('districts', [
            'id' => $district->id,
        ]);
    }

    public function test_district_name_must_be_unique_within_city(): void
    {
        $user = User::factory()->create();
        $city = City::factory()->create();
        District::factory()->create([
            'city_id' => $city->id,
            'name' => '中正區',
        ]);

        $this->actingAs($user)
            ->postJson('/api/districts', [
                'city_id' => $city->id,
                'name' => '中正區',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name'])
            ->assertJsonPath('errors.name.0', '此縣市下已有相同地區名稱。');
    }
}
