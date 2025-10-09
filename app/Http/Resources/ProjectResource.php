<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    public function toArray($request)
    {
        // status 已經是 Enum，由模型 cast 處理
        $statusEnum = $this->status;

        return [
            'id' => $this->id,
            'team_id' => $this->team_id,
            'name' => $this->name,
            'description' => $this->description,
            'status' => [
                'value' => $statusEnum->value,
                'label' => $statusEnum->label(),
                'color' => $statusEnum->color(),
            ],
            'created_by' => $this->created_by,
            'due_date' => $this->due_date,
            'created_at' => $this->created_at?->toDateTimeString(),
            'updated_at' => $this->updated_at?->toDateTimeString(),
            'tasks' => TaskResource::collection($this->whenLoaded('tasks')),
        ];
    }
}
