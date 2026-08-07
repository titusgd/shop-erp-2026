<?php

namespace App\Http\Requests\Warehouses;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreWarehouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $nullable = ['contact_name', 'phone', 'email', 'postal_code', 'address', 'notes'];

        foreach ($nullable as $field) {
            if ($this->has($field) && is_string($this->input($field)) && trim($this->input($field)) === '') {
                $this->merge([$field => null]);
            }
        }

        foreach (['city_id', 'district_id'] as $field) {
            if ($this->has($field) && ($this->input($field) === '' || $this->input($field) === null)) {
                $this->merge([$field => null]);
            }
        }

        if (! $this->has('warehouse_type_ids')) {
            $this->merge(['warehouse_type_ids' => []]);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'district_id' => [
                'nullable',
                'integer',
                Rule::exists('districts', 'id')->where(fn ($query) => $query->where('city_id', $this->input('city_id'))),
            ],
            'address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['sometimes', 'boolean'],
            'warehouse_type_ids' => ['nullable', 'array'],
            'warehouse_type_ids.*' => ['integer', 'distinct', 'exists:warehouse_types,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
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
            'warehouse_type_ids.*' => '倉庫類型',
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->filled('district_id') && ! $this->filled('city_id')) {
                $validator->errors()->add('city_id', '請先選擇縣市。');
            }
        });
    }
}
