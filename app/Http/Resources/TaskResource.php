<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray($request)
    {
        // status 已經是 Enum，由模型 cast 處理
        $statusEnum = $this->status;

        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'board_id' => $this->board_id,
            'title' => $this->title,
            'description' => $this->description,
            'due_date' => $this->due_date,
            'status' => [
                'value' => $statusEnum->value,
                'label' => $statusEnum->label(),
                'color' => $statusEnum->color(),
            ],
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
            'users' => UserResource::collection($this->whenLoaded('users')),
        ];
    }
}
