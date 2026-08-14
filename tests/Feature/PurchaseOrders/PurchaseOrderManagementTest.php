<?php

namespace Tests\Feature\PurchaseOrders;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Models\Vendor;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseOrderManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_purchase_orders_page_requires_authentication(): void
    {
        $this->get(route('purchase-orders.index'))->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_view_purchase_orders_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('purchase-orders.index'))
            ->assertOk()
            ->assertSee('採購單')
            ->assertSee('新增採購單');
    }

    public function test_authenticated_users_can_view_create_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('purchase-orders.create'))
            ->assertOk()
            ->assertSee('新增採購單')
            ->assertSee('供應商')
            ->assertSee('進貨倉庫')
            ->assertSee('採購明細');
    }

    public function test_authenticated_users_can_view_show_page(): void
    {
        $user = User::factory()->create();
        $order = PurchaseOrder::factory()->withItems(1)->create();

        $this->actingAs($user)
            ->get(route('purchase-orders.show', $order))
            ->assertOk()
            ->assertSee('檢視採購單')
            ->assertSee('data-purchase-order-show-page', false)
            ->assertSee('編輯');
    }

    public function test_authenticated_users_can_view_edit_page(): void
    {
        $user = User::factory()->create();
        $order = PurchaseOrder::factory()->withItems(1)->create();

        $this->actingAs($user)
            ->get(route('purchase-orders.edit', $order))
            ->assertOk()
            ->assertSee('編輯採購單')
            ->assertSee('採購單號')
            ->assertSee($order->fresh()->code);
    }

    public function test_purchase_orders_can_be_listed_via_api(): void
    {
        $user = User::factory()->create();
        $vendor = Vendor::factory()->create(['name' => '泉源企業']);
        $order = PurchaseOrder::factory()->withItems(1)->create([
            'vendor_id' => $vendor->id,
        ]);

        $this->actingAs($user)
            ->getJson('/api/purchase-orders')
            ->assertOk()
            ->assertJsonPath('data.0.code', $order->fresh()->code)
            ->assertJsonPath('data.0.vendor.name', '泉源企業');
    }

    public function test_purchase_orders_can_be_searched_via_api(): void
    {
        $user = User::factory()->create();
        $vendorA = Vendor::factory()->create(['name' => '泉源企業']);
        $vendorB = Vendor::factory()->create(['name' => '其他廠商']);
        PurchaseOrder::factory()->withItems(1)->create(['vendor_id' => $vendorA->id]);
        PurchaseOrder::factory()->withItems(1)->create(['vendor_id' => $vendorB->id]);

        $this->actingAs($user)
            ->getJson('/api/purchase-orders?search=泉源')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.vendor.name', '泉源企業');
    }

    public function test_purchase_orders_can_be_shown_via_api(): void
    {
        $user = User::factory()->create();
        $product = Product::factory()->create(['name' => '礦泉水 600ml']);
        $vendor = Vendor::factory()->create(['name' => '泉源企業']);
        $warehouse = Warehouse::factory()->create(['name' => '總倉']);
        $product->vendors()->attach($vendor->id);
        $order = PurchaseOrder::factory()->create([
            'vendor_id' => $vendor->id,
            'warehouse_id' => $warehouse->id,
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'quantity' => '10.000',
            'unit_price' => '25.50',
            'amount' => '255.00',
            'sort_order' => 0,
        ]);
        $order->forceFill(['total_amount' => '255.00'])->save();

        $this->actingAs($user)
            ->getJson("/api/purchase-orders/{$order->id}")
            ->assertOk()
            ->assertJsonPath('data.vendor.name', '泉源企業')
            ->assertJsonPath('data.warehouse.name', '總倉')
            ->assertJsonPath('data.items.0.product.name', '礦泉水 600ml')
            ->assertJsonPath('data.total_amount', '255.00')
            ->assertJsonPath('data.code', $order->fresh()->code);
    }

    public function test_purchase_orders_can_be_created_via_api(): void
    {
        $user = User::factory()->create();
        $vendor = Vendor::factory()->create();
        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();
        $product->vendors()->attach($vendor->id);

        $response = $this->actingAs($user)
            ->postJson('/api/purchase-orders', [
                'vendor_id' => $vendor->id,
                'warehouse_id' => $warehouse->id,
                'order_date' => '2026-08-10',
                'expected_date' => '2026-08-15',
                'status' => 'draft',
                'notes' => '測試採購',
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 2,
                        'unit_price' => 100,
                    ],
                ],
            ])
            ->assertCreated()
            ->assertJsonPath('data.vendor_id', $vendor->id)
            ->assertJsonPath('data.warehouse_id', $warehouse->id)
            ->assertJsonPath('data.total_amount', '200.00')
            ->assertJsonPath('data.status', 'draft')
            ->assertJsonPath('data.items.0.product_id', $product->id);

        $code = $response->json('data.code');
        $this->assertMatchesRegularExpression('/^PO\d{6}$/', $code);
        $this->assertDatabaseHas('purchase_orders', [
            'code' => $code,
            'vendor_id' => $vendor->id,
            'total_amount' => '200.00',
        ]);
    }

    public function test_purchase_orders_can_be_updated_via_api(): void
    {
        $user = User::factory()->create();
        $vendor = Vendor::factory()->create();
        $warehouse = Warehouse::factory()->create();
        $productA = Product::factory()->create();
        $productB = Product::factory()->create();
        $productA->vendors()->attach($vendor->id);
        $productB->vendors()->attach($vendor->id);
        $order = PurchaseOrder::factory()->create([
            'vendor_id' => $vendor->id,
            'warehouse_id' => $warehouse->id,
            'status' => PurchaseOrder::STATUS_DRAFT,
        ]);
        $order->items()->create([
            'product_id' => $productA->id,
            'quantity' => '1.000',
            'unit_price' => '10.00',
            'amount' => '10.00',
            'sort_order' => 0,
        ]);

        $this->actingAs($user)
            ->putJson("/api/purchase-orders/{$order->id}", [
                'vendor_id' => $vendor->id,
                'warehouse_id' => $warehouse->id,
                'order_date' => '2026-08-11',
                'expected_date' => null,
                'status' => 'confirmed',
                'notes' => '已確認',
                'items' => [
                    [
                        'product_id' => $productB->id,
                        'quantity' => 3,
                        'unit_price' => 50,
                    ],
                ],
            ])
            ->assertOk()
            ->assertJsonPath('data.status', 'confirmed')
            ->assertJsonPath('data.total_amount', '150.00')
            ->assertJsonPath('data.items.0.product_id', $productB->id)
            ->assertJsonPath('data.code', $order->fresh()->code);

        $this->assertDatabaseMissing('purchase_order_items', [
            'purchase_order_id' => $order->id,
            'product_id' => $productA->id,
        ]);
    }

    public function test_cancelled_purchase_orders_cannot_be_updated(): void
    {
        $user = User::factory()->create();
        $order = PurchaseOrder::factory()->cancelled()->withItems(1)->create();

        $this->actingAs($user)
            ->putJson("/api/purchase-orders/{$order->id}", [
                'vendor_id' => $order->vendor_id,
                'warehouse_id' => $order->warehouse_id,
                'order_date' => $order->order_date->format('Y-m-d'),
                'status' => 'draft',
                'items' => [
                    [
                        'product_id' => $order->items()->first()->product_id,
                        'quantity' => 1,
                        'unit_price' => 10,
                    ],
                ],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['status']);
    }

    public function test_purchase_orders_can_be_deleted_via_api(): void
    {
        $user = User::factory()->create();
        $order = PurchaseOrder::factory()->withItems(1)->create([
            'status' => PurchaseOrder::STATUS_DRAFT,
        ]);

        $this->actingAs($user)
            ->deleteJson("/api/purchase-orders/{$order->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('purchase_orders', ['id' => $order->id]);
        $this->assertDatabaseMissing('purchase_order_items', ['purchase_order_id' => $order->id]);
    }

    public function test_confirmed_purchase_orders_cannot_be_deleted(): void
    {
        $user = User::factory()->create();
        $order = PurchaseOrder::factory()->confirmed()->withItems(1)->create();

        $this->actingAs($user)
            ->deleteJson("/api/purchase-orders/{$order->id}")
            ->assertStatus(422)
            ->assertJsonValidationErrors(['status']);

        $this->assertDatabaseHas('purchase_orders', ['id' => $order->id]);
    }

    public function test_purchase_order_validation_requires_items(): void
    {
        $user = User::factory()->create();
        $vendor = Vendor::factory()->create();
        $warehouse = Warehouse::factory()->create();

        $this->actingAs($user)
            ->postJson('/api/purchase-orders', [
                'vendor_id' => $vendor->id,
                'warehouse_id' => $warehouse->id,
                'order_date' => '2026-08-10',
                'status' => 'draft',
                'items' => [],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['items']);
    }

    public function test_client_provided_code_is_ignored_on_create(): void
    {
        $user = User::factory()->create();
        $vendor = Vendor::factory()->create();
        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();
        $product->vendors()->attach($vendor->id);

        $response = $this->actingAs($user)
            ->postJson('/api/purchase-orders', [
                'code' => 'CUSTOM-CODE',
                'vendor_id' => $vendor->id,
                'warehouse_id' => $warehouse->id,
                'order_date' => '2026-08-10',
                'status' => 'draft',
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 1,
                        'unit_price' => 10,
                    ],
                ],
            ])
            ->assertCreated();

        $this->assertNotSame('CUSTOM-CODE', $response->json('data.code'));
        $this->assertMatchesRegularExpression('/^PO\d{6}$/', $response->json('data.code'));
    }

    public function test_purchase_order_items_must_belong_to_selected_vendor(): void
    {
        $user = User::factory()->create();
        $vendor = Vendor::factory()->create();
        $otherVendor = Vendor::factory()->create();
        $warehouse = Warehouse::factory()->create();
        $product = Product::factory()->create();
        $product->vendors()->attach($otherVendor->id);

        $this->actingAs($user)
            ->postJson('/api/purchase-orders', [
                'vendor_id' => $vendor->id,
                'warehouse_id' => $warehouse->id,
                'order_date' => '2026-08-10',
                'status' => 'draft',
                'items' => [
                    [
                        'product_id' => $product->id,
                        'quantity' => 1,
                        'unit_price' => 10,
                    ],
                ],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['items.0.product_id']);
    }
}
