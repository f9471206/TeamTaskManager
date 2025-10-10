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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|integer|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'due_date' => 'nullable|date',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $this->taskService->create($validated);

        return $this->success();
    }

    public function show(Task $task)
    {

        $res = $this->taskService->getDetails($task);

        return $this->success(TaskResource::make($res));

    }

    public function assign(Task $task, Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $this->taskService->assignTask($task, $validated);

        return $this->success();
    }

    public function update(Task $task, Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'due_date' => 'nullable|date',
        ]);

        $this->taskService->updateTask($task, $validated);
        return $this->success();
    }

    public function destroyTask(Task $task)
    {
        $this->taskService->destroyTask($task);

        return $this->success();
    }

    public function unassign(Task $task, Request $request)
    {
        $validated = $request->validate([
            'user_ids' => 'required|array', // 可以取消多個使用者
            'user_ids.*' => 'integer|exists:users,id',
        ]);

        $this->taskService->unassignTask($task, $validated);
        return $this->success();
    }

    public function assignUsers(Task $task)
    {

        $res = $this->taskService->assignUsers($task);

        return $this->success($res);
    }

    public function createTaskAssignUsers(Project $project)
    {
        $res = $this->taskService->createTaskAssignUsers($project);

        return $this->success($res);
    }
}
