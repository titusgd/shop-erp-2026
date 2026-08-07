<?php

namespace App\Services;

use App\Models\ProductUnit;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProductUnitService
{
    /**
     * @param  array{search?: string|null, per_page?: int|null}  $filters
     * @return LengthAwarePaginator<int, ProductUnit>
     */
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $perPage = (int) ($filters['per_page'] ?? 10);
        $perPage = max(1, min($perPage, 50));

        return ProductUnit::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('symbol', 'like', "%{$search}%");
                });
            })
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array{
     *     name: string,
     *     symbol?: string|null,
     *     notes?: string|null,
     *     is_active?: bool|null
     * }  $data
     */
    public function create(array $data): ProductUnit
    {
        return DB::transaction(function () use ($data) {
            $unit = ProductUnit::query()->create([
                'name' => $data['name'],
                'code' => null,
                'symbol' => $this->nullableString($data['symbol'] ?? null),
                'notes' => $this->nullableString($data['notes'] ?? null),
                'is_active' => (bool) ($data['is_active'] ?? true),
            ]);

            $unit->code = $this->formatSystemCode($unit->id);
            $unit->save();

            return $unit->refresh();
        });
    }

    /**
     * @param  array{
     *     name: string,
     *     symbol?: string|null,
     *     notes?: string|null,
     *     is_active?: bool|null
     * }  $data
     */
    public function update(ProductUnit $productUnit, array $data): ProductUnit
    {
        $productUnit->fill([
            'name' => $data['name'],
            'symbol' => $this->nullableString($data['symbol'] ?? null),
            'notes' => $this->nullableString($data['notes'] ?? null),
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);
        $productUnit->save();

        return $productUnit->refresh();
    }

    public function delete(ProductUnit $productUnit): void
    {
        $productUnit->delete();
    }

    public function formatSystemCode(int $id): string
    {
        return 'U'.str_pad((string) $id, 6, '0', STR_PAD_LEFT);
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
