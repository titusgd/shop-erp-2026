<?php

namespace App\Services;

use App\Models\District;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class DistrictService
{
    /**
     * @param  array{
     *     search?: string|null,
     *     city_id?: int|null,
     *     per_page?: int|null,
     *     active_only?: bool|null
     * }  $filters
     * @return LengthAwarePaginator<int, District>
     */
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $cityId = $filters['city_id'] ?? null;
        $perPage = (int) ($filters['per_page'] ?? 10);
        $perPage = max(1, min($perPage, 50));
        $activeOnly = (bool) ($filters['active_only'] ?? false);

        return District::query()
            ->with('city')
            ->when($activeOnly, fn ($query) => $query->where('is_active', true))
            ->when($cityId, fn ($query) => $query->where('city_id', $cityId))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhereHas('city', function ($query) use ($search) {
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
     *     city_id: int,
     *     name: string,
     *     notes?: string|null,
     *     is_active?: bool|null
     * }  $data
     */
    public function create(array $data): District
    {
        return DB::transaction(function () use ($data) {
            $district = District::query()->create([
                'city_id' => $data['city_id'],
                'name' => $data['name'],
                'code' => null,
                'notes' => $this->nullableString($data['notes'] ?? null),
                'is_active' => (bool) ($data['is_active'] ?? true),
            ]);

            $district->code = $this->formatSystemCode($district->id);
            $district->save();

            return $district->refresh()->load('city');
        });
    }

    /**
     * @param  array{
     *     city_id: int,
     *     name: string,
     *     notes?: string|null,
     *     is_active?: bool|null
     * }  $data
     */
    public function update(District $district, array $data): District
    {
        $district->fill([
            'city_id' => $data['city_id'],
            'name' => $data['name'],
            'notes' => $this->nullableString($data['notes'] ?? null),
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);
        $district->save();

        return $district->refresh()->load('city');
    }

    public function delete(District $district): void
    {
        $district->delete();
    }

    public function formatSystemCode(int $id): string
    {
        return 'DT'.str_pad((string) $id, 6, '0', STR_PAD_LEFT);
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
