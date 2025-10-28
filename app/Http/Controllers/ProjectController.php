<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProjectResource;
use App\Models\Project;
use App\Services\ProjectService;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function __construct(
        protected ProjectService $projectService
    ) {

    }

    /**
     * 新增專案
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'team_id' => 'required|integer|exists:teams,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'due_date' => 'nullable|date',
        ]);

        $this->projectService->createProject($validated);

        return $this->success();
    }

    /**
     * 取得特定專案
     * @param \App\Models\Project $project
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Project $project)
    {
        $res = $this->projectService->getDetails($project);
        return $this->success(ProjectResource::make($res));
    }

    /**
     * 編輯專案
     * @param \App\Models\Project $project
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit(Project $project, Request $request)
    {
        $validated = $request->validate([
            'team_id' => 'required|integer|exists:teams,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'due_date' => 'nullable|date',
        ]);

        $this->projectService->updateProject($project, $validated);

        return $this->success();
    }

    /**
     * 刪除專案
     * @param \App\Models\Project $project
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Project $project)
    {
        $this->projectService->destroy($project);

        return $this->success();
    }
}
