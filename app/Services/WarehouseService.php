<?php

namespace App\Services;

use App\Models\City;
use App\Models\District;
use App\Models\Warehouse;
use App\Models\WarehouseHistory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WarehouseService
{
    /**
     * @var array<string, string>
     */
    private const FIELD_LABELS = [
        'name' => '倉庫名稱',
        'contact_name' => '聯絡人',
        'phone' => '電話',
        'email' => '電子郵件',
        'postal_code' => '郵遞區號',
        'city_id' => '縣市',
        'district_id' => '區域',
        'address' => '地址',
        'notes' => '備註',
        'is_active' => '啟用狀態',
        'warehouse_type_ids' => '倉庫類型',
    ];

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
            ->with(['warehouseTypes', 'city', 'district', 'creator', 'updater'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('contact_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('postal_code', 'like', "%{$search}%")
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
     *     postal_code?: string|null,
     *     city_id?: int|null,
     *     district_id?: int|null,
     *     address?: string|null,
     *     notes?: string|null,
     *     is_active?: bool|null,
     *     warehouse_type_ids?: list<int>|null
     * }  $data
     */
    public function create(array $data): Warehouse
    {
        return DB::transaction(function () use ($data) {
            $userId = Auth::id();

            $warehouse = Warehouse::query()->create([
                'name' => $data['name'],
                'code' => null,
                'contact_name' => $this->nullableString($data['contact_name'] ?? null),
                'phone' => $this->nullableString($data['phone'] ?? null),
                'email' => $this->nullableString($data['email'] ?? null),
                'postal_code' => $this->nullableString($data['postal_code'] ?? null),
                'city_id' => $data['city_id'] ?? null,
                'district_id' => $data['district_id'] ?? null,
                'address' => $this->nullableString($data['address'] ?? null),
                'notes' => $this->nullableString($data['notes'] ?? null),
                'is_active' => (bool) ($data['is_active'] ?? true),
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);

            $warehouse->code = $this->formatSystemCode($warehouse->id);
            $warehouse->save();

            $typeIds = array_values(array_map('intval', $data['warehouse_type_ids'] ?? []));
            $warehouse->warehouseTypes()->sync($typeIds);

            $this->recordHistory($warehouse, 'created', [
                [
                    'field' => 'name',
                    'label' => self::FIELD_LABELS['name'],
                    'old' => null,
                    'new' => $warehouse->name,
                ],
            ], $userId);

            return $warehouse->load(['warehouseTypes', 'city', 'district', 'creator', 'updater']);
        });
    }

    /**
     * @param  array{
     *     name: string,
     *     contact_name?: string|null,
     *     phone?: string|null,
     *     email?: string|null,
     *     postal_code?: string|null,
     *     city_id?: int|null,
     *     district_id?: int|null,
     *     address?: string|null,
     *     notes?: string|null,
     *     is_active?: bool|null,
     *     warehouse_type_ids?: list<int>|null
     * }  $data
     */
    public function update(Warehouse $warehouse, array $data): Warehouse
    {
        return DB::transaction(function () use ($warehouse, $data) {
            $warehouse->loadMissing(['warehouseTypes', 'city', 'district']);

            $before = $this->snapshot($warehouse);
            $userId = Auth::id();

            $warehouse->fill([
                'name' => $data['name'],
                'contact_name' => $this->nullableString($data['contact_name'] ?? null),
                'phone' => $this->nullableString($data['phone'] ?? null),
                'email' => $this->nullableString($data['email'] ?? null),
                'postal_code' => $this->nullableString($data['postal_code'] ?? null),
                'city_id' => $data['city_id'] ?? null,
                'district_id' => $data['district_id'] ?? null,
                'address' => $this->nullableString($data['address'] ?? null),
                'notes' => $this->nullableString($data['notes'] ?? null),
                'is_active' => (bool) ($data['is_active'] ?? true),
                'updated_by' => $userId,
            ]);
            $warehouse->save();

            if (array_key_exists('warehouse_type_ids', $data)) {
                $warehouse->warehouseTypes()->sync(array_values(array_map('intval', $data['warehouse_type_ids'] ?? [])));
            }

            $warehouse->load(['warehouseTypes', 'city', 'district']);
            $after = $this->snapshot($warehouse);
            $changes = $this->diffSnapshots($before, $after);

            if ($changes !== []) {
                $this->recordHistory($warehouse, 'updated', $changes, $userId);
            }

            return $warehouse->load(['warehouseTypes', 'city', 'district', 'creator', 'updater']);
        });
    }

    public function delete(Warehouse $warehouse): void
    {
        $warehouse->delete();
    }

    /**
     * @return Collection<int, WarehouseHistory>
     */
    public function histories(Warehouse $warehouse): Collection
    {
        return $warehouse->histories()->with('user')->get();
    }

    public function formatSystemCode(int $id): string
    {
        return 'W'.str_pad((string) $id, 6, '0', STR_PAD_LEFT);
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshot(Warehouse $warehouse): array
    {
        $typeIds = $warehouse->warehouseTypes
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->sort()
            ->values()
            ->all();

        $typeNames = $warehouse->warehouseTypes
            ->sortBy('id')
            ->pluck('name')
            ->values()
            ->all();

        return [
            'name' => $warehouse->name,
            'contact_name' => $warehouse->contact_name,
            'phone' => $warehouse->phone,
            'email' => $warehouse->email,
            'postal_code' => $warehouse->postal_code,
            'city_id' => $warehouse->city_id,
            'city_label' => $warehouse->city?->name,
            'district_id' => $warehouse->district_id,
            'district_label' => $warehouse->district?->name,
            'address' => $warehouse->address,
            'notes' => $warehouse->notes,
            'is_active' => (bool) $warehouse->is_active,
            'warehouse_type_ids' => $typeIds,
            'warehouse_type_labels' => $typeNames,
        ];
    }

    /**
     * @param  array<string, mixed>  $before
     * @param  array<string, mixed>  $after
     * @return list<array{field: string, label: string, old: mixed, new: mixed}>
     */
    private function diffSnapshots(array $before, array $after): array
    {
        $changes = [];

        foreach (array_keys(self::FIELD_LABELS) as $field) {
            $oldValue = $before[$field] ?? null;
            $newValue = $after[$field] ?? null;

            if ($field === 'warehouse_type_ids') {
                if ($oldValue === $newValue) {
                    continue;
                }

                $changes[] = [
                    'field' => $field,
                    'label' => self::FIELD_LABELS[$field],
                    'old' => $this->formatTypeLabels($before['warehouse_type_labels'] ?? []),
                    'new' => $this->formatTypeLabels($after['warehouse_type_labels'] ?? []),
                ];

                continue;
            }

            if ($oldValue === $newValue) {
                continue;
            }

            if (in_array($field, ['city_id', 'district_id'], true)) {
                $labelKey = $field === 'city_id' ? 'city_label' : 'district_label';
                $changes[] = [
                    'field' => $field,
                    'label' => self::FIELD_LABELS[$field],
                    'old' => $before[$labelKey] ?? null,
                    'new' => $after[$labelKey] ?? null,
                ];

                continue;
            }

            if ($field === 'is_active') {
                $changes[] = [
                    'field' => $field,
                    'label' => self::FIELD_LABELS[$field],
                    'old' => $oldValue ? '啟用' : '停用',
                    'new' => $newValue ? '啟用' : '停用',
                ];

                continue;
            }

            $changes[] = [
                'field' => $field,
                'label' => self::FIELD_LABELS[$field],
                'old' => $oldValue,
                'new' => $newValue,
            ];
        }

        // Resolve city/district labels when relation was not loaded on before snapshot.
        foreach ($changes as &$change) {
            if ($change['field'] === 'city_id') {
                $change['old'] = $change['old'] ?? $this->cityName($before['city_id'] ?? null);
                $change['new'] = $change['new'] ?? $this->cityName($after['city_id'] ?? null);
            }

            if ($change['field'] === 'district_id') {
                $change['old'] = $change['old'] ?? $this->districtName($before['district_id'] ?? null);
                $change['new'] = $change['new'] ?? $this->districtName($after['district_id'] ?? null);
            }
        }
        unset($change);

        return $changes;
    }

    /**
     * @param  list<array{field: string, label: string, old: mixed, new: mixed}>  $changes
     */
    private function recordHistory(Warehouse $warehouse, string $action, array $changes, ?int $userId): void
    {
        WarehouseHistory::query()->create([
            'warehouse_id' => $warehouse->id,
            'user_id' => $userId,
            'action' => $action,
            'changes' => $changes,
            'created_at' => now(),
        ]);
    }

    /**
     * @param  list<string>  $labels
     */
    private function formatTypeLabels(array $labels): ?string
    {
        if ($labels === []) {
            return null;
        }

        return implode('、', $labels);
    }

    private function cityName(mixed $cityId): ?string
    {
        if ($cityId === null) {
            return null;
        }

        return City::query()->whereKey($cityId)->value('name');
    }

    private function districtName(mixed $districtId): ?string
    {
        if ($districtId === null) {
            return null;
        }

        return District::query()->whereKey($districtId)->value('name');
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
