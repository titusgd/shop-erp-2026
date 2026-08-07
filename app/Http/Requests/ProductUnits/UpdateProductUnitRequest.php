<?php

namespace App\Http\Requests\ProductUnits;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductUnitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $nullable = ['symbol', 'notes'];

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
        $productUnitId = $this->route('product_unit')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('product_units', 'name')->ignore($productUnitId),
            ],
            'symbol' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('product_units', 'symbol')->ignore($productUnitId),
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
            'name' => '單位名稱',
            'symbol' => '簡稱',
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
            'name.unique' => '此單位名稱已存在。',
            'symbol.unique' => '此簡稱已存在。',
        ];
    }
}
