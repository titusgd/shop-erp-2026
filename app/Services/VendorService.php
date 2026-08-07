<?php

namespace App\Services;

use App\Models\Vendor;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class VendorService
{
    /**
     * @param  array{search?: string|null, per_page?: int|null}  $filters
     * @return LengthAwarePaginator<int, Vendor>
     */
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $perPage = (int) ($filters['per_page'] ?? 10);
        $perPage = max(1, min($perPage, 50));

        return Vendor::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('tax_id', 'like', "%{$search}%")
                        ->orWhere('contact_name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @param  array{
     *     name: string,
     *     tax_id?: string|null,
     *     contact_name?: string|null,
     *     phone?: string|null,
     *     email?: string|null,
     *     address?: string|null,
     *     notes?: string|null,
     *     is_active?: bool|null
     * }  $data
     */
    public function create(array $data): Vendor
    {
        return DB::transaction(function () use ($data) {
            $vendor = Vendor::query()->create([
                'name' => $data['name'],
                'code' => null,
                'tax_id' => $this->nullableString($data['tax_id'] ?? null),
                'contact_name' => $this->nullableString($data['contact_name'] ?? null),
                'phone' => $this->nullableString($data['phone'] ?? null),
                'email' => $this->nullableString($data['email'] ?? null),
                'address' => $this->nullableString($data['address'] ?? null),
                'notes' => $this->nullableString($data['notes'] ?? null),
                'is_active' => (bool) ($data['is_active'] ?? true),
            ]);

            $vendor->code = $this->formatSystemCode($vendor->id);
            $vendor->save();

            return $vendor->refresh();
        });
    }

    /**
     * @param  array{
     *     name: string,
     *     tax_id?: string|null,
     *     contact_name?: string|null,
     *     phone?: string|null,
     *     email?: string|null,
     *     address?: string|null,
     *     notes?: string|null,
     *     is_active?: bool|null
     * }  $data
     */
    public function update(Vendor $vendor, array $data): Vendor
    {
        $vendor->fill([
            'name' => $data['name'],
            'tax_id' => $this->nullableString($data['tax_id'] ?? null),
            'contact_name' => $this->nullableString($data['contact_name'] ?? null),
            'phone' => $this->nullableString($data['phone'] ?? null),
            'email' => $this->nullableString($data['email'] ?? null),
            'address' => $this->nullableString($data['address'] ?? null),
            'notes' => $this->nullableString($data['notes'] ?? null),
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);
        $vendor->save();

        return $vendor->refresh();
    }

    public function delete(Vendor $vendor): void
    {
        $vendor->delete();
    }

    public function formatSystemCode(int $id): string
    {
        return 'V'.str_pad((string) $id, 6, '0', STR_PAD_LEFT);
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
