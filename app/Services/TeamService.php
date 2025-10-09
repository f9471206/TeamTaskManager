<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\Invitation;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class TeamService
{
    /**
     * 建立新團隊
     *
     * @param array $data
     * @return Team
     */
    public function createTeam(array $data): Team
    {
        // 建立團隊
        $team = Team::create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);

        // 把建立者加入 pivot table，role = owner
        $team->members()->attach(Auth::id(), [
            'role' => 'owner',
        ]);

        return $team;
    }

    /**
     *
     */
    public function ownTeam()
    {
        $authID = Auth::id();

        // 取得 pivot role = 'owner' 的第一個 team
        $res = Team::whereHas('members', function ($query) use ($authID) {
            $query->where('user_id', $authID);
        })->get();

        // Collection of names
        return $res;
    }

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

        // 3. 返回結果
        // 這裡我們返回 Model 的陣列表示形式，包含了預先載入的成員數據
        return $team;
    }

    public function allUsersWithStatus(Team $team)
    {
        // 取得團隊所有成員
        $teamMembers = $team->members; // Collection of User

        // 取得團隊 owner
        $owner = $team->owner; // User 或 null

        // 取得所有使用者
        $allUsers = User::all();

        // 取得這個團隊的所有邀請，key = user_id, value = status
        $invitations = Invitation::where('team_id', $team->id)
            ->pluck('status', 'user_id') // [user_id => status]
            ->toArray();

        $invitationsExpires_at = Invitation::where('team_id', $team->id)
            ->pluck('expires_at', 'user_id') // [user_id => status]
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
            // 由於這是更嚴格的權限檢查，我們使用相同的 403 錯誤
            throw new ApiException('只有團隊建立者（擁有者）才能修改團隊資料。', 403);
        }

        $team->update([
            'name' => $data['name'],
            'description' => $data['description'],
        ]);
    }

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
            // 由於這是更嚴格的權限檢查，我們使用相同的 403 錯誤
            throw new ApiException('只有團隊建立者（擁有者）才能修改團隊資料。');
        }

        // 確認被刪除的成員存在於 team_user pivot 表
        $exists = $team->members()->where('user_id', $destroyMemberId)->exists();
        if (!$exists) {
            throw new ApiException('該成員不在此團隊中。');
        }

        if ($userId === $destroyMemberId) {
            throw new ApiException('無法將自己剔除');
        }

        // 執行刪除 (pivot table: team_user)
        $team->members()->detach($destroyMemberId);
    }

    public function destroy(Team $team)
    {
        $userId = Auth::id();

        $isOwner = $team->owner->id === $userId;
        // 如果不是擁有者，拋出權限不足例外
        if (!$isOwner) {
            // 由於這是更嚴格的權限檢查，我們使用相同的 403 錯誤
            throw new ApiException('只有團隊建立者（擁有者）才能刪除團隊。');
        }

        $team->members()->detach();

        $team->delete();
    }
}
