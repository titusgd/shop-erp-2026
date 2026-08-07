<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\WarehouseHistory */
class WarehouseHistoryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'action' => $this->action,
            'action_label' => match ($this->action) {
                'created' => '建立',
                'updated' => '修改',
                default => $this->action,
            },
            'changes' => $this->changes ?? [],
            'user' => $this->whenLoaded('user', fn () => $this->user ? [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'username' => $this->user->username,
            ] : null),
            'created_at' => $this->created_at?->timezone(config('app.timezone'))->format('Y-m-d H:i:s'),
        ];
    }
}
