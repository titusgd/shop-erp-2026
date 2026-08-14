<?php

namespace App\Http\Resources;

use App\Models\PurchaseRequisition;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PurchaseRequisition */
class PurchaseRequisitionResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $statuses = PurchaseRequisition::statuses();

        return [
            'id' => $this->id,
            'code' => $this->code,
            'requester_id' => $this->requester_id,
            'warehouse_id' => $this->warehouse_id,
            'requester' => $this->whenLoaded('requester', fn () => [
                'id' => $this->requester->id,
                'name' => $this->requester->name,
                'username' => $this->requester->username,
            ]),
            'warehouse' => $this->whenLoaded('warehouse', fn () => [
                'id' => $this->warehouse->id,
                'name' => $this->warehouse->name,
                'code' => $this->warehouse->code,
            ]),
            'request_date' => $this->request_date?->format('Y-m-d'),
            'required_date' => $this->required_date?->format('Y-m-d'),
            'status' => $this->status,
            'status_label' => $statuses[$this->status] ?? $this->status,
            'notes' => $this->notes,
            'items' => PurchaseRequisitionItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
