<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseOrderService
{
    /**
     * @param  array{
     *     search?: string|null,
     *     vendor_id?: int|null,
     *     warehouse_id?: int|null,
     *     status?: string|null,
     *     per_page?: int|null
     * }  $filters
     * @return LengthAwarePaginator<int, PurchaseOrder>
     */
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $vendorId = $filters['vendor_id'] ?? null;
        $warehouseId = $filters['warehouse_id'] ?? null;
        $status = trim((string) ($filters['status'] ?? ''));
        $perPage = (int) ($filters['per_page'] ?? 10);
        $perPage = max(1, min($perPage, 50));

        return PurchaseOrder::query()
            ->with(['vendor', 'warehouse', 'items.product.unit'])
            ->when($vendorId, fn ($query) => $query->where('vendor_id', $vendorId))
            ->when($warehouseId, fn ($query) => $query->where('warehouse_id', $warehouseId))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('code', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%")
                        ->orWhereHas('vendor', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%")
                                ->orWhere('code', 'like', "%{$search}%");
                        })
                        ->orWhereHas('warehouse', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%")
                                ->orWhere('code', 'like', "%{$search}%");
                        });
                });
            })
            ->orderByDesc('order_date')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array{
     *     vendor_id: int,
     *     warehouse_id: int,
     *     order_date: string,
     *     expected_date?: string|null,
     *     status?: string|null,
     *     notes?: string|null,
     *     items: list<array{
     *         product_id: int,
     *         quantity: numeric,
     *         unit_price: numeric,
     *         notes?: string|null
     *     }>
     * }  $data
     */
    public function create(array $data): PurchaseOrder
    {
        return DB::transaction(function () use ($data) {
            $normalizedItems = $this->normalizeItems($data['items'] ?? []);
            $totalAmount = $this->sumAmounts($normalizedItems);

            $order = PurchaseOrder::query()->create([
                'code' => null,
                'vendor_id' => $data['vendor_id'],
                'warehouse_id' => $data['warehouse_id'],
                'order_date' => $data['order_date'],
                'expected_date' => $data['expected_date'] ?? null,
                'status' => $data['status'] ?? PurchaseOrder::STATUS_DRAFT,
                'total_amount' => $totalAmount,
                'notes' => $this->nullableString($data['notes'] ?? null),
            ]);

            $order->code = $this->formatSystemCode($order->id);
            $order->save();

            $this->syncItems($order, $normalizedItems);

            return $order->refresh()->load(['vendor', 'warehouse', 'items.product.unit']);
        });
    }

    /**
     * @param  array{
     *     vendor_id: int,
     *     warehouse_id: int,
     *     order_date: string,
     *     expected_date?: string|null,
     *     status?: string|null,
     *     notes?: string|null,
     *     items: list<array{
     *         product_id: int,
     *         quantity: numeric,
     *         unit_price: numeric,
     *         notes?: string|null
     *     }>
     * }  $data
     */
    public function update(PurchaseOrder $purchaseOrder, array $data): PurchaseOrder
    {
        if ($purchaseOrder->status === PurchaseOrder::STATUS_CANCELLED) {
            throw ValidationException::withMessages([
                'status' => '已取消的採購單不可修改。',
            ]);
        }

        return DB::transaction(function () use ($purchaseOrder, $data) {
            $normalizedItems = $this->normalizeItems($data['items'] ?? []);
            $totalAmount = $this->sumAmounts($normalizedItems);

            $purchaseOrder->fill([
                'vendor_id' => $data['vendor_id'],
                'warehouse_id' => $data['warehouse_id'],
                'order_date' => $data['order_date'],
                'expected_date' => $data['expected_date'] ?? null,
                'status' => $data['status'] ?? $purchaseOrder->status,
                'total_amount' => $totalAmount,
                'notes' => $this->nullableString($data['notes'] ?? null),
            ]);
            $purchaseOrder->save();

            $this->syncItems($purchaseOrder, $normalizedItems);

            return $purchaseOrder->refresh()->load(['vendor', 'warehouse', 'items.product.unit']);
        });
    }

    public function delete(PurchaseOrder $purchaseOrder): void
    {
        if ($purchaseOrder->status === PurchaseOrder::STATUS_CONFIRMED) {
            throw ValidationException::withMessages([
                'status' => '已確認的採購單不可刪除，請先取消後再刪除。',
            ]);
        }

        DB::transaction(function () use ($purchaseOrder) {
            $purchaseOrder->items()->delete();
            $purchaseOrder->delete();
        });
    }

    public function formatSystemCode(int $id): string
    {
        return 'PO'.str_pad((string) $id, 6, '0', STR_PAD_LEFT);
    }

    /**
     * @param  list<array{product_id: int, quantity: numeric, unit_price: numeric, notes?: string|null}>  $items
     * @return list<array{product_id: int, quantity: string, unit_price: string, amount: string, notes: string|null, sort_order: int}>
     */
    private function normalizeItems(array $items): array
    {
        if ($items === []) {
            throw ValidationException::withMessages([
                'items' => '請至少新增一筆採購明細。',
            ]);
        }

        $normalized = [];

        foreach (array_values($items) as $index => $item) {
            $quantity = round((float) $item['quantity'], 3);
            $unitPrice = round((float) $item['unit_price'], 2);

            if ($quantity <= 0) {
                throw ValidationException::withMessages([
                    "items.{$index}.quantity" => '數量必須大於 0。',
                ]);
            }

            if ($unitPrice < 0) {
                throw ValidationException::withMessages([
                    "items.{$index}.unit_price" => '單價不可為負數。',
                ]);
            }

            $amount = round($quantity * $unitPrice, 2);

            $normalized[] = [
                'product_id' => (int) $item['product_id'],
                'quantity' => number_format($quantity, 3, '.', ''),
                'unit_price' => number_format($unitPrice, 2, '.', ''),
                'amount' => number_format($amount, 2, '.', ''),
                'notes' => $this->nullableString($item['notes'] ?? null),
                'sort_order' => $index,
            ];
        }

        return $normalized;
    }

    /**
     * @param  list<array{amount: string}>  $items
     */
    private function sumAmounts(array $items): string
    {
        $total = 0.0;

        foreach ($items as $item) {
            $total += (float) $item['amount'];
        }

        return number_format(round($total, 2), 2, '.', '');
    }

    /**
     * @param  list<array{product_id: int, quantity: string, unit_price: string, amount: string, notes: string|null, sort_order: int}>  $items
     */
    private function syncItems(PurchaseOrder $purchaseOrder, array $items): void
    {
        $purchaseOrder->items()->delete();

        foreach ($items as $item) {
            PurchaseOrderItem::query()->create([
                'purchase_order_id' => $purchaseOrder->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'amount' => $item['amount'],
                'notes' => $item['notes'],
                'sort_order' => $item['sort_order'],
            ]);
        }
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }
}
