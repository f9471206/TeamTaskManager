<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\Project;
use App\Models\Team;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProjectService
{
    /**
     * 新增專案
     */
    public function createProject(array $data): Project
    {
        $authID = Auth::id();
        $team = Team::findOrFail($data['team_id']);
        if ($team->owner->id !== $authID) {
            throw new ApiException('只有團隊建立者才能新增專案');
        }

        return DB::transaction(function () use ($team, $data, $authID) {
            $project = Project::create([
                'team_id' => $team->id,
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'due_date' => $data['due_date'] ?? null,
                'created_by' => $authID,
            ]);

            return $project;
        });
    }

    /**
     * 取得特定專案及關聯
     */
    public function getDetails(Project $project): Project
    {
        $project->load([
            'tasks' => fn($query) => $query->orderBy('created_at', 'desc'),
            'tasks.users' => fn($query) => $query->select(),
        ]);

        return $project;
    }

    /**
     * 編輯專案
     */
    public function updateProject(Project $project, array $data): Project
    {
        $authID = Auth::id();
        $team = $project->team;

        if ($team->owner->id !== $authID) {
            throw new ApiException('只有團隊建立者才能編輯專案', 403);
        }

        return DB::transaction(function () use ($project, $data) {
            $project->update([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'due_date' => $data['due_date'] ?? null,
                // 建議不要修改 created_by，保留原創建者
            ]);

            return $project;
        });
    }

    /**
     * 刪除專案及相關資料
     */
    public function destroy(Project $project): void
    {
        $authID = Auth::id();
        $team = $project->team;

        if ($team->owner->id !== $authID) {
            throw new ApiException('只有團隊建立者才能刪除專案', 403);
        }

        DB::transaction(function () use ($project) {
            // 刪除關聯 tasks
            if ($project->tasks()->exists()) {
                $project->tasks()->delete();
            }

            // 刪除專案
            $project->delete();
        });
    }
}
