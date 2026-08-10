<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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
     *     name: string,
     *     notes?: string|null,
     *     is_active?: bool|null
     * }  $data
     */
    public function create(array $data): Product
    {
        return DB::transaction(function () use ($data) {
            $product = Product::query()->create([
                'product_category_id' => $data['product_category_id'],
                'product_unit_id' => $data['product_unit_id'],
                'name' => $data['name'],
                'code' => null,
                'notes' => $this->nullableString($data['notes'] ?? null),
                'is_active' => (bool) ($data['is_active'] ?? true),
            ]);

            $product->code = $this->formatSystemCode($product->id);
            $product->save();

            $product->vendors()->sync($this->normalizeIds($data['vendor_ids'] ?? []));

            return $product->refresh()->load(['category', 'unit', 'vendors']);
        });
    }

    /**
     * @param  array{
     *     product_category_id: int,
     *     product_unit_id: int,
     *     vendor_ids?: list<int>|null,
     *     name: string,
     *     notes?: string|null,
     *     is_active?: bool|null
     * }  $data
     */
    public function update(Product $product, array $data): Product
    {
        return DB::transaction(function () use ($product, $data) {
            $product->fill([
                'product_category_id' => $data['product_category_id'],
                'product_unit_id' => $data['product_unit_id'],
                'name' => $data['name'],
                'notes' => $this->nullableString($data['notes'] ?? null),
                'is_active' => (bool) ($data['is_active'] ?? true),
            ]);
            $product->save();

            if (array_key_exists('vendor_ids', $data)) {
                $product->vendors()->sync($this->normalizeIds($data['vendor_ids'] ?? []));
            }

            return $product->refresh()->load(['category', 'unit', 'vendors']);
        });
    }

    public function delete(Product $product): void
    {
        $product->delete();
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

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : $trimmed;
    }
}
