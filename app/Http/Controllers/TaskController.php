<?php

namespace App\Http\Controllers;

use App\Http\Resources\TaskResource;
use App\Models\Project;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    protected $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    /**
     * 專案新增任務
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|integer|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'due_date' => 'nullable|date',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $this->taskService->create($validated);

        return $this->success();
    }

    /**
     * 專案檢視任務
     * @param \App\Models\Task $task
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Task $task)
    {

        $res = $this->taskService->getDetails($task);

        return $this->success(TaskResource::make($res));

    }

    /**
     * 取得任務所有狀態
     * @return \Illuminate\Http\JsonResponse
     */
    public function status()
    {
        $res = $this->taskService->getStatus();

        return $this->success($res);
    }

    /**
     * 任務指派
     * @param \App\Models\Task $task
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function assign(Task $task, Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $this->taskService->assignTask($task, $validated);

        return $this->success();
    }

    /**
     * 編輯任務
     * @param \App\Models\Task $task
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Task $task, Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'due_date' => 'nullable|date',
            'status' => 'nullable|integer|in:0,1,2,3',
            'user_ids' => 'nullable|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $this->taskService->updateTask($task, $validated);
        return $this->success();
    }

    /**
     * 刪除任務
     * @param \App\Models\Task $task
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroyTask(Task $task)
    {
        $this->taskService->destroyTask($task);

        return $this->success();
    }

    /**
     * 取消指派人員
     * @param \App\Models\Task $task
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function unassign(Task $task, Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array', // 可以取消多個使用者
            'user_ids.*' => 'integer|exists:users,id',
        ]);

        $this->taskService->unassignTask($task, $validated);
        return $this->success();
    }

    /**
     * 取得指派人員名單(編輯任務使用)
     * @param \App\Models\Task $task
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignUsers(Task $task)
    {

        $res = $this->taskService->assignUsers($task);

        return $this->success($res);
    }

    /**
     * 取得指派人員名單(新增任務使用)
     * @param \App\Models\Project $project
     * @return \Illuminate\Http\JsonResponse
     */
    public function createTaskAssignUsers(Project $project)
    {
        $res = $this->taskService->createTaskAssignUsers($project);

        return $this->success($res);
    }
}
