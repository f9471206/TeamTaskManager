<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     * 新增專案
     */
    public function authenticated_user_can_create_project()
    {
        // 建立使用者與團隊
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => 'owner']);

        // 登入並取得 token
        $token = $user->createToken('api_token')->plainTextToken;

        // 準備送出的資料
        $payload = [
            'team_id' => $team->id,
            'name' => '登入功能',
            'description' => '帳號登入邏輯',
            'due_date' => now()->addDays(7)->toDateString(),
        ];

        // 呼叫 API
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/projects', $payload);

        // 驗證回傳狀態與格式
        $response->assertStatus(200)
            ->assertJson(['msg' => 'success']);

        // 驗證資料庫有這筆資料
        $this->assertDatabaseHas('projects', [
            'team_id' => $team->id,
            'name' => '登入功能',
            'description' => '帳號登入邏輯',
        ]);
    }

    /**
     * @test
     * 未授權新增
     */
    public function guest_cannot_create_project()
    {
        $team = Team::factory()->create();

        $payload = [
            'team_id' => $team->id,
            'name' => '未授權建立',
            'description' => '這不應該成功',
        ];

        $response = $this->postJson('/api/projects', $payload);

        $response->assertStatus(401)
            ->assertJson([
                'msg' => 'Token 已過期或未授權，請重新登入',
                'code' => 401,
            ]);
    }

    /**
     * @test
     * 不存在團隊
     */
    public function cannot_create_project_with_invalid_team_id()
    {
        $user = User::factory()->create();
        $token = $user->createToken('api_token')->plainTextToken;

        $payload = [
            'team_id' => 999, // 不存在的 team_id
            'name' => '無效團隊',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/projects', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['team_id']);
    }

    /**
     * @test
     * 取得專案清單
     */
    public function authenticated_user_can_view_project_detail()
    {
        // 建立使用者、團隊、專案
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => 'owner']);

        $project = Project::factory()->create([
            'team_id' => $team->id,
            'created_by' => $user->id,
            'name' => 'testtt123',
            'description' => 'test123',
            'status' => 0,
        ]);

        $token = $user->createToken('api_token')->plainTextToken;

        // 呼叫 API
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/projects/{$project->id}");

        // 驗證 HTTP 狀態
        $response->assertStatus(200);

        // 驗證 JSON 結構
        $response->assertJsonStructure([
            'msg',
            'data' => [
                'id',
                'team_id',
                'name',
                'description',
                'status' => ['value', 'label', 'color'],
                'created_by',
                'due_date',
                'created_at',
                'updated_at',
                'tasks',
            ],
        ]);

        // 驗證回傳內容正確
        $response->assertJson([
            'msg' => 'success',
            'data' => [
                'id' => $project->id,
                'team_id' => $team->id,
                'name' => 'testtt123',
                'description' => 'test123',
                'status' => [
                    'value' => 0,
                    'label' => '進行中',
                    'color' => 'success',
                ],
                'created_by' => $user->id,
                'tasks' => [],
            ],
        ]);
    }

    /**
     * @test
     * 未授權編輯
     */
    public function guest_cannot_view_project_detail()
    {
        $project = Project::factory()->create();

        $response = $this->getJson("/api/projects/{$project->id}");

        $response->assertStatus(401)
            ->assertJson([
                'msg' => 'Token 已過期或未授權，請重新登入',
                'code' => 401,
            ]);
    }

    /**
     * @test
     * 編輯專案
     */
    public function authenticated_user_can_edit_project()
    {
        // 建立使用者與團隊
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => 'owner']);

        // 建立專案
        $project = Project::factory()->create([
            'team_id' => $team->id,
            'name' => '原始名稱',
            'description' => '原始描述',
        ]);

        // 模擬登入
        $this->actingAs($user);

        // 新資料
        $payload = [
            'team_id' => $team->id,
            'name' => '更新後名稱',
            'description' => '更新後描述',
            'due_date' => now()->addDays(3)->toDateString(),
        ];

        // 發送請求
        $response = $this->putJson("/api/projects/{$project->id}", $payload);

        // 驗證回應
        $response->assertStatus(200)
            ->assertJson([
                'msg' => 'success',
            ]);

        // 驗證資料庫確實更新
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'name' => '更新後名稱',
            'description' => '更新後描述',
        ]);
    }

    /**
     * @test
     * 非擁有者無法編輯
     */
    public function non_owner_cannot_edit_project()
    {
        // 建立擁有者與團隊
        $owner = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($owner->id, ['role' => 'owner']);

        // 建立專案
        $project = Project::factory()->create([
            'team_id' => $team->id,
            'created_by' => $owner->id,
            'name' => 'ProjectX',
        ]);

        // 建立另一個非擁有者
        $user = User::factory()->create();
        $team->members()->attach($user->id, ['role' => 'member']);

        // 模擬非擁有者登入
        $this->actingAs($user);

        $payload = [
            'team_id' => $team->id,
            'name' => '試圖修改',
            'description' => '不該被允許',
        ];

        $response = $this->putJson("/api/projects/{$project->id}", $payload);

        // 驗證權限錯誤（403）
        $response->assertStatus(403)
            ->assertJson([
                'msg' => '只有團隊建立者才能編輯專案',
            ]);

        // 驗證資料庫未被修改
        $this->assertDatabaseMissing('projects', [
            'name' => '試圖修改',
        ]);
    }

    /**
     * @test
     * 刪除專案
     */
    public function authenticated_owner_can_delete_project()
    {
        // 建立使用者與團隊
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => 'owner']);

        // 建立專案
        $project = Project::factory()->create([
            'team_id' => $team->id,
            'name' => '刪除測試專案',
        ]);

        // 模擬登入
        $this->actingAs($user);

        // 執行刪除請求
        $response = $this->deleteJson("/api/projects/{$project->id}");

        // 驗證回應
        $response->assertStatus(200)
            ->assertJson([
                'msg' => 'success',
            ]);

        // 驗證軟刪除
        $this->assertSoftDeleted('projects', ['id' => $project->id]);
    }

    /**
     * @test
     * 非擁有者無法刪除
     */
    public function non_owner_cannot_delete_project()
    {
        // 建立擁有者與團隊
        $owner = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($owner->id, ['role' => 'owner']);

        // 建立專案
        $project = Project::factory()->create([
            'team_id' => $team->id,
            'name' => 'ProtectedProject',
        ]);

        // 建立另一個非擁有者
        $user = User::factory()->create();
        $team->members()->attach($user->id, ['role' => 'member']);

        // 模擬登入
        $this->actingAs($user);

        // 嘗試刪除
        $response = $this->deleteJson("/api/projects/{$project->id}");

        // 驗證權限錯誤
        $response->assertStatus(403)
            ->assertJson([
                'msg' => '只有團隊建立者才能刪除專案',
            ]);

        // 確保未被刪除
        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'deleted_at' => null,
        ]);
    }
}
