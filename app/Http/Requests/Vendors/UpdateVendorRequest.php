<?php

namespace App\Http\Requests\Vendors;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVendorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $nullable = ['tax_id', 'contact_name', 'phone', 'email', 'address', 'notes'];

        foreach ($nullable as $field) {
            if ($this->has($field) && is_string($this->input($field)) && trim($this->input($field)) === '') {
                $this->merge([$field => null]);
            }
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $vendorId = $this->route('vendor')?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'tax_id' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('vendors', 'tax_id')->ignore($vendorId),
            ],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:2000'],
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
            'address' => '地址',
            'notes' => '備註',
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
}
