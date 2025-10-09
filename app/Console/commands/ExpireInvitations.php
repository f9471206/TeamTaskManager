<?php

namespace App\Console\Commands;

use App\Models\Invitation;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ExpireInvitations extends Command
{
    protected $signature = 'invitations:expire';
    protected $description = 'Expire pending invitations past their expiration time';

    public function handle()
    {
        $expiredCount = Invitation::where('status', 'pending')
            ->where('expires_at', '<', Carbon::now())
            ->update(['status' => 'expired']);

        $this->info("Expired {$expiredCount} invitations.");
    }
}
