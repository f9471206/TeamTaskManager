<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\Invitation;
use App\Models\Team;
use App\Models\User;
use App\Traits\HasPaginationAndSearch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TeamService
{
    use HasPaginationAndSearch;

    /**
     * 建立新團隊
     *
     * @param array $data
     * @return Team
     */
    public function createTeam(array $data): Team
    {
        return DB::transaction(function () use ($data) {
            $team = Team::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
            ]);

            $team->members()->attach(Auth::id(), ['role' => 'owner']);

            return $team;
        });

    }

    /**
     * Summary of ownTeam
     * @param mixed $request
     * @return \Illuminate\Pagination\LengthAwarePaginator
     */
    public function ownTeam($request)
    {
        $authID = Auth::id();

        $params = $this->parseListParams($request);

        $query = Team::whereHas('members', function ($q) use ($authID) {
            $q->where('user_id', $authID);
        });

        $sort = in_array($params['sort'], ['id', 'name', 'created_at', 'updated_at']) ? $params['sort'] : 'created_at';
        $direction = strtolower($params['direction']) === 'asc' ? 'asc' : 'desc';
        $query->orderBy($sort, $direction);

        $perPage = $params['per_page'] > 0 ? $params['per_page'] : 10;
        $res = $query->paginate($perPage);

        return $res;
    }

    /**
     * 取的團隊明細
     * @param mixed $team
     * @throws \App\Exceptions\ApiException
     */
    public function getDetails($team)
    {
        if (!$team->members()->where('user_id', Auth::id())->exists()) {
            throw new ApiException('您不是此團隊成員，無權查看該團隊的詳細資料。');
        }

        $team->load([
            // 載入成員，並在載入成員時，載入中間表欄位 'role' 和 'status'
            'members' => function ($query) {
                $query->withPivot(['role', 'status']);
            },
            'projects' => function ($query) {},
        ]);

        return $team;
    }

    /**
     * 取得所有使用者
     * @param \App\Models\Team $team
     * @return \Illuminate\Database\Eloquent\Collection<int, User>|\Illuminate\Support\Collection<int, User>
     */
    public function allUsersWithStatus(Team $team)
    {
        // 取得團隊所有成員
        $teamMembers = $team->members;

        // 取得團隊 owner
        $owner = $team->owner;

        // 取得所有使用者
        $allUsers = User::all();

        // 取得這個團隊的所有邀請，key = user_id, value = status
        $invitations = Invitation::where('team_id', $team->id)
            ->pluck('status', 'user_id')
            ->toArray();

        $invitationsExpires_at = Invitation::where('team_id', $team->id)
            ->pluck('expires_at', 'user_id')
            ->toArray();

        // 標記是否為成員 & 是否為 owner
        $usersWithStatus = $allUsers->map(function ($user) use ($teamMembers, $owner, $invitations, $invitationsExpires_at) {
            $user->is_member = $teamMembers->contains($user->id);
            $user->is_owner = $owner ? $user->id === $owner->id : false;
            $user->invite_status = $invitations[$user->id] ?? null; // null = 未邀請
            $user->expires_at = $invitationsExpires_at[$user->id] ?? null;

            return $user;
        });

        return $usersWithStatus;
    }

    /**
     * 更新團隊資訊
     * @param mixed $team
     * @param mixed $data
     * @throws \App\Exceptions\ApiException
     * @return void
     */
    public function updateTeam($team, $data)
    {
        $userId = Auth::id();
        if (!$team->members()->where('user_id', $userId)->exists()) {
            throw new ApiException('您不是此團隊成員，無權查看該團隊的詳細資料。');
        }
        // 確保 Owner 關聯是正確的，並且只檢查當前登入的使用者
        $isOwner = $team->owner->id === $userId;
        // 如果不是擁有者，拋出權限不足例外
        if (!$isOwner) {
            throw new ApiException('只有團隊建立者（擁有者）才能修改團隊資料。', 403);
        }

        $team->update([
            'name' => $data['name'],
            'description' => $data['description'],
        ]);
    }

    /**
     * 刪除團隊會員
     * @param \App\Models\Team $team
     * @param int $destroyMemberId
     * @throws \App\Exceptions\ApiException
     * @return void
     */
    public function destroyMember(Team $team, int $destroyMemberId)
    {
        $userId = Auth::id();
        if (!$team->members()->where('user_id', $userId)->exists()) {
            throw new ApiException('您不是此團隊成員，無權查看該團隊的詳細資料。');
        }
        // 確保 Owner 關聯是正確的，並且只檢查當前登入的使用者
        $isOwner = $team->owner->id === $userId;
        // 如果不是擁有者，拋出權限不足例外
        if (!$isOwner) {
            throw new ApiException('只有團隊建立者（擁有者）才能修改團隊資料。', 403);
        }

        // 確認被刪除的成員存在於 team_user pivot 表
        $exists = $team->members()->where('user_id', $destroyMemberId)->exists();
        if (!$exists) {
            throw new ApiException('該成員不在此團隊中。', 404);
        }

        if ($userId === $destroyMemberId) {
            throw new ApiException('無法將自己剔除');
        }

        // 執行刪除 (pivot table: team_user)
        $team->members()->detach($destroyMemberId);
    }

    /**
     * 刪除團隊
     * @param \App\Models\Team $team
     * @throws \App\Exceptions\ApiException
     * @return void
     */
    public function destroy(Team $team)
    {
        $userId = Auth::id();

        $isOwner = $team->owner->id === $userId;
        // 如果不是擁有者，拋出權限不足例外
        if (!$isOwner) {
            throw new ApiException('只有團隊建立者（擁有者）才能刪除團隊。', 403);
        }

        $team->members()->detach();

        $team->delete();
    }
}
