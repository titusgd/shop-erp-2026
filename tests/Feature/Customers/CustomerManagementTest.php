<?php

namespace Tests\Feature\Customers;

use App\Models\City;
use App\Models\Customer;
use App\Models\District;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_customers_page_requires_authentication(): void
    {
        $this->get(route('customers.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_view_customers_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('customers.index'))
            ->assertOk()
            ->assertSee('客戶管理')
            ->assertSee('客戶');
    }

    public function test_authenticated_users_can_view_create_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('customers.create'))
            ->assertOk()
            ->assertSee('新增客戶')
            ->assertSee('統一編號')
            ->assertSee('郵遞區號')
            ->assertDontSee('客戶代碼');
    }

    public function test_authenticated_users_can_view_show_page(): void
    {
        $user = User::factory()->create();
        $customer = Customer::factory()->create([
            'name' => '泉源企業',
        ]);

        $this->actingAs($user)
            ->get(route('customers.show', $customer))
            ->assertOk()
            ->assertSee('檢視客戶')
            ->assertSee('查看客戶主檔明細')
            ->assertSee('data-customer-show-page', false)
            ->assertSee('編輯');
    }

    public function test_authenticated_users_can_view_edit_page(): void
    {
        $user = User::factory()->create();
        $customer = Customer::factory()->create([
            'name' => '測試客戶',
        ]);

        $this->actingAs($user)
            ->get(route('customers.edit', $customer))
            ->assertOk()
            ->assertSee('編輯客戶')
            ->assertSee('測試客戶')
            ->assertSee('系統編號')
            ->assertSee($customer->fresh()->code);
    }

    public function test_customers_can_be_listed_via_api(): void
    {
        $user = User::factory()->create();
        $customer = Customer::factory()->create([
            'name' => '台塑企業',
        ]);

        $this->actingAs($user)
            ->getJson('/api/customers')
            ->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonFragment(['code' => $customer->fresh()->code]);
    }

    public function test_customers_can_be_shown_via_api(): void
    {
        $user = User::factory()->create();
        $customer = Customer::factory()->create([
            'name' => '泉源企業',
            'tax_id' => '12345678',
            'contact_name' => '陳經理',
            'address' => '台中市西屯區',
        ]);

        $this->actingAs($user)
            ->getJson("/api/customers/{$customer->id}")
            ->assertOk()
            ->assertJsonPath('data.name', '泉源企業')
            ->assertJsonPath('data.tax_id', '12345678')
            ->assertJsonPath('data.contact_name', '陳經理')
            ->assertJsonPath('data.address', '台中市西屯區')
            ->assertJsonPath('data.code', $customer->fresh()->code);
    }

    public function test_customers_can_be_searched_via_api(): void
    {
        $user = User::factory()->create();
        Customer::factory()->create([
            'name' => '台塑企業',
        ]);
        Customer::factory()->create([
            'name' => '聯華食品',
        ]);

        $this->actingAs($user)
            ->getJson('/api/customers?search=聯華')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', '聯華食品');
    }

    public function test_customers_can_be_created_via_api_with_auto_system_code(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/customers', [
                'name' => '新進客戶',
                'tax_id' => '12345678',
                'contact_name' => '陳經理',
                'phone' => '0987654321',
                'email' => 'customer@example.com',
                'address' => '台中市西屯區',
                'notes' => '測試備註',
                'is_active' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('data.tax_id', '12345678');

        $customerId = $response->json('data.id');
        $expectedCode = 'C'.str_pad((string) $customerId, 6, '0', STR_PAD_LEFT);

        $response->assertJsonPath('data.code', $expectedCode);

        $this->assertDatabaseHas('customers', [
            'id' => $customerId,
            'code' => $expectedCode,
            'tax_id' => '12345678',
            'name' => '新進客戶',
            'contact_name' => '陳經理',
        ]);
    }

    public function test_customers_can_be_created_with_location_fields(): void
    {
        $user = User::factory()->create();
        $city = City::factory()->create(['name' => '臺北市']);
        $district = District::factory()->create([
            'city_id' => $city->id,
            'name' => '中正區',
        ]);

        $this->actingAs($user)
            ->postJson('/api/customers', [
                'name' => '地址客戶',
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

        $this->assertDatabaseHas('customers', [
            'name' => '地址客戶',
            'postal_code' => '100',
            'city_id' => $city->id,
            'district_id' => $district->id,
            'address' => '重慶南路一段122號',
        ]);
    }

    public function test_customer_district_must_belong_to_selected_city(): void
    {
        $user = User::factory()->create();
        $cityA = City::factory()->create(['name' => '臺北市']);
        $cityB = City::factory()->create(['name' => '新北市']);
        $districtB = District::factory()->create([
            'city_id' => $cityB->id,
            'name' => '板橋區',
        ]);

        $this->actingAs($user)
            ->postJson('/api/customers', [
                'name' => '錯誤區域客戶',
                'city_id' => $cityA->id,
                'district_id' => $districtB->id,
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['district_id']);
    }

    public function test_customers_ignore_client_provided_code_on_create(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/customers', [
                'name' => '系統編號客戶',
                'code' => 'CLIENT-CODE',
            ])
            ->assertCreated();

        $customerId = $response->json('data.id');
        $expectedCode = 'C'.str_pad((string) $customerId, 6, '0', STR_PAD_LEFT);

        $response->assertJsonPath('data.code', $expectedCode);
        $this->assertDatabaseMissing('customers', ['code' => 'CLIENT-CODE']);
    }

    public function test_customers_can_be_updated_via_api_without_changing_system_code(): void
    {
        $user = User::factory()->create();
        $customer = Customer::factory()->create([
            'name' => '舊客戶',
            'is_active' => true,
        ]);
        $originalCode = $customer->fresh()->code;

        $this->actingAs($user)
            ->putJson("/api/customers/{$customer->id}", [
                'name' => '更新客戶',
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
            ->assertJsonPath('data.name', '更新客戶')
            ->assertJsonPath('data.code', $originalCode)
            ->assertJsonPath('data.tax_id', '87654321')
            ->assertJsonPath('data.is_active', false);

        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'name' => '更新客戶',
            'code' => $originalCode,
            'tax_id' => '87654321',
            'is_active' => false,
        ]);
    }

    public function test_customers_can_be_deleted_via_api(): void
    {
        $user = User::factory()->create();
        $customer = Customer::factory()->create();

        $this->actingAs($user)
            ->deleteJson("/api/customers/{$customer->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('customers', [
            'id' => $customer->id,
        ]);
    }

    public function test_customer_tax_id_must_be_unique(): void
    {
        $user = User::factory()->create();
        Customer::factory()->create(['tax_id' => '11223344']);

        $this->actingAs($user)
            ->postJson('/api/customers', [
                'name' => '重複統編客戶',
                'tax_id' => '11223344',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['tax_id'])
            ->assertJsonPath('errors.tax_id.0', '此統一編號已存在。');
    }
}
