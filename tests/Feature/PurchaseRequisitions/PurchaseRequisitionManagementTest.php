<?php

namespace Tests\Feature\PurchaseRequisitions;

use App\Models\Product;
use App\Models\PurchaseRequisition;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseRequisitionManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchase_requisitions_page_requires_authentication(): void
    {
        $this->get(route('purchase-requisitions.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_view_purchase_requisitions_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('purchase-requisitions.index'))
            ->assertOk()
            ->assertSee('請購單')
            ->assertSee('新增請購單');
    }

    public function test_authenticated_users_can_view_create_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('purchase-requisitions.create'))
            ->assertOk()
            ->assertSee('新增請購單')
            ->assertSee('請購人')
            ->assertSee('進貨倉庫')
            ->assertSee('請購明細')
            ->assertSee('可輸入商品編號或名稱搜尋');
    }

    public function test_authenticated_users_can_view_show_page(): void
    {
        $user = User::factory()->create();
        $requisition = PurchaseRequisition::factory()->withItems(1)->create();

        $this->actingAs($user)
            ->get(route('purchase-requisitions.show', $requisition))
            ->assertOk()
            ->assertSee('檢視請購單')
            ->assertSee('data-purchase-requisition-show-page', false)
            ->assertSee('編輯');
    }

    public function test_authenticated_users_can_view_edit_page(): void
    {
        $user = User::factory()->create();
        $requisition = PurchaseRequisition::factory()->withItems(1)->create();

        $this->actingAs($user)
            ->get(route('purchase-requisitions.edit', $requisition))
            ->assertOk()
            ->assertSee('編輯請購單')
            ->assertSee('請購單號')
            ->assertSee($requisition->fresh()->code);
    }

    public function test_purchase_requisitions_can_be_listed_via_api(): void
    {
        $user = User::factory()->create();
        $requester = User::factory()->create(['name' => '王小明']);
        $requisition = PurchaseRequisition::factory()->withItems(1)->create([
            'requester_id' => $requester->id,
        ]);

        $this->actingAs($user)
            ->getJson('/api/purchase-requisitions')
            ->assertOk()
            ->assertJsonPath('data.0.code', $requisition->fresh()->code)
            ->assertJsonPath('data.0.requester.name', '王小明');
    }

    public function test_purchase_requisitions_can_be_searched_via_api(): void
    {
        $user = User::factory()->create();
        $requesterA = User::factory()->create(['name' => '王小明']);
        $requesterB = User::factory()->create(['name' => '其他人員']);
        PurchaseRequisition::factory()->withItems(1)->create(['requester_id' => $requesterA->id]);
        PurchaseRequisition::factory()->withItems(1)->create(['requester_id' => $requesterB->id]);

        $this->actingAs($user)
            ->getJson('/api/purchase-requisitions?search=王小明')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.requester.name', '王小明');
    }

    public function test_purchase_requisitions_can_be_shown_via_api(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['name' => '礦泉水 600ml']);
        $requester = User::factory()->create(['name' => '王小明']);
        $warehouse = Warehouse::factory()->create(['name' => '總倉']);
        $requisition = PurchaseRequisition::factory()->create([
            'requester_id' => $requester->id,
            'warehouse_id' => $warehouse->id,
        ]);

        $requisition->items()->create([
            'product_id' => $product->id,
            'quantity' => '10.000',
            'sort_order' => 0,
        ]);

        $this->actingAs($user)
            ->getJson("/api/purchase-requisitions/{$requisition->id}")
            ->assertOk()
            ->assertJsonPath('data.requester.name', '王小明')
            ->assertJsonPath('data.warehouse.name', '總倉')
            ->assertJsonPath('data.items.0.product.name', '礦泉水 600ml')
            ->assertJsonPath('data.code', $requisition->fresh()->code);
    }

    public function test_purchase_requisitions_can_be_created_via_api(): void
    {
        $user = User::factory()->create();
        $requester = User::factory()->create();
        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/purchase-requisitions', [
                'requester_id' => $requester->id,
                'warehouse_id' => $warehouse->id,
                'request_date' => '2026-08-10',
                'required_date' => '2026-08-15',
                'status' => 'draft',
                'notes' => '測試請購',
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 2,
                    ],
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('data.requester_id', $requester->id)
            ->assertJsonPath('data.warehouse_id', $warehouse->id)
            ->assertJsonPath('data.status', 'draft')
            ->assertJsonPath('data.items.0.product_id', $product->id);

        $code = $response->json('data.code');
        $this->assertMatchesRegularExpression('/^PR\d{6}$/', $code);
        $this->assertDatabaseHas('purchase_requisitions', [
            'code' => $code,
            'requester_id' => $requester->id,
        ]);
    }

    public function test_purchase_requisitions_can_be_updated_via_api(): void
    {
        $user = User::factory()->create();
        $requester = User::factory()->create();
        $warehouse = Warehouse::factory()->create();
        $productA = Product::factory()->create();
        $productB = Product::factory()->create();
        $requisition = PurchaseRequisition::factory()->create([
            'requester_id' => $requester->id,
            'warehouse_id' => $warehouse->id,
            'status' => PurchaseRequisition::STATUS_DRAFT,
        ]);
        $requisition->items()->create([
            'product_id' => $productA->id,
            'quantity' => '1.000',
            'sort_order' => 0,
        ]);

        $this->actingAs($user)
            ->putJson("/api/purchase-requisitions/{$requisition->id}", [
                'requester_id' => $requester->id,
                'warehouse_id' => $warehouse->id,
                'request_date' => '2026-08-11',
                'required_date' => null,
                'status' => 'confirmed',
                'notes' => '已確認',
                'items' => [
                    [
                        'product_id' => $productB->id,
                        'quantity' => 3,
                    ],
                ],
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'confirmed')
            ->assertJsonPath('data.items.0.product_id', $productB->id)
            ->assertJsonPath('data.code', $requisition->fresh()->code);

        $this->assertDatabaseMissing('purchase_requisition_items', [
            'purchase_requisition_id' => $requisition->id,
            'product_id' => $productA->id,
        ]);
    }

    public function test_cancelled_purchase_requisitions_cannot_be_updated(): void
    {
        $user = User::factory()->create();
        $requisition = PurchaseRequisition::factory()->cancelled()->withItems(1)->create();

        $this->actingAs($user)
            ->putJson("/api/purchase-requisitions/{$requisition->id}", [
                'requester_id' => $requisition->requester_id,
                'warehouse_id' => $requisition->warehouse_id,
                'request_date' => $requisition->request_date->format('Y-m-d'),
                'status' => 'draft',
                'items' => [
                    [
                        'product_id' => $requisition->items()->first()->product_id,
                        'quantity' => 1,
                    ],
                ],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_purchase_requisitions_can_be_deleted_via_api(): void
    {
        $user = User::factory()->create();
        $requisition = PurchaseRequisition::factory()->withItems(1)->create([
            'status' => PurchaseRequisition::STATUS_DRAFT,
        ]);

        $this->actingAs($user)
            ->deleteJson("/api/purchase-requisitions/{$requisition->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('purchase_requisitions', ['id' => $requisition->id]);
        $this->assertDatabaseMissing('purchase_requisition_items', ['purchase_requisition_id' => $requisition->id]);
    }

    public function test_confirmed_purchase_requisitions_cannot_be_deleted(): void
    {
        $user = User::factory()->create();
        $requisition = PurchaseRequisition::factory()->confirmed()->withItems(1)->create();

        $this->actingAs($user)
            ->deleteJson("/api/purchase-requisitions/{$requisition->id}")
            ->assertStatus(422)
            ->assertJsonValidationErrors(['status']);

        $this->assertDatabaseHas('purchase_requisitions', ['id' => $requisition->id]);
    }

    public function test_purchase_requisition_validation_requires_items(): void
    {
        $user = User::factory()->create();
        $requester = User::factory()->create();
        $warehouse = Warehouse::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/purchase-requisitions', [
                'requester_id' => $requester->id,
                'warehouse_id' => $warehouse->id,
                'request_date' => '2026-08-10',
                'status' => 'draft',
                'items' => [],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['items']);
    }

    public function test_client_provided_code_is_ignored_on_create(): void
    {
        $user = User::factory()->create();
        $requester = User::factory()->create();
        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();

        $response = $this->actingAs($user)
            ->postJson('/api/purchase-requisitions', [
                'code' => 'CUSTOM-CODE',
                'requester_id' => $requester->id,
                'warehouse_id' => $warehouse->id,
                'request_date' => '2026-08-10',
                'status' => 'draft',
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 1,
                    ],
                ],
            ])
            ->assertCreated();

        $this->assertNotSame('CUSTOM-CODE', $response->json('data.code'));
        $this->assertMatchesRegularExpression('/^PR\d{6}$/', $response->json('data.code'));
    }

    public function test_purchase_requisition_items_cannot_repeat_the_same_product(): void
    {
        $user = User::factory()->create();
        $requester = User::factory()->create();
        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/purchase-requisitions', [
                'requester_id' => $requester->id,
                'warehouse_id' => $warehouse->id,
                'request_date' => '2026-08-10',
                'status' => 'draft',
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 1,
                    ],
                    [
                        'product_id' => $product->id,
                        'quantity' => 2,
                    ],
                ],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.product_id', 'items.1.product_id']);
    }
}
