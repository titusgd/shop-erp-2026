<?php

namespace App\Http\Requests\Products;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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

        if ($this->has('estimated_selling_price') && is_string($this->input('estimated_selling_price')) && trim($this->input('estimated_selling_price')) === '') {
            $this->merge(['estimated_selling_price' => null]);
        }

        if (! $this->has('vendor_ids')) {
            $this->merge(['vendor_ids' => []]);
        }

        if ($this->has('vendor_purchase_prices') && is_array($this->input('vendor_purchase_prices'))) {
            $normalized = [];

            foreach ($this->input('vendor_purchase_prices') as $vendorId => $price) {
                $normalized[$vendorId] = is_string($price) && trim($price) === '' ? null : $price;
            }

            $this->merge(['vendor_purchase_prices' => $normalized]);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'product_category_id' => ['required', 'integer', 'exists:product_categories,id'],
            'product_unit_id' => ['required', 'integer', 'exists:product_units,id'],
            'vendor_ids' => ['nullable', 'array'],
            'vendor_ids.*' => ['integer', 'distinct', 'exists:vendors,id'],
            'vendor_purchase_prices' => ['nullable', 'array'],
            'vendor_purchase_prices.*' => ['nullable', 'numeric', 'min:0'],
            'name' => ['required', 'string', 'max:255', 'unique:products,name'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'estimated_selling_price' => ['nullable', 'numeric', 'min:0'],
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
            'vendor_purchase_prices' => '預計進價',
            'vendor_purchase_prices.*' => '預計進價',
            'name' => '商品名稱',
            'notes' => '備註',
            'estimated_selling_price' => '預計售價',
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
            'vendor_purchase_prices.*.min' => '預計進價不可為負數。',
            'estimated_selling_price.min' => '預計售價不可為負數。',
        ];
    }
}
