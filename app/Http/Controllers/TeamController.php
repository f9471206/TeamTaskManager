<?php

namespace App\Http\Controllers;

use App\Http\Resources\InvitationsResource;
use App\Http\Resources\PageResource;
use App\Http\Resources\TeamResource;
use App\Models\Team;
use App\Services\TeamService;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    protected $teamService;

    public function __construct(TeamService $teamService)
    {
        $this->teamService = $teamService;
    }

    /**
     * 已加入團隊清單
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {

        $teams = $this->teamService->ownTeam($request);
        return $this->success(new PageResource(TeamResource::collection($teams)));

    }

    /**
     * 新增團隊
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        $this->teamService->createTeam($validated);

        return $this->success();
    }

    /**
     * 檢視團隊
     * @param \App\Models\Team $team
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Team $team)
    {
        $details = $this->teamService->getDetails($team);
        return $this->success(TeamResource::make($details));
    }

    /**
     * 所有使用者狀態
     * @param \App\Models\Team $team
     * @return \Illuminate\Http\JsonResponse
     */
    public function allUsersWithStatus(Team $team)
    {
        $datails = $this->teamService->allUsersWithStatus($team);

        return $this->success(InvitationsResource::collection($datails));
    }

    /**
     * 更新團隊
     * @param \App\Models\Team $team
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Team $team, Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        $this->teamService->updateTeam($team, $validated);
        return $this->success();
    }

    /**
     * 刪除團隊成員
     * @param \App\Models\Team $team
     * @param int $destroyMemberId
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroyMember(Team $team, int $destroyMemberId)
    {

        $this->teamService->destroyMember($team, $destroyMemberId);

        return $this->success();

    }

    /**
     * 刪除團隊
     * @param \App\Models\Team $team
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Team $team)
    {
        $this->teamService->destroy($team);

        return $this->success();
    }

}
