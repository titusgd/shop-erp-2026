<?php

namespace App\Services;

use App\Models\WarehouseType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class WarehouseTypeService
{
    /**
     * @param  array{search?: string|null, per_page?: int|null, active_only?: bool|null}  $filters
     * @return LengthAwarePaginator<int, WarehouseType>
     */
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $perPage = (int) ($filters['per_page'] ?? 10);
        $perPage = max(1, min($perPage, 50));
        $activeOnly = (bool) ($filters['active_only'] ?? false);

        return WarehouseType::query()
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
    public function create(array $data): WarehouseType
    {
        return DB::transaction(function () use ($data) {
            $warehouseType = WarehouseType::query()->create([
                'name' => $data['name'],
                'code' => null,
                'notes' => $this->nullableString($data['notes'] ?? null),
                'is_active' => (bool) ($data['is_active'] ?? true),
            ]);

            $warehouseType->code = $this->formatSystemCode($warehouseType->id);
            $warehouseType->save();

            return $warehouseType->refresh();
        });
    }

    /**
     * @param  array{
     *     name: string,
     *     notes?: string|null,
     *     is_active?: bool|null
     * }  $data
     */
    public function update(WarehouseType $warehouseType, array $data): WarehouseType
    {
        $warehouseType->fill([
            'name' => $data['name'],
            'notes' => $this->nullableString($data['notes'] ?? null),
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);
        $warehouseType->save();

        return $warehouseType->refresh();
    }

    public function delete(WarehouseType $warehouseType): void
    {
        $warehouseType->delete();
    }

    public function formatSystemCode(int $id): string
    {
        return 'WT'.str_pad((string) $id, 6, '0', STR_PAD_LEFT);
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
