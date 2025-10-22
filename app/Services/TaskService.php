<?php

namespace App\Services;

use App\Enums\TaskStatus;
use App\Exceptions\ApiException;
use App\Models\Project;
use App\Models\Task;
use App\Models\Team;
use Illuminate\Support\Facades\Auth;

class TaskService
{

    /**
     * 新增專案的任務
     * @param mixed $data
     * @throws \App\Exceptions\ApiException
     * @return bool
     */
    public function create($data)
    {
        $authId = Auth::id();
        $project = Project::findOrFail($data['project_id']);
        $teamOwner = $project->team->owner;

        // 檢查是否為團隊建立者
        if ($authId !== $teamOwner->id) {
            throw new ApiException('只有團隊建立者才能新增任務');
        }

        $userIds = $data['user_ids'];

        $task = Task::create([
            'project_id' => $data['project_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'due_date' => $data['due_date'] ?? null,
        ]);

        $task->users()->sync($userIds);

        return true;
    }

    /**
     * 取的特定任務
     * @param \App\Models\Task $task
     * @return Task
     */
    public function getDetails(Task $task)
    {
        return $task; // 已經是 Task 模型實例
    }

    /**
     * 取得任務所有狀態
     * @return array{id: int, name: string[]}
     */
    public function getStatus()
    {
        return collect(TaskStatus::cases())
            ->map(fn($status) => [
                'id' => $status->value,
                'name' => $status->label(),
            ])
            ->all();
    }

    /**
     * 新增Task的指派人員
     * @param \App\Models\Task $task
     * @param mixed $data
     * @throws \App\Exceptions\ApiException
     * @return void
     */
    public function assignTask(Task $task, $data)
    {
        $auth = Auth::id();

        // 檢查權限：只有團隊建立者能編輯任務
        if ($task->project->team->owner->id !== $auth) {
            throw new ApiException('只有團隊建立者才能編輯任務');
        }

        // 整理要綁定的資料（包含 assigned_at）
        $attachData = [];
        foreach ($data['user_ids'] as $userId) {
            $attachData[$userId] = ['assigned_at' => now()];
        }

        // 先刪除全部舊的，再新增新的（sync 會自動處理）
        $task->users()->sync($attachData);
    }

    /**
     * 編輯 task
     * @param \App\Models\Task $task
     * @param array $data
     * @throws \App\Exceptions\ApiException
     * @return Task
     */
    public function updateTask(Task $task, array $data)
    {
        $authID = Auth::id();

        // 取得任務所屬的 project 與 team
        $project = $task->project;
        $team = $project->team;

        // 只有團隊建立者可以編輯
        if ($team->owner->id !== $authID) {
            throw new ApiException('只有團隊建立者才能編輯任務');
        }

        $userIds = $data['user_ids'];
        // 更新任務資料
        $task->update([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'due_date' => $data['due_date'] ?? null,
            'status' => $data['status'] ?? null,
        ]);

        $task->users()->sync($userIds);

        return $task;
    }

    /**
     * 刪除 Task
     * @param \App\Models\Task $task
     * @throws \App\Exceptions\ApiException
     * @return void
     */
    public function destroyTask(Task $task)
    {

        $authID = Auth::id();

        // 取得任務所屬的 project 與 team
        $project = $task->project;
        $team = $project->team;

        // 只有團隊建立者可以編輯
        if ($team->owner->id !== $authID) {
            throw new ApiException('只有團隊建立者才能編輯任務');
        }

        $task->users()->detach();

        $task->delete();
    }

    /**
     * 刪除 task 的指派人員
     * @param \App\Models\Task $task
     * @param mixed $data
     * @throws \App\Exceptions\ApiException
     * @return void
     */
    public function unassignTask(Task $task, $data)
    {
        $auth = Auth::id();
        //檢查
        if ($task->project->team->owner->id !== $auth) {
            throw new ApiException('只有團隊建立者才能編輯任務');
        }

        // 移除關聯
        $task->users()->detach($data['user_ids']);
    }

    /**
     * 取得指派人員名單(編輯任務使用)
     * @param \App\Models\Task $task
     */
    public function assignUsers(Task $task)
    {
        // 1. 取得已分配的用戶 ID 列表，用於快速查詢
        $taskUserIds = $task->users->pluck('id')->toArray();

        // 2. 取得團隊成員列表
        $teamMembers = $task->project->team->members;

        // 3. 處理集合：迭代團隊成員，並新增一個 'assigned' 標籤
        $result = $teamMembers->map(function ($member) use ($taskUserIds) {
            // 檢查成員 ID 是否在已分配的 ID 列表中
            $isAssigned = in_array($member->id, $taskUserIds);

            // 將用戶模型轉換為陣列，然後新增 'assigned' 標籤
            $userArray = $member->toArray();
            $userArray['assigned'] = $isAssigned;

            return $userArray;
        });
        return $result;
    }

    /**
     * 取得指派人員名單(新增任務使用)
     * @param \App\Models\Project $project
     * @return \App\Models\User
     */
    public function createTaskAssignUsers(Project $project)
    {
        $teamMembers = $project->team->members;

        return $teamMembers;
    }
}
