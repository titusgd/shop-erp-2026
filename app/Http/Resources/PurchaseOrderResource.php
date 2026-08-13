<?php

namespace App\Http\Resources;

use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\PurchaseOrder */
class PurchaseOrderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $statuses = PurchaseOrder::statuses();

        return [
            'id' => $this->id,
            'code' => $this->code,
            'vendor_id' => $this->vendor_id,
            'warehouse_id' => $this->warehouse_id,
            'vendor' => $this->whenLoaded('vendor', fn () => [
                'id' => $this->vendor->id,
                'name' => $this->vendor->name,
                'code' => $this->vendor->code,
            ]),
            'warehouse' => $this->whenLoaded('warehouse', fn () => [
                'id' => $this->warehouse->id,
                'name' => $this->warehouse->name,
                'code' => $this->warehouse->code,
            ]),
            'order_date' => $this->order_date?->format('Y-m-d'),
            'expected_date' => $this->expected_date?->format('Y-m-d'),
            'status' => $this->status,
            'status_label' => $statuses[$this->status] ?? $this->status,
            'total_amount' => (string) $this->total_amount,
            'notes' => $this->notes,
            'items' => PurchaseOrderItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
