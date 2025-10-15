<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    // 登入
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return $this->error('Invalid credentials', 401);
        }

        $user = Auth::user();
        $token = $user->createToken('api_token')->plainTextToken;

        return $this->success([
            'user' => $user,
            'token' => $token,
        ]);
    }

    // 註冊
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        $result = $this->authService->register($validated);

        return $this->success([
            'user' => $result['user'],
            'token' => $result['token'],
        ]);
    }

    // 登出
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return $this->success(data: ['message' => 'Logged out']);
    }

    // 取得當前使用者資訊
    public function me(Request $request)
    {
        return $this->success($request->user());
    }

    public function notification(Request $request)
    {

        $userId = $request->user()->id;

        $res = $this->authService->getNotifications($userId);

        return $this->success($res);
    }
    public function notificationRead($id)
    {
        $notification = $this->authService->markAsRead($id);

        if (!$notification) {
            return $this->error('通知不存在');
        }

        return $this->success(['success' => true, 'message' => '已讀', 'data' => $notification]);
    }
}
