<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\Project;
use App\Models\Team;
use Illuminate\Support\Facades\Auth;

class ProjectService
{

    /**
     * 新增專案
     * @param mixed $data
     * @throws \App\Exceptions\ApiException
     * @return void
     */
    public function createProject($data)
    {
        $authID = Auth::id();
        //只有團隊建立者才能新增專案
        $team = Team::findOrFail($data['team_id']);

        if ($team->owner->id !== $authID) {
            throw new ApiException('只有團隊建立者才能新增專案');
        }

        Project::create([
            'team_id' => $data['team_id'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'due_date' => $data['due_date'] ?? null,
            'created_by' => $authID,
        ]);
    }

    /**
     * 取的特定專案
     * @param \App\Models\Project $project
     * @return Project
     */
    public function getDetails(Project $project)
    {
        $project->load([
            // 篩選 tasks
            'tasks' => function ($taskQuery) {},
            // 篩選 users
            'tasks.users' => function ($userQuery) {},
        ]);

        return $project;
    }
}
