<?php

namespace App\Services;

use App\Events\TeamNotify;
use App\Exceptions\ApiException;
use App\Mail\TeamInvitationMail;
use App\Models\Invitation;
use App\Models\Notification;
use App\Models\Team;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InvitationService
{
    protected $authService;

    public function __construct(
        AuthService $authService
    ) {
        $this->authService = $authService;
    }

    public function sendInviteArray(Team $team, array $userIds)
    {
        $results = [
            'success' => [],
            'failed' => [],
        ];

        foreach ($userIds as $userId) {
            try {
                // 直接使用現有的單筆發送方法
                $this->sendInvite($team, $userId);

                $results['success'][] = $userId;
            } catch (\Exception $e) {
                // 若其中一筆失敗，記錄失敗原因
                $results['failed'][] = [
                    'user_id' => $userId,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return [
            'msg' => 'completed',
            'data' => $results,
        ];
    }

    public function sendInvite(Team $team, int $userId)
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

        $message = "邀請你加入 {$team->name} 團隊";

        Notification::create([
            'user_id' => $userId,
            'message' => $message,
            'link' => "/teams/invitations/{$invitation->token}/accept",
            'type' => 'team_invite',
        ]);

        $notifications = $this->authService->getNotifications($userId);

        // 發送通知
        event(new TeamNotify(userId: $userId, message: $notifications));

        return 'success';
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
