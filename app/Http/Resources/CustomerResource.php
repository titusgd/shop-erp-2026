<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Customer */
class CustomerResource extends JsonResource
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
            'tax_id' => $this->tax_id,
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
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
