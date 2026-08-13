<?php

namespace App\Http\Requests\Vendors;

use App\Models\Vendor;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreVendorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $nullable = ['tax_id', 'contact_name', 'phone', 'email', 'postal_code', 'address', 'notes', 'remittance_bank', 'remittance_account', 'settlement_method'];

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
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'tax_id' => ['nullable', 'string', 'max:20', 'unique:vendors,tax_id'],
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
            'remittance_bank' => ['nullable', 'string', 'max:255'],
            'remittance_account' => ['nullable', 'string', 'max:50'],
            'settlement_method' => ['nullable', 'string', Rule::in(array_keys(Vendor::settlementMethods()))],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => '廠商名稱',
            'tax_id' => '統一編號',
            'contact_name' => '聯絡人',
            'phone' => '電話',
            'email' => '電子郵件',
            'postal_code' => '郵遞區號',
            'city_id' => '縣市',
            'district_id' => '區域',
            'address' => '地址',
            'notes' => '備註',
            'remittance_bank' => '匯款銀行',
            'remittance_account' => '匯款帳號',
            'settlement_method' => '結帳方式',
            'is_active' => '啟用狀態',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'tax_id.unique' => '此統一編號已存在。',
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
