<?php

namespace App\Services;

use App\Models\City;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CityService
{
    /**
     * @param  array{search?: string|null, per_page?: int|null, active_only?: bool|null}  $filters
     * @return LengthAwarePaginator<int, City>
     */
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $perPage = (int) ($filters['per_page'] ?? 10);
        $perPage = max(1, min($perPage, 50));
        $activeOnly = (bool) ($filters['active_only'] ?? false);

        return City::query()
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
    public function create(array $data): City
    {
        return DB::transaction(function () use ($data) {
            $city = City::query()->create([
                'name' => $data['name'],
                'code' => null,
                'notes' => $this->nullableString($data['notes'] ?? null),
                'is_active' => (bool) ($data['is_active'] ?? true),
            ]);

            $city->code = $this->formatSystemCode($city->id);
            $city->save();

            return $city->refresh();
        });
    }

    /**
     * @param  array{
     *     name: string,
     *     notes?: string|null,
     *     is_active?: bool|null
     * }  $data
     */
    public function update(City $city, array $data): City
    {
        $city->fill([
            'name' => $data['name'],
            'notes' => $this->nullableString($data['notes'] ?? null),
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);
        $city->save();

        return $city->refresh();
    }

    public function delete(City $city): void
    {
        if ($city->districts()->exists()) {
            throw ValidationException::withMessages([
                'city' => ['此縣市仍有地區資料，無法刪除。'],
            ]);
        }

        $city->delete();
    }

    public function formatSystemCode(int $id): string
    {
        return 'CT'.str_pad((string) $id, 6, '0', STR_PAD_LEFT);
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
