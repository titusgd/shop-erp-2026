<?php

namespace App\Http\Requests\Cities;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCityRequest extends FormRequest
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
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $cityId = $this->route('city')?->id;

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('cities', 'name')->ignore($cityId),
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
            'name' => '縣市名稱',
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
            'name.unique' => '此縣市名稱已存在。',
        ];
    }
}
