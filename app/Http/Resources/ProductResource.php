<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Product */
class ProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_category_id' => $this->product_category_id,
            'product_unit_id' => $this->product_unit_id,
            'vendor_ids' => $this->whenLoaded('vendors', fn () => $this->vendors->pluck('id')->values()->all()),
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category->id,
                'name' => $this->category->name,
                'code' => $this->category->code,
            ]),
            'unit' => $this->whenLoaded('unit', fn () => [
                'id' => $this->unit->id,
                'name' => $this->unit->name,
                'code' => $this->unit->code,
                'symbol' => $this->unit->symbol,
            ]),
            'vendors' => $this->whenLoaded('vendors', fn () => $this->vendors->map(fn ($vendor) => [
                'id' => $vendor->id,
                'name' => $vendor->name,
                'code' => $vendor->code,
            ])->values()->all()),
            'name' => $this->name,
            'code' => $this->code,
            'notes' => $this->notes,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
