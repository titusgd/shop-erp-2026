<?php

namespace Tests\Feature\Cities;

use App\Models\City;
use App\Models\District;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CityManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_cities_page_requires_authentication(): void
    {
        $this->get(route('cities.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_view_cities_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('cities.index'))
            ->assertOk()
            ->assertSee('縣市管理')
            ->assertSee('縣市');
    }

    public function test_authenticated_users_can_view_create_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('cities.create'))
            ->assertOk()
            ->assertSee('新增縣市')
            ->assertSee('縣市名稱');
    }

    public function test_authenticated_users_can_view_edit_page(): void
    {
        $user = User::factory()->create();
        $city = City::factory()->create([
            'name' => '臺北市',
        ]);

        $this->actingAs($user)
            ->get(route('cities.edit', $city))
            ->assertOk()
            ->assertSee('編輯縣市')
            ->assertSee('臺北市')
            ->assertSee('系統編號')
            ->assertSee($city->fresh()->code);
    }

    public function test_cities_can_be_listed_via_api(): void
    {
        $user = User::factory()->create();
        $city = City::factory()->create([
            'name' => '新北市',
        ]);

        $this->actingAs($user)
            ->getJson('/api/cities')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonFragment(['code' => $city->fresh()->code]);
    }

    public function test_cities_can_be_searched_via_api(): void
    {
        $user = User::factory()->create();
        City::factory()->create([
            'name' => '臺北市',
        ]);
        City::factory()->create([
            'name' => '高雄市',
        ]);

        $this->actingAs($user)
            ->getJson('/api/cities?search=高雄市')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', '高雄市');
    }

    public function test_cities_can_be_created_via_api_with_auto_system_code(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/cities', [
                'name' => '桃園市',
                'notes' => '示範縣市',
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('data.name', '桃園市');

        $cityId = $response->json('data.id');
        $expectedCode = 'CT'.str_pad((string) $cityId, 6, '0', STR_PAD_LEFT);

        $response->assertJsonPath('data.code', $expectedCode);

        $this->assertDatabaseHas('cities', [
            'id' => $cityId,
            'code' => $expectedCode,
            'name' => '桃園市',
        ]);
    }

    public function test_cities_ignore_client_provided_code_on_create(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/cities', [
                'name' => '臺中市',
                'code' => 'CLIENT-CODE',
            ])
            ->assertCreated();

        $cityId = $response->json('data.id');
        $expectedCode = 'CT'.str_pad((string) $cityId, 6, '0', STR_PAD_LEFT);

        $response->assertJsonPath('data.code', $expectedCode);
        $this->assertDatabaseMissing('cities', ['code' => 'CLIENT-CODE']);
    }

    public function test_cities_can_be_updated_via_api_without_changing_system_code(): void
    {
        $user = User::factory()->create();
        $city = City::factory()->create([
            'name' => '舊縣市',
            'is_active' => true,
        ]);
        $originalCode = $city->fresh()->code;

        $this->actingAs($user)
            ->putJson("/api/cities/{$city->id}", [
                'name' => '新縣市',
                'code' => 'SHOULD-NOT-CHANGE',
                'notes' => null,
                'is_active' => false,
            ])
            ->assertOk()
            ->assertJsonPath('data.name', '新縣市')
            ->assertJsonPath('data.code', $originalCode)
            ->assertJsonPath('data.is_active', false);

        $this->assertDatabaseHas('cities', [
            'id' => $city->id,
            'name' => '新縣市',
            'code' => $originalCode,
            'is_active' => false,
        ]);
    }

    public function test_cities_can_be_deleted_via_api(): void
    {
        $user = User::factory()->create();
        $city = City::factory()->create();

        $this->actingAs($user)
            ->deleteJson("/api/cities/{$city->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('cities', [
            'id' => $city->id,
        ]);
    }

    public function test_cities_cannot_be_deleted_when_districts_exist(): void
    {
        $user = User::factory()->create();
        $city = City::factory()->create();
        District::factory()->create([
            'city_id' => $city->id,
        ]);

        $this->actingAs($user)
            ->deleteJson("/api/cities/{$city->id}")
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['city']);

        $this->assertDatabaseHas('cities', [
            'id' => $city->id,
        ]);
    }

    public function test_city_name_must_be_unique(): void
    {
        $user = User::factory()->create();
        City::factory()->create(['name' => '臺北市']);

        $this->actingAs($user)
            ->postJson('/api/cities', [
                'name' => '臺北市',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['name'])
            ->assertJsonPath('errors.name.0', '此縣市名稱已存在。');
    }
}
