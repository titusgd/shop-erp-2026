<?php

namespace App\Services;

use App\Models\Warehouse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class WarehouseService
{
    /**
     * @param  array{search?: string|null, per_page?: int|null}  $filters
     * @return LengthAwarePaginator<int, Warehouse>
     */
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $perPage = (int) ($filters['per_page'] ?? 10);
        $perPage = max(1, min($perPage, 50));

        return Warehouse::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('contact_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%");
                });
            })
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array{
     *     name: string,
     *     contact_name?: string|null,
     *     phone?: string|null,
     *     email?: string|null,
     *     address?: string|null,
     *     notes?: string|null,
     *     is_active?: bool|null
     * }  $data
     */
    public function create(array $data): Warehouse
    {
        return DB::transaction(function () use ($data) {
            $warehouse = Warehouse::query()->create([
                'name' => $data['name'],
                'code' => null,
                'contact_name' => $this->nullableString($data['contact_name'] ?? null),
                'phone' => $this->nullableString($data['phone'] ?? null),
                'email' => $this->nullableString($data['email'] ?? null),
                'address' => $this->nullableString($data['address'] ?? null),
                'notes' => $this->nullableString($data['notes'] ?? null),
                'is_active' => (bool) ($data['is_active'] ?? true),
            ]);

            $warehouse->code = $this->formatSystemCode($warehouse->id);
            $warehouse->save();

            return $warehouse->refresh();
        });
    }

    /**
     * @param  array{
     *     name: string,
     *     contact_name?: string|null,
     *     phone?: string|null,
     *     email?: string|null,
     *     address?: string|null,
     *     notes?: string|null,
     *     is_active?: bool|null
     * }  $data
     */
    public function update(Warehouse $warehouse, array $data): Warehouse
    {
        $warehouse->fill([
            'name' => $data['name'],
            'contact_name' => $this->nullableString($data['contact_name'] ?? null),
            'phone' => $this->nullableString($data['phone'] ?? null),
            'email' => $this->nullableString($data['email'] ?? null),
            'address' => $this->nullableString($data['address'] ?? null),
            'notes' => $this->nullableString($data['notes'] ?? null),
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);
        $warehouse->save();

        return $warehouse->refresh();
    }

    public function delete(Warehouse $warehouse): void
    {
        $warehouse->delete();
    }

    public function formatSystemCode(int $id): string
    {
        return 'W'.str_pad((string) $id, 6, '0', STR_PAD_LEFT);
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
