<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductPriceHistory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductService
{
    /**
     * @param  array{
     *     search?: string|null,
     *     product_category_id?: int|null,
     *     product_unit_id?: int|null,
     *     vendor_id?: int|null,
     *     per_page?: int|null,
     *     active_only?: bool|null
     * }  $filters
     * @return LengthAwarePaginator<int, Product>
     */
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $categoryId = $filters['product_category_id'] ?? null;
        $unitId = $filters['product_unit_id'] ?? null;
        $vendorId = $filters['vendor_id'] ?? null;
        $perPage = (int) ($filters['per_page'] ?? 10);
        $perPage = max(1, min($perPage, 50));
        $activeOnly = (bool) ($filters['active_only'] ?? false);

        return Product::query()
            ->with(['category', 'unit', 'vendors'])
            ->when($activeOnly, fn ($query) => $query->where('is_active', true))
            ->when($categoryId, fn ($query) => $query->where('product_category_id', $categoryId))
            ->when($unitId, fn ($query) => $query->where('product_unit_id', $unitId))
            ->when($vendorId, fn ($query) => $query->whereHas(
                'vendors',
                fn ($query) => $query->where('vendors.id', $vendorId),
            ))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhereHas('category', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('unit', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('vendors', function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array{
     *     product_category_id: int,
     *     product_unit_id: int,
     *     vendor_ids?: list<int>|null,
     *     vendor_purchase_prices?: array<int|string, numeric|null>|null,
     *     name: string,
     *     notes?: string|null,
     *     estimated_selling_price?: numeric|null,
     *     is_active?: bool|null
     * }  $data
     */
    public function create(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            $sellingPrice = $this->normalizePrice($data['estimated_selling_price'] ?? null);

            $product = Product::query()->create([
                'product_category_id' => $data['product_category_id'],
                'product_unit_id' => $data['product_unit_id'],
                'name' => $data['name'],
                'code' => null,
                'notes' => $this->nullableString($data['notes'] ?? null),
                'estimated_selling_price' => $sellingPrice,
                'is_active' => (bool) ($data['is_active'] ?? true),
            ]);

            $product->code = $this->formatSystemCode($product->id);
            $product->save();

            $this->syncVendors(
                $product,
                $this->normalizeIds($data['vendor_ids'] ?? []),
                $data['vendor_purchase_prices'] ?? [],
            );

            $product->load('vendors');

            $changes = array_merge(
                $this->vendorPurchasePriceChanges(collect(), $product->vendors),
                $this->sellingPriceChanges(null, $sellingPrice),
            );

            if ($changes !== []) {
                $this->recordPriceHistory($product, 'created', $changes);
            }

            return $product->refresh()->load(['category', 'unit', 'vendors']);
        });
    }

    /**
     * @param  array{
     *     product_category_id: int,
     *     product_unit_id: int,
     *     vendor_ids?: list<int>|null,
     *     vendor_purchase_prices?: array<int|string, numeric|null>|null,
     *     name: string,
     *     notes?: string|null,
     *     estimated_selling_price?: numeric|null,
     *     is_active?: bool|null
     * }  $data
     */
    public function update(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data) {
            $product->load('vendors');
            $beforeVendors = $product->vendors;
            $beforeSellingPrice = $this->normalizePrice($product->estimated_selling_price);

            $sellingPrice = array_key_exists('estimated_selling_price', $data)
                ? $this->normalizePrice($data['estimated_selling_price'])
                : $beforeSellingPrice;

            $product->fill([
                'product_category_id' => $data['product_category_id'],
                'product_unit_id' => $data['product_unit_id'],
                'name' => $data['name'],
                'notes' => $this->nullableString($data['notes'] ?? null),
                'estimated_selling_price' => $sellingPrice,
                'is_active' => (bool) ($data['is_active'] ?? true),
            ]);
            $product->save();

            if (array_key_exists('vendor_ids', $data)) {
                $this->syncVendors(
                    $product,
                    $this->normalizeIds($data['vendor_ids'] ?? []),
                    $data['vendor_purchase_prices'] ?? [],
                );
            }

            $product->load('vendors');

            $changes = array_merge(
                $this->vendorPurchasePriceChanges($beforeVendors, $product->vendors),
                $this->sellingPriceChanges($beforeSellingPrice, $sellingPrice),
            );

            if ($changes !== []) {
                $this->recordPriceHistory($product, 'updated', $changes);
            }

            return $product->refresh()->load(['category', 'unit', 'vendors']);
        });
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }

    /**
     * @return Collection<int, ProductPriceHistory>
     */
    public function priceHistories(Product $product, ?string $field = null): Collection
    {
        $histories = $product->priceHistories()->with('user')->get();

        if (! in_array($field, ['estimated_purchase_price', 'estimated_selling_price'], true)) {
            return $histories;
        }

        return $histories
            ->map(function (ProductPriceHistory $history) use ($field) {
                $changes = array_values(array_filter(
                    $history->changes ?? [],
                    fn ($change) => ($change['field'] ?? null) === $field,
                ));

                if ($changes === []) {
                    return null;
                }

                $history->changes = $changes;

                return $history;
            })
            ->filter()
            ->values();
    }

    public function formatSystemCode(int $id): string
    {
        return 'P'.str_pad((string) $id, 6, '0', STR_PAD_LEFT);
    }

    /**
     * @param  list<int|string>|null  $ids
     * @return list<int>
     */
    private function normalizeIds(?array $ids): array
    {
        return array_values(array_map('intval', $ids ?? []));
    }

    /**
     * @param  list<int>  $vendorIds
     * @param  array<int|string, mixed>  $purchasePrices
     */
    private function syncVendors(Product $product, array $vendorIds, array $purchasePrices): void
    {
        $sync = [];

        foreach ($vendorIds as $vendorId) {
            $sync[$vendorId] = [
                'estimated_purchase_price' => $this->normalizePrice(
                    $purchasePrices[$vendorId] ?? $purchasePrices[(string) $vendorId] ?? null,
                ),
            ];
        }

        $product->vendors()->sync($sync);
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }

    private function normalizePrice(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return number_format((float) $value, 2, '.', '');
    }

    /**
     * @param  Collection<int, \App\Models\Vendor>  $beforeVendors
     * @param  Collection<int, \App\Models\Vendor>  $afterVendors
     * @return list<array{field: string, label: string, vendor_id: int, vendor_name: string, old: mixed, new: mixed}>
     */
    private function vendorPurchasePriceChanges(Collection $beforeVendors, Collection $afterVendors): array
    {
        $before = $beforeVendors->mapWithKeys(fn ($vendor) => [(int) $vendor->id => [
            'name' => $vendor->name,
            'price' => $this->normalizePrice($vendor->pivot?->estimated_purchase_price),
        ]]);
        $after = $afterVendors->mapWithKeys(fn ($vendor) => [(int) $vendor->id => [
            'name' => $vendor->name,
            'price' => $this->normalizePrice($vendor->pivot?->estimated_purchase_price),
        ]]);

        $changes = [];

        foreach ($before->keys()->merge($after->keys())->unique()->map(fn ($id) => (int) $id) as $vendorId) {
            $oldPrice = $before[$vendorId]['price'] ?? null;
            $newPrice = $after[$vendorId]['price'] ?? null;

            if ($oldPrice === $newPrice) {
                continue;
            }

            $vendorName = $after[$vendorId]['name'] ?? $before[$vendorId]['name'] ?? '供應商';

            $changes[] = [
                'field' => 'estimated_purchase_price',
                'vendor_id' => (int) $vendorId,
                'vendor_name' => $vendorName,
                'label' => "預計進價（{$vendorName}）",
                'old' => $oldPrice,
                'new' => $newPrice,
            ];
        }

        return $changes;
    }

    /**
     * @return list<array{field: string, label: string, old: mixed, new: mixed}>
     */
    private function sellingPriceChanges(?string $before, ?string $after): array
    {
        if ($before === $after) {
            return [];
        }

        return [[
            'field' => 'estimated_selling_price',
            'label' => '預計售價',
            'old' => $before,
            'new' => $after,
        ]];
    }

    /**
     * @param  list<array<string, mixed>>  $changes
     */
    private function recordPriceHistory(Product $product, string $action, array $changes): void
    {
        ProductPriceHistory::query()->create([
            'product_id' => $product->id,
            'user_id' => Auth::id(),
            'action' => $action,
            'changes' => $changes,
            'created_at' => now(),
        ]);
    }
}
