<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Mail\TeamInvitationMail;
use App\Models\Invitation;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InvitationService
{

    public function sendInvite(Team $team, int $userId): Invitation
    {
        $user = User::findOrFail($userId);

        // 檢查是否已經是成員
        if ($team->members()->where('user_id', $userId)->exists()) {
            throw new ApiException('User is already a team member');
        }

        // 檢查是否已經有待處理的邀請
        // 這行程式碼會在發信前就檢查，避免重複發信
        if (Invitation::where('team_id', $team->id)->where('user_id', $userId)->where('status', 'pending')->exists()) {
            throw new ApiException('A pending invitation for this user already exists');
        }

        // 建立邀請
        $invitation = Invitation::create([
            'team_id' => $team->id,
            'user_id' => $userId,
            'status' => 'pending',
            'token' => Str::uuid()->toString(),
            'expires_at' => now()->addMinutes(5),
        ]);

        // 發送郵件
        Mail::to($user->email)->send(new TeamInvitationMail($team, $user, $invitation->token));

        return $invitation;
    }

    /**
     * 接受邀請
     */
    public function acceptInvite(string $token, $authID)
    {
        $invitation = Invitation::where('token', $token)
            ->where('status', 'pending')
            ->first();

        if (!$invitation) {
            throw new ApiException('過期');
        }

        if ($invitation->user_id != $authID) {
            throw new ApiException('非本人');
        }

        if ($invitation->expires_at && $invitation->expires_at->isPast()) {
            $invitation->status = 'expired';
            $invitation->save();
            throw new ApiException('Invitation has expired');
        }

        return DB::transaction(function () use ($invitation) {
            // 加入 team_user
            $invitation->team->members()->attach($invitation->user_id, [
                'role' => 'member',
            ]);

            // 更新邀請狀態
            $invitation->status = 'accepted';
            $invitation->save();

            return $invitation->team;
        });
    }
}
