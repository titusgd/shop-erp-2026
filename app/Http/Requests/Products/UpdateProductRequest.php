<?php

namespace App\Http\Requests\Products;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('notes') && is_string($this->input('notes')) && trim($this->input('notes')) === '') {
            $this->merge(['notes' => null]);
        }

        if (! $this->has('vendor_ids')) {
            $this->merge(['vendor_ids' => []]);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $productId = $this->route('product')?->id;

        return [
            'product_category_id' => ['required', 'integer', 'exists:product_categories,id'],
            'product_unit_id' => ['required', 'integer', 'exists:product_units,id'],
            'vendor_ids' => ['nullable', 'array'],
            'vendor_ids.*' => ['integer', 'distinct', 'exists:vendors,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('products', 'name')->ignore($productId),
            ],
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
            'product_category_id' => '商品分類',
            'product_unit_id' => '商品單位',
            'vendor_ids' => '供應商',
            'vendor_ids.*' => '供應商',
            'name' => '商品名稱',
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
            'product_category_id.required' => '請選擇商品分類。',
            'product_unit_id.required' => '請選擇商品單位。',
            'name.unique' => '此商品名稱已存在。',
        ];
    }
}
