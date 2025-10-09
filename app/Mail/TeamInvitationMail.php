<?php

namespace App\Mail;

use App\Models\Team;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TeamInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public $team;
    public $user;
    public $token;

    /**
     * 建構子，接收資料
     */
    public function __construct(Team $team, User $user, string $token)
    {
        $this->team = $team;
        $this->user = $user;
        $this->token = $token;
    }

    /**
     * 設定郵件內容
     */
    public function build()
    {
        return $this->subject('Invitation to join ' . $this->team->name)
            ->view('emails.team_invitation')
            ->with([
                'team' => $this->team,
                'user' => $this->user,
                'token' => $this->token,
            ]);
    }
}
