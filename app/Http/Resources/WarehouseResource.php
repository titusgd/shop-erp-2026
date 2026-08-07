<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Warehouse */
class WarehouseResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'contact_name' => $this->contact_name,
            'phone' => $this->phone,
            'email' => $this->email,
            'postal_code' => $this->postal_code,
            'city_id' => $this->city_id,
            'district_id' => $this->district_id,
            'city' => $this->whenLoaded('city', fn () => $this->city ? [
                'id' => $this->city->id,
                'name' => $this->city->name,
                'code' => $this->city->code,
            ] : null),
            'district' => $this->whenLoaded('district', fn () => $this->district ? [
                'id' => $this->district->id,
                'name' => $this->district->name,
                'code' => $this->district->code,
            ] : null),
            'address' => $this->address,
            'notes' => $this->notes,
            'is_active' => $this->is_active,
            'warehouse_types' => WarehouseTypeResource::collection($this->whenLoaded('warehouseTypes')),
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'creator' => $this->whenLoaded('creator', fn () => $this->creator ? [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
                'username' => $this->creator->username,
            ] : null),
            'updater' => $this->whenLoaded('updater', fn () => $this->updater ? [
                'id' => $this->updater->id,
                'name' => $this->updater->name,
                'username' => $this->updater->username,
            ] : null),
            'created_at' => $this->created_at?->timezone(config('app.timezone'))->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->timezone(config('app.timezone'))->format('Y-m-d H:i:s'),
        ];
    }
}
