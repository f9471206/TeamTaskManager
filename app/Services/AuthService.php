<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function register(array $data): array
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $token = $user->createToken('api_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    /**
     * Summary of getNotifications
     * @param mixed $userId
     */
    public function getNotifications($userId)
    {
        $notifications = Notification::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->take(5) // 只取最新五筆
            ->get();

        return $notifications;
    }

    public function markAsRead(int $id): Notification | null
    {
        $notification = Notification::find($id);
        if ($notification && !$notification->read_at) {
            $notification->read_at = now();
            $notification->save();
        }
        return $notification;
    }
}
