<?php

namespace Tests\Feature\Vendors;

use App\Models\City;
use App\Models\District;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VendorManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendors_page_requires_authentication(): void
    {
        $this->get(route('vendors.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_view_vendors_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('vendors.index'))
            ->assertOk()
            ->assertSee('廠商管理')
            ->assertSee('廠商');
    }

    public function test_authenticated_users_can_view_create_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('vendors.create'))
            ->assertOk()
            ->assertSee('新增廠商')
            ->assertSee('統一編號')
            ->assertDontSee('廠商代碼');
    }

    public function test_authenticated_users_can_view_show_page(): void
    {
        $user = User::factory()->create();
        $vendor = Vendor::factory()->create([
            'name' => '泉源企業',
        ]);

        $this->actingAs($user)
            ->get(route('vendors.show', $vendor))
            ->assertOk()
            ->assertSee('檢視廠商')
            ->assertSee('查看廠商主檔明細')
            ->assertSee('data-vendor-show-page', false)
            ->assertSee('編輯');
    }

    public function test_authenticated_users_can_view_edit_page(): void
    {
        $user = User::factory()->create();
        $vendor = Vendor::factory()->create([
            'name' => '測試廠商',
        ]);

        $this->actingAs($user)
            ->get(route('vendors.edit', $vendor))
            ->assertOk()
            ->assertSee('編輯廠商')
            ->assertSee('測試廠商')
            ->assertSee('系統編號')
            ->assertSee($vendor->fresh()->code);
    }

    public function test_vendors_can_be_listed_via_api(): void
    {
        $user = User::factory()->create();
        $vendor = Vendor::factory()->create([
            'name' => '台塑企業',
        ]);

        $this->actingAs($user)
            ->getJson('/api/vendors')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonFragment(['code' => $vendor->fresh()->code]);
    }

    public function test_vendors_can_be_shown_via_api(): void
    {
        $user = User::factory()->create();
        $vendor = Vendor::factory()->create([
            'name' => '泉源企業',
            'tax_id' => '12345678',
            'contact_name' => '陳經理',
            'address' => '台中市西屯區',
        ]);

        $this->actingAs($user)
            ->getJson("/api/vendors/{$vendor->id}")
            ->assertOk()
            ->assertJsonPath('data.name', '泉源企業')
            ->assertJsonPath('data.tax_id', '12345678')
            ->assertJsonPath('data.contact_name', '陳經理')
            ->assertJsonPath('data.address', '台中市西屯區')
            ->assertJsonPath('data.code', $vendor->fresh()->code);
    }

    public function test_vendors_can_be_searched_via_api(): void
    {
        $user = User::factory()->create();
        Vendor::factory()->create([
            'name' => '台塑企業',
        ]);
        Vendor::factory()->create([
            'name' => '聯華食品',
        ]);

        $this->actingAs($user)
            ->getJson('/api/vendors?search=聯華')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', '聯華食品');
    }

    public function test_vendors_can_be_created_via_api_with_auto_system_code(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/vendors', [
                'name' => '新進廠商',
                'tax_id' => '12345678',
                'contact_name' => '陳經理',
                'phone' => '0987654321',
                'email' => 'vendor@example.com',
                'address' => '台中市西屯區',
                'notes' => '測試備註',
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('data.tax_id', '12345678');

        $vendorId = $response->json('data.id');
        $expectedCode = 'V'.str_pad((string) $vendorId, 6, '0', STR_PAD_LEFT);

        $response->assertJsonPath('data.code', $expectedCode);

        $this->assertDatabaseHas('vendors', [
            'id' => $vendorId,
            'code' => $expectedCode,
            'tax_id' => '12345678',
            'name' => '新進廠商',
            'contact_name' => '陳經理',
        ]);
    }

    public function test_vendors_ignore_client_provided_code_on_create(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/vendors', [
                'name' => '系統編號廠商',
                'code' => 'CLIENT-CODE',
            ])
            ->assertCreated();

        $vendorId = $response->json('data.id');
        $expectedCode = 'V'.str_pad((string) $vendorId, 6, '0', STR_PAD_LEFT);

        $response->assertJsonPath('data.code', $expectedCode);
        $this->assertDatabaseMissing('vendors', ['code' => 'CLIENT-CODE']);
    }

    public function test_vendors_can_be_updated_via_api_without_changing_system_code(): void
    {
        $user = User::factory()->create();
        $vendor = Vendor::factory()->create([
            'name' => '舊廠商',
            'is_active' => true,
        ]);
        $originalCode = $vendor->fresh()->code;

        $this->actingAs($user)
            ->putJson("/api/vendors/{$vendor->id}", [
                'name' => '更新廠商',
                'code' => 'SHOULD-NOT-CHANGE',
                'tax_id' => '87654321',
                'contact_name' => '李小姐',
                'phone' => '0911111111',
                'email' => 'updated@example.com',
                'address' => '高雄市',
                'notes' => null,
                'is_active' => false,
            ])
            ->assertOk()
            ->assertJsonPath('data.name', '更新廠商')
            ->assertJsonPath('data.code', $originalCode)
            ->assertJsonPath('data.tax_id', '87654321')
            ->assertJsonPath('data.is_active', false);

        $this->assertDatabaseHas('vendors', [
            'id' => $vendor->id,
            'name' => '更新廠商',
            'code' => $originalCode,
            'tax_id' => '87654321',
            'is_active' => false,
        ]);
    }

    public function test_vendors_can_be_deleted_via_api(): void
    {
        $user = User::factory()->create();
        $vendor = Vendor::factory()->create();

        $this->actingAs($user)
            ->deleteJson("/api/vendors/{$vendor->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('vendors', [
            'id' => $vendor->id,
        ]);
    }

    public function test_vendor_tax_id_must_be_unique(): void
    {
        $user = User::factory()->create();
        Vendor::factory()->create(['tax_id' => '11223344']);

        $this->actingAs($user)
            ->postJson('/api/vendors', [
                'name' => '重複統編廠商',
                'tax_id' => '11223344',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['tax_id'])
            ->assertJsonPath('errors.tax_id.0', '此統一編號已存在。');
    }

    public function test_vendors_can_be_created_with_location_fields(): void
    {
        $user = User::factory()->create();
        $city = City::factory()->create(['name' => '臺北市']);
        $district = District::factory()->create([
            'city_id' => $city->id,
            'name' => '中正區',
        ]);

        $this->actingAs($user)
            ->postJson('/api/vendors', [
                'name' => '地址廠商',
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

        $this->assertDatabaseHas('vendors', [
            'name' => '地址廠商',
            'postal_code' => '100',
            'city_id' => $city->id,
            'district_id' => $district->id,
            'address' => '重慶南路一段122號',
        ]);
    }

    public function test_vendor_district_must_belong_to_selected_city(): void
    {
        $user = User::factory()->create();
        $cityA = City::factory()->create(['name' => '臺北市']);
        $cityB = City::factory()->create(['name' => '新北市']);
        $districtB = District::factory()->create([
            'city_id' => $cityB->id,
            'name' => '板橋區',
        ]);

        $this->actingAs($user)
            ->postJson('/api/vendors', [
                'name' => '錯誤區域廠商',
                'city_id' => $cityA->id,
                'district_id' => $districtB->id,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['district_id']);
    }
}
