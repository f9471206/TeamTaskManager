<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InvitationsResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'is_member' => $this->is_member ?? false,
            'is_owner' => $this->is_owner ?? false,
            'invite_status' => $this->invite_status ?? null,
            'expires_at' => $this->expires_at?->toDateTimeString(),
        ];

    }
}
