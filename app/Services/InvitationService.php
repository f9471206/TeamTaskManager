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

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * 多筆邀請
     */
    public function sendInviteArray(Team $team, array $userIds)
    {
        $results = ['success' => [], 'failed' => []];

        foreach ($userIds as $userId) {
            try {
                $this->sendInvite($team, $userId);
                $results['success'][] = $userId;
            } catch (\Exception $e) {
                $results['failed'][] = [
                    'user_id' => $userId,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return ['msg' => 'completed', 'data' => $results];
    }

    /**
     * 單筆邀請
     */
    public function sendInvite(Team $team, int $userId)
    {
        $user = User::findOrFail($userId);

        if ($team->members()->where('user_id', $userId)->exists()) {
            throw new ApiException('User is already a team member');
        }

        if (Invitation::where('team_id', $team->id)
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->exists()
        ) {
            throw new ApiException('A pending invitation for this user already exists');
        }

        DB::transaction(function () use ($team, $userId, $user) {
            $invitation = Invitation::create([
                'team_id' => $team->id,
                'user_id' => $userId,
                'status' => 'pending',
                'token' => Str::uuid()->toString(),
                'expires_at' => now()->addMinutes(5),
            ]);

            Notification::create([
                'user_id' => $userId,
                'message' => "邀請你加入 {$team->name} 團隊",
                'link' => "/teams/invitations/{$invitation->token}/accept",
                'type' => 'team_invite',
            ]);

            // 發送郵件
            Mail::to($user->email)->queue(new TeamInvitationMail($team, $user, $invitation->token));

            // 發送即時通知
            $notifications = $this->authService->getNotifications($userId);
            event(new TeamNotify(userId: $userId, message: $notifications));
        });

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
            if (!$invitation->team->members()->where('user_id', $invitation->user_id)->exists()) {
                $invitation->team->members()->attach($invitation->user_id, [
                    'role' => 'member',
                ]);
            }

            $invitation->status = 'accepted';
            $invitation->save();

            return $invitation->team;
        });
    }
}
