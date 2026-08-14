<?php

namespace App\Http\Requests\PurchaseRequisitions;

use App\Models\PurchaseRequisition;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePurchaseRequisitionRequest extends FormRequest
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

        if ($this->has('required_date') && is_string($this->input('required_date')) && trim($this->input('required_date')) === '') {
            $this->merge(['required_date' => null]);
        }

        $items = $this->input('items');
        if (is_array($items)) {
            $normalized = [];
            foreach ($items as $item) {
                if (! is_array($item)) {
                    continue;
                }
                if (array_key_exists('notes', $item) && is_string($item['notes']) && trim($item['notes']) === '') {
                    $item['notes'] = null;
                }
                $normalized[] = $item;
            }
            $this->merge(['items' => $normalized]);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'requester_id' => ['required', 'integer', 'exists:users,id'],
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
            'request_date' => ['required', 'date'],
            'required_date' => ['nullable', 'date', 'after_or_equal:request_date'],
            'status' => ['required', 'string', Rule::in(array_keys(PurchaseRequisition::statuses()))],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'requester_id' => '請購人',
            'warehouse_id' => '進貨倉庫',
            'request_date' => '請購日期',
            'required_date' => '需求日期',
            'status' => '狀態',
            'notes' => '備註',
            'items' => '請購明細',
            'items.*.product_id' => '商品',
            'items.*.quantity' => '數量',
            'items.*.notes' => '明細備註',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'requester_id.required' => '請選擇請購人。',
            'warehouse_id.required' => '請選擇進貨倉庫。',
            'request_date.required' => '請填寫請購日期。',
            'items.required' => '請至少新增一筆請購明細。',
            'items.min' => '請至少新增一筆請購明細。',
            'items.*.product_id.required' => '請選擇商品。',
            'items.*.product_id.distinct' => '同一請購單不可重複選擇相同商品。',
            'items.*.quantity.gt' => '數量必須大於 0。',
            'required_date.after_or_equal' => '需求日期不可早於請購日期。',
        ];
    }
}
