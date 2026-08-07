<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\District */
class DistrictResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'city_id' => $this->city_id,
            'city' => $this->whenLoaded('city', fn () => [
                'id' => $this->city->id,
                'name' => $this->city->name,
                'code' => $this->city->code,
            ]),
            'name' => $this->name,
            'code' => $this->code,
            'notes' => $this->notes,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
