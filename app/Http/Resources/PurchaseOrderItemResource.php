<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\PurchaseOrderItem */
class PurchaseOrderItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'purchase_order_id' => $this->purchase_order_id,
            'product_id' => $this->product_id,
            'product' => $this->whenLoaded('product', function () {
                $unit = null;
                if ($this->product->relationLoaded('unit') && $this->product->unit) {
                    $unit = [
                        'id' => $this->product->unit->id,
                        'name' => $this->product->unit->name,
                        'symbol' => $this->product->unit->symbol,
                    ];
                }

                return [
                    'id' => $this->product->id,
                    'name' => $this->product->name,
                    'code' => $this->product->code,
                    'unit' => $unit,
                ];
            }),
            'quantity' => (string) $this->quantity,
            'unit_price' => (string) $this->unit_price,
            'amount' => (string) $this->amount,
            'notes' => $this->notes,
            'sort_order' => $this->sort_order,
        ];
    }
}
