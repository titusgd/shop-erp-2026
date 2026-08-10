<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CustomerService
{
    /**
     * @param  array{search?: string|null, per_page?: int|null}  $filters
     * @return LengthAwarePaginator<int, Customer>
     */
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $search = trim((string) ($filters['search'] ?? ''));
        $perPage = (int) ($filters['per_page'] ?? 10);
        $perPage = max(1, min($perPage, 50));

        return Customer::query()
            ->with(['city', 'district'])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('tax_id', 'like', "%{$search}%")
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
     *     tax_id?: string|null,
     *     contact_name?: string|null,
     *     phone?: string|null,
     *     email?: string|null,
     *     postal_code?: string|null,
     *     city_id?: int|null,
     *     district_id?: int|null,
     *     address?: string|null,
     *     notes?: string|null,
     *     is_active?: bool|null
     * }  $data
     */
    public function create(array $data): Customer
    {
        return DB::transaction(function () use ($data) {
            $customer = Customer::query()->create([
                'name' => $data['name'],
                'code' => null,
                'tax_id' => $this->nullableString($data['tax_id'] ?? null),
                'contact_name' => $this->nullableString($data['contact_name'] ?? null),
                'phone' => $this->nullableString($data['phone'] ?? null),
                'email' => $this->nullableString($data['email'] ?? null),
                'postal_code' => $this->nullableString($data['postal_code'] ?? null),
                'city_id' => $data['city_id'] ?? null,
                'district_id' => $data['district_id'] ?? null,
                'address' => $this->nullableString($data['address'] ?? null),
                'notes' => $this->nullableString($data['notes'] ?? null),
                'is_active' => (bool) ($data['is_active'] ?? true),
            ]);

            $customer->code = $this->formatSystemCode($customer->id);
            $customer->save();

            return $customer->refresh()->load(['city', 'district']);
        });
    }

    /**
     * @param  array{
     *     name: string,
     *     tax_id?: string|null,
     *     contact_name?: string|null,
     *     phone?: string|null,
     *     email?: string|null,
     *     postal_code?: string|null,
     *     city_id?: int|null,
     *     district_id?: int|null,
     *     address?: string|null,
     *     notes?: string|null,
     *     is_active?: bool|null
     * }  $data
     */
    public function update(Customer $customer, array $data): Customer
    {
        $customer->fill([
            'name' => $data['name'],
            'tax_id' => $this->nullableString($data['tax_id'] ?? null),
            'contact_name' => $this->nullableString($data['contact_name'] ?? null),
            'phone' => $this->nullableString($data['phone'] ?? null),
            'email' => $this->nullableString($data['email'] ?? null),
            'postal_code' => $this->nullableString($data['postal_code'] ?? null),
            'city_id' => $data['city_id'] ?? null,
            'district_id' => $data['district_id'] ?? null,
            'address' => $this->nullableString($data['address'] ?? null),
            'notes' => $this->nullableString($data['notes'] ?? null),
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);
        $customer->save();

        return $customer->refresh()->load(['city', 'district']);
    }

    public function delete(Customer $customer): void
    {
        $customer->delete();
    }

    public function formatSystemCode(int $id): string
    {
        return 'C'.str_pad((string) $id, 6, '0', STR_PAD_LEFT);
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
