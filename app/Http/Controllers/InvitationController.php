<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Services\InvitationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InvitationController extends Controller
{
    protected $invitationService;

    public function __construct(InvitationService $invitationService)
    {
        $this->invitationService = $invitationService;
    }

    /**
     * 發送邀請
     */
    public function send(Request $request, Team $team)
    {
        $request->validate([
            'user_id' => 'required',
        ]);

        $userIds = $request->input('user_id');

        // ✅ 判斷是單筆還是多筆
        if (is_array($userIds)) {
            // 多筆邀請
            $result = $this->invitationService->sendInviteArray($team, $userIds);

            return response()->json([
                'message' => 'Invitations sent',
                'result' => $result,
            ]);
        } else {
            // 單筆邀請
            $invitation = $this->invitationService->sendInvite($team, (int) $userIds);

            return response()->json([
                'message' => 'Invitation sent',
                'invitation' => $invitation,
            ]);
        }
    }

    // 接受邀請
    public function accept($token)
    {
        $authID = auth()->id();

        $team = $this->invitationService->acceptInvite($token, $authID);

        return response()->json([
            'message' => 'Invitation accepted successfully',
            'team' => $team,
        ]);
    }

}
