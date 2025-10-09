<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'pivot' => [
                'task_id' => $this->pivot->task_id ?? null,
                'user_id' => $this->pivot->user_id ?? null,
                'assigned_at' => $this->pivot->assigned_at ?? null,
            ],
        ];
    }
}
