<?php

namespace App\Http\Requests\Cities;

use Illuminate\Foundation\Http\FormRequest;

class StoreCityRequest extends FormRequest
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
        return [
            'name' => ['required', 'string', 'max:255', 'unique:cities,name'],
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
