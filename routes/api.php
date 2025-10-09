<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->name('login');

// 需要認證的路由
Route::middleware(['auth:sanctum', 'token.expiry'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/me', [AuthController::class, 'me']);

    // 團隊 Api 群組
    Route::prefix('team')->group(function () {
        Route::get('/', [TeamController::class, 'index']); // 取得自己的團隊列表
        Route::post('/', [TeamController::class, 'store']); // 建立新團隊
        Route::get('/{team}', [TeamController::class, 'show']); // 取得單一團隊
        Route::get('{team}/all-users', [TeamController::class, 'allUsersWithStatus']);
        Route::put('/{team}', [TeamController::class, 'update']); // 更新團隊資訊
        Route::delete('/{team}/{destroyMemberId}', [TeamController::class, 'destroyMember']); // 團隊成員刪除
        Route::delete('/{team}', [TeamController::class, 'destroy']); // 刪除團隊
        Route::post('{team}/invite', [InvitationController::class, 'send']); // 發送邀請 新增團隊成員
        Route::get('invitations/{token}/accept', [InvitationController::class, 'accept']); // 接受邀請
    });

    // project
    Route::prefix('project')->group(function () {
        Route::get('/test', [ProjectController::class, 'test']);
        Route::post('/', [ProjectController::class, 'store']);
        Route::get('/{project}', [ProjectController::class, 'show']);
    });

    Route::prefix('task')->group(function () {
        route::post('/', [TaskController::class, 'store']);
        route::get('/{task}', [TaskController::class, 'show']);
        Route::post('/{task}/assign', [TaskController::class, 'assign']);
        Route::put('/{task}', [TaskController::class, 'update']);
        Route::delete('/{task}', [TaskController::class, 'destroyTask']);
        Route::delete('/{task}/unassign', [TaskController::class, 'unassign']);
        Route::get('/{task}/assignUsersList', [TaskController::class, 'assignUsers']);
    });
});
