<?php

namespace App\Http\Controllers;

use App\Http\Resources\InvitationsResource;
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

    public function index(Request $request)
    {

        $teams = $this->teamService->ownTeam();

        // 用 Resource 轉成 JSON 回傳
        return $this->success(TeamResource::collection($teams));

    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        $this->teamService->createTeam($validated);

        return $this->success();
    }

    public function show(Team $team)
    {
        $details = $this->teamService->getDetails($team);
        return $this->success(TeamResource::make($details));
    }

    public function allUsersWithStatus(Team $team)
    {
        $datails = $this->teamService->allUsersWithStatus($team);

        return $this->success(InvitationsResource::collection($datails));
    }

    public function update(Team $team, Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
        ]);

        $this->teamService->updateTeam($team, $validated);
        return $this->success();
    }

    public function destroyMember(Team $team, int $destroyMemberId)
    {

        $this->teamService->destroyMember($team, $destroyMemberId);

        return $this->success();

    }

    public function destroy(Team $team)
    {
        $this->teamService->destroy($team);

        return $this->success();
    }

}
