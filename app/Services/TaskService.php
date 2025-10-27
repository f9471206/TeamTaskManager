<?php

namespace App\Services;

use App\Enums\TaskStatus;
use App\Exceptions\ApiException;
use App\Models\Project;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TaskService
{
    /**
     * 建立任務
     */
    public function create(array $data): Task
    {
        $authId = Auth::id();
        $project = Project::findOrFail($data['project_id']);
        $teamOwner = $project->team->owner;

        if ($authId !== $teamOwner->id) {
            throw new ApiException('只有團隊建立者才能新增任務');
        }

        // 驗證 user_ids 是否為團隊成員
        $userIds = $data['user_ids'] ?? [];
        $teamMemberIds = $project->team->members->pluck('id')->toArray();
        foreach ($userIds as $id) {
            if (!in_array($id, $teamMemberIds)) {
                throw new ApiException("User ID {$id} 不是團隊成員，無法指派任務");
            }
        }

        return DB::transaction(function () use ($data, $userIds, $project) {
            $task = Task::create([
                'project_id' => $project->id,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'due_date' => $data['due_date'] ?? null,
            ]);

            // 指派人員
            if (!empty($userIds)) {
                $attachData = [];
                foreach ($userIds as $uid) {
                    $attachData[$uid] = ['assigned_at' => now()];
                }
                $task->users()->sync($attachData);
            }

            return $task;
        });
    }

    /**
     * 編輯任務
     */
    public function updateTask(Task $task, array $data): Task
    {
        $authId = Auth::id();
        $teamOwner = $task->project->team->owner;

        if ($authId !== $teamOwner->id) {
            throw new ApiException('只有團隊建立者才能編輯任務');
        }

        $userIds = $data['user_ids'] ?? [];
        $teamMemberIds = $task->project->team->members->pluck('id')->toArray();
        foreach ($userIds as $id) {
            if (!in_array($id, $teamMemberIds)) {
                throw new ApiException("User ID {$id} 不是團隊成員，無法指派任務");
            }
        }

        // 驗證 status 是否有效
        if (isset($data['status']) && !in_array($data['status'], array_column(TaskStatus::cases(), 'value'))) {
            throw new ApiException("Task status {$data['status']} 無效");
        }

        return DB::transaction(function () use ($task, $data, $userIds) {
            $task->update([
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'due_date' => $data['due_date'] ?? null,
                'status' => $data['status'] ?? $task->status,
            ]);

            $attachData = [];
            foreach ($userIds as $uid) {
                $attachData[$uid] = ['assigned_at' => now()];
            }
            $task->users()->sync($attachData);

            return $task;
        });
    }

    /**
     * 刪除任務
     */
    public function destroyTask(Task $task): void
    {
        $authId = Auth::id();
        $teamOwner = $task->project->team->owner;

        if ($authId !== $teamOwner->id) {
            throw new ApiException('只有團隊建立者才能刪除任務');
        }

        DB::transaction(function () use ($task) {
            // 刪除 pivot table
            $task->users()->detach();

            // 如果有附件或通知，可在這裡刪除

            $task->delete();
        });
    }

    /**
     * 取得任務詳情
     */
    public function getDetails(Task $task): Task
    {
        $task->load(['users', 'project', 'project.team']);
        return $task;
    }

    /**
     * 取得任務所有狀態
     */
    public function getStatus(): array
    {
        return collect(TaskStatus::cases())
            ->map(fn($status) => [
                'id' => $status->value,
                'name' => $status->label(),
            ])
            ->all();
    }

    /**
     * 取得任務指派人員（編輯用）
     */
    public function assignUsers(Task $task)
    {
        $taskUserIds = $task->users->pluck('id')->toArray();
        $teamMembers = $task->project->team->members;

        return $teamMembers->map(function ($member) use ($taskUserIds) {
            $userArray = $member->toArray();
            $userArray['assigned'] = in_array($member->id, $taskUserIds);
            return $userArray;
        });
    }

    /**
     * 取得任務可指派人員（新增用）
     */
    public function createTaskAssignUsers(Project $project)
    {
        return $project->team->members;
    }
}
