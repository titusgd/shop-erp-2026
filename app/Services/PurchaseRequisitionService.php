<?php

namespace App\Services;

use App\Models\PurchaseRequisition;
use App\Models\PurchaseRequisitionItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseRequisitionService
{
    /**
     * @param  array{
     *     search?: string|null,
     *     requester_id?: int|null,
     *     warehouse_id?: int|null,
     *     status?: string|null,
     *     per_page?: int|null
     * }  $filters
     * @return LengthAwarePaginator<int, PurchaseRequisition>
     */
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $requesterId = $filters['requester_id'] ?? null;
        $warehouseId = $filters['warehouse_id'] ?? null;
        $status = trim((string) ($filters['status'] ?? ''));
        $perPage = (int) ($filters['per_page'] ?? 10);
        $perPage = max(1, min($perPage, 50));

        return PurchaseRequisition::query()
            ->with(['requester', 'warehouse', 'items.product.unit'])
            ->when($requesterId, fn ($query) => $query->where('requester_id', $requesterId))
            ->when($warehouseId, fn ($query) => $query->where('warehouse_id', $warehouseId))
            ->when($status !== '', fn ($query) => $query->where('status', $status))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('code', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%")
                        ->orWhereHas('requester', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%")
                                ->orWhere('username', 'like', "%{$search}%");
                        })
                        ->orWhereHas('warehouse', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%")
                                ->orWhere('code', 'like', "%{$search}%");
                        });
                });
            })
            ->orderByDesc('request_date')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array{
     *     requester_id: int,
     *     warehouse_id: int,
     *     request_date: string,
     *     required_date?: string|null,
     *     status?: string|null,
     *     notes?: string|null,
     *     items: list<array{
     *         product_id: int,
     *         quantity: numeric,
     *         notes?: string|null
     *     }>
     * }  $data
     */
    public function create(array $data): PurchaseRequisition
    {
        return DB::transaction(function () use ($data) {
            $normalizedItems = $this->normalizeItems($data['items'] ?? []);

            $requisition = PurchaseRequisition::query()->create([
                'code' => null,
                'requester_id' => $data['requester_id'],
                'warehouse_id' => $data['warehouse_id'],
                'request_date' => $data['request_date'],
                'required_date' => $data['required_date'] ?? null,
                'status' => $data['status'] ?? PurchaseRequisition::STATUS_DRAFT,
                'notes' => $this->nullableString($data['notes'] ?? null),
            ]);

            $requisition->code = $this->formatSystemCode($requisition->id);
            $requisition->save();

            $this->syncItems($requisition, $normalizedItems);

            return $requisition->refresh()->load(['requester', 'warehouse', 'items.product.unit']);
        });
    }

    /**
     * @param  array{
     *     requester_id: int,
     *     warehouse_id: int,
     *     request_date: string,
     *     required_date?: string|null,
     *     status?: string|null,
     *     notes?: string|null,
     *     items: list<array{
     *         product_id: int,
     *         quantity: numeric,
     *         notes?: string|null
     *     }>
     * }  $data
     */
    public function update(PurchaseRequisition $purchaseRequisition, array $data): PurchaseRequisition
    {
        if ($purchaseRequisition->status === PurchaseRequisition::STATUS_CANCELLED) {
            throw ValidationException::withMessages([
                'status' => '已取消的請購單不可修改。',
            ]);
        }

        return DB::transaction(function () use ($purchaseRequisition, $data) {
            $normalizedItems = $this->normalizeItems($data['items'] ?? []);

            $purchaseRequisition->fill([
                'requester_id' => $data['requester_id'],
                'warehouse_id' => $data['warehouse_id'],
                'request_date' => $data['request_date'],
                'required_date' => $data['required_date'] ?? null,
                'status' => $data['status'] ?? $purchaseRequisition->status,
                'notes' => $this->nullableString($data['notes'] ?? null),
            ]);
            $purchaseRequisition->save();

            $this->syncItems($purchaseRequisition, $normalizedItems);

            return $purchaseRequisition->refresh()->load(['requester', 'warehouse', 'items.product.unit']);
        });
    }

    public function delete(PurchaseRequisition $purchaseRequisition): void
    {
        if ($purchaseRequisition->status === PurchaseRequisition::STATUS_CONFIRMED) {
            throw ValidationException::withMessages([
                'status' => '已確認的請購單不可刪除，請先取消後再刪除。',
            ]);
        }

        DB::transaction(function () use ($purchaseRequisition) {
            $purchaseRequisition->items()->delete();
            $purchaseRequisition->delete();
        });
    }

    public function formatSystemCode(int $id): string
    {
        return 'PR'.str_pad((string) $id, 6, '0', STR_PAD_LEFT);
    }

    /**
     * @param  list<array{product_id: int, quantity: numeric, notes?: string|null}>  $items
     * @return list<array{product_id: int, quantity: string, notes: string|null, sort_order: int}>
     */
    private function normalizeItems(array $items): array
    {
        if ($items === []) {
            throw ValidationException::withMessages([
                'items' => '請至少新增一筆請購明細。',
            ]);
        }

        $normalized = [];

        foreach (array_values($items) as $index => $item) {
            $quantity = round((float) $item['quantity'], 3);

            if ($quantity <= 0) {
                throw ValidationException::withMessages([
                    "items.{$index}.quantity" => '數量必須大於 0。',
                ]);
            }

            $normalized[] = [
                'product_id' => (int) $item['product_id'],
                'quantity' => number_format($quantity, 3, '.', ''),
                'notes' => $this->nullableString($item['notes'] ?? null),
                'sort_order' => $index,
            ];
        }

        return $normalized;
    }

    /**
     * @param  list<array{product_id: int, quantity: string, notes: string|null, sort_order: int}>  $items
     */
    private function syncItems(PurchaseRequisition $purchaseRequisition, array $items): void
    {
        $purchaseRequisition->items()->delete();

        foreach ($items as $item) {
            PurchaseRequisitionItem::query()->create([
                'purchase_requisition_id' => $purchaseRequisition->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
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
