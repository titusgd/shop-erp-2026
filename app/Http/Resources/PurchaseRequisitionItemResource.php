<?php

namespace App\Http\Resources;

use App\Models\PurchaseRequisitionItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PurchaseRequisitionItem */
class PurchaseRequisitionItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'purchase_requisition_id' => $this->purchase_requisition_id,
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
            'notes' => $this->notes,
            'sort_order' => $this->sort_order,
        ];
    }
}
