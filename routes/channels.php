<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::routes(['middleware' => ['auth:sanctum']]);
Broadcast::channel('user.{id}', function (User $user, int $id) {
    return $user && ((int) $user->id === (int) $id);
});
