<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthService
{

    /**
     * 註冊
     * @param array $data
     * @return array{token: string, user: User}
     */
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
     * 取得通知
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

    /**
     * 已讀通知
     * @param int $id
     * @return Notification|\Illuminate\Database\Eloquent\Builder<Notification>
     */
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
