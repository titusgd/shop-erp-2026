<?php

namespace App\Http\Requests\Districts;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreDistrictRequest extends FormRequest
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
            'city_id' => ['required', 'integer', 'exists:cities,id'],
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('districts', 'name')->where(fn ($query) => $query->where('city_id', $this->input('city_id'))),
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
            'city_id' => '縣市',
            'name' => '地區名稱',
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
            'city_id.required' => '請選擇縣市。',
            'name.unique' => '此縣市下已有相同地區名稱。',
        ];
    }
}
