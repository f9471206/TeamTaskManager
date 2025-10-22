<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/register', [AuthController::class, 'register']);

// 需要認證的路由
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/notification', [AuthController::class, 'notification']);
    Route::post('/notification/{id}/read', [AuthController::class, 'notificationRead']);

    // 團隊 Api 群組
    Route::prefix('teams')->group(function () {
        Route::get('/', [TeamController::class, 'index']); // 取得自己的團隊列表
        Route::post('/', [TeamController::class, 'store']); // 建立新團隊
        Route::get('/{team}', [TeamController::class, 'show']); // 取得單一團隊
        Route::get('{team}/all-users', [TeamController::class, 'allUsersWithStatus']);
        Route::put('/{team}', [TeamController::class, 'update']); // 更新團隊資訊
        Route::delete('/{team}/{destroyMemberId}', [TeamController::class, 'destroyMember']); // 團隊成員刪除
        Route::delete('/{team}', [TeamController::class, 'destroy']); // 刪除團隊
        Route::post('/{team}/invite', [InvitationController::class, 'send']); // 發送邀請 新增團隊成員
        Route::get('/invitations/{token}/accept', [InvitationController::class, 'accept']); // 接受邀請
    });

    // 團隊專案
    Route::prefix('projects')->group(function () {
        Route::get('/{project}', [ProjectController::class, 'show']); // 取得專案
        Route::put('/{project}', [ProjectController::class, 'edit']); // 編輯專案
        Route::post('/', [ProjectController::class, 'store']); // 新增專案
    });

    // 專案內任務
    Route::prefix('tasks')->group(function () {
        Route::get('/createTaskAssignUsersList/{project}', [TaskController::class, 'createTaskAssignUsers']); //取得指派人員名單(新增使用)
        Route::get('/assignUsersList/{task}', [TaskController::class, 'assignUsers']); //取得指派人員名單(編輯使用)
        route::post('/', [TaskController::class, 'store']); // 新增任務
        route::get('/status', [TaskController::class, 'status']); // 取的所有任務狀態
        route::get('/{task}', [TaskController::class, 'show']); // 取的特定任務
        Route::put('/{task}', [TaskController::class, 'update']); // 編輯任務
        Route::delete('/{task}', [TaskController::class, 'destroyTask']); // 刪除任務
        Route::post('/{task}/assign', [TaskController::class, 'assign']); // 新增任務
        Route::delete('/{task}/unassign', [TaskController::class, 'unassign']); // 刪除指派人員
    });
});
