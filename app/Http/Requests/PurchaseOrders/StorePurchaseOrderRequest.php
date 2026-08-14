<?php

namespace App\Http\Requests\PurchaseOrders;

use App\Models\PurchaseOrder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePurchaseOrderRequest extends FormRequest
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

        if ($this->has('expected_date') && is_string($this->input('expected_date')) && trim($this->input('expected_date')) === '') {
            $this->merge(['expected_date' => null]);
        }

        if (! $this->has('status') || $this->input('status') === null || $this->input('status') === '') {
            $this->merge(['status' => PurchaseOrder::STATUS_DRAFT]);
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
            'vendor_id' => ['required', 'integer', 'exists:vendors,id'],
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
            'order_date' => ['required', 'date'],
            'expected_date' => ['nullable', 'date', 'after_or_equal:order_date'],
            'status' => ['required', 'string', Rule::in(array_keys(PurchaseOrder::statuses()))],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => [
                'required',
                'integer',
                'distinct',
                'exists:products,id',
                Rule::exists('product_vendor', 'product_id')->where(
                    fn ($query) => $query->where('vendor_id', $this->input('vendor_id')),
                ),
            ],
            'items.*.quantity' => ['required', 'numeric', 'gt:0'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'vendor_id' => '供應商',
            'warehouse_id' => '進貨倉庫',
            'order_date' => '採購日期',
            'expected_date' => '預計到貨日',
            'status' => '狀態',
            'notes' => '備註',
            'items' => '採購明細',
            'items.*.product_id' => '商品',
            'items.*.quantity' => '數量',
            'items.*.unit_price' => '單價',
            'items.*.notes' => '明細備註',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'vendor_id.required' => '請選擇供應商。',
            'warehouse_id.required' => '請選擇進貨倉庫。',
            'order_date.required' => '請填寫採購日期。',
            'items.required' => '請至少新增一筆採購明細。',
            'items.min' => '請至少新增一筆採購明細。',
            'items.*.product_id.required' => '請選擇商品。',
            'items.*.product_id.distinct' => '同一採購單不可重複選擇相同商品。',
            'items.*.product_id.exists' => '請選擇此供應商可採購的商品。',
            'items.*.quantity.gt' => '數量必須大於 0。',
            'items.*.unit_price.min' => '單價不可為負數。',
            'expected_date.after_or_equal' => '預計到貨日不可早於採購日期。',
        ];
    }
}
