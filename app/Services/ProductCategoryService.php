<?php

namespace App\Services;

use App\Models\ProductCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProductCategoryService
{
    /**
     * @param  array{search?: string|null, per_page?: int|null, active_only?: bool|null}  $filters
     * @return LengthAwarePaginator<int, ProductCategory>
     */
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $perPage = (int) ($filters['per_page'] ?? 10);
        $perPage = max(1, min($perPage, 50));
        $activeOnly = (bool) ($filters['active_only'] ?? false);

        return ProductCategory::query()
            ->when($activeOnly, fn ($query) => $query->where('is_active', true))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array{
     *     name: string,
     *     notes?: string|null,
     *     is_active?: bool|null
     * }  $data
     */
    public function create(array $data): ProductCategory
    {
        return DB::transaction(function () use ($data) {
            $productCategory = ProductCategory::query()->create([
                'name' => $data['name'],
                'code' => null,
                'notes' => $this->nullableString($data['notes'] ?? null),
                'is_active' => (bool) ($data['is_active'] ?? true),
            ]);

            $productCategory->code = $this->formatSystemCode($productCategory->id);
            $productCategory->save();

            return $productCategory->refresh();
        });
    }

    /**
     * @param  array{
     *     name: string,
     *     notes?: string|null,
     *     is_active?: bool|null
     * }  $data
     */
    public function update(ProductCategory $productCategory, array $data): ProductCategory
    {
        $productCategory->fill([
            'name' => $data['name'],
            'notes' => $this->nullableString($data['notes'] ?? null),
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);
        $productCategory->save();

        return $productCategory->refresh();
    }

    public function delete(ProductCategory $productCategory): void
    {
        $productCategory->delete();
    }

    public function formatSystemCode(int $id): string
    {
        return 'PC'.str_pad((string) $id, 6, '0', STR_PAD_LEFT);
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
