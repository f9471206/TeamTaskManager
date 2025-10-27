<?php

namespace Tests\Feature;

use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamTest extends TestCase
{
    use RefreshDatabase;

    /** @test
     * 取得團隊清單
     */
    public function authenticated_user_can_list_own_teams()
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => 'owner']);

        $token = $user->createToken('api_token')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson('/api/teams');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'msg',
                'data' => [
                    'items' => [
                        [
                            'team_id',
                            'team_name',
                            'slug',
                            'description',
                            'created_at',
                            'updated_at',
                            'members',
                            'projects',
                        ],
                    ],
                    'pagination' => [
                        'current_page',
                        'per_page',
                        'total',
                        'last_page',
                    ],
                ],
            ]);
    }

    /** @test
     * 新增團隊
     */
    public function authenticated_user_can_create_team()
    {
        $user = User::factory()->create();
        $token = $user->createToken('api_token')->plainTextToken;

        $payload = ['name' => '新團隊', 'description' => '描述'];

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/teams', $payload);

        $response->assertStatus(201)
            ->assertJson(['msg' => 'created']);

        $this->assertDatabaseHas('teams', ['name' => '新團隊']);
        $this->assertDatabaseHas('team_user', ['user_id' => $user->id, 'role' => 'owner']);
    }

    /** @test
     * 檢視團隊
     */
    public function authenticated_user_can_view_team()
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => 'owner']);

        $token = $user->createToken('api_token')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson("/api/teams/{$team->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'msg',
                'data' => [
                    'team_id',
                    'team_name',
                    'slug',
                    'description',
                    'created_at',
                    'updated_at',
                    'members',
                    'projects',
                ],
            ]);
    }

    /** @test
     * 檢視團隊所有使用者狀態
     */
    public function authenticated_user_can_get_all_users_with_status()
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => 'owner']);

        $token = $user->createToken('api_token')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->getJson("/api/teams/{$team->id}/all-users");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'msg',
                'data' => [
                    '*' => ['id', 'name', 'email', 'is_member', 'is_owner', 'invite_status', 'expires_at'],
                ],
            ]);
    }

    /** @test
     * 更新團隊
     */
    public function authenticated_user_can_update_team()
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => 'owner']);

        $token = $user->createToken('api_token')->plainTextToken;
        $payload = ['name' => '更新團隊', 'description' => '更新描述'];

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->putJson("/api/teams/{$team->id}", $payload);

        $response->assertStatus(200)
            ->assertJson(['msg' => 'updated']);

        $this->assertDatabaseHas('teams', ['id' => $team->id, 'name' => '更新團隊']);
    }

    /** @test
     * 刪除團隊成員
     */
    public function authenticated_user_can_remove_team_member()
    {
        $user = User::factory()->create();
        $member = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach([$user->id => ['role' => 'owner'], $member->id => ['role' => 'member']]);

        $token = $user->createToken('api_token')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->deleteJson("/api/teams/{$team->id}/members/{$member->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('team_user', ['team_id' => $team->id, 'user_id' => $member->id]);
    }

    /** @test
     * 刪除團隊
     */
    public function authenticated_user_can_delete_team()
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => 'owner']);

        $token = $user->createToken('api_token')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->deleteJson("/api/teams/{$team->id}");

        $response->assertStatus(204);
        $this->assertSoftDeleted('teams', ['id' => $team->id]);
    }

    /** @test
     * 未授權無法新增團隊
     */
    public function guest_cannot_create_team()
    {
        $payload = ['name' => '新團隊', 'description' => '描述'];

        $response = $this->postJson('/api/teams', $payload);

        $response->assertStatus(401)
            ->assertJson(['msg' => 'Token 已過期或未授權，請重新登入']);
    }

    /** @test
     * 新增團隊資料驗證失敗
     */
    public function authenticated_user_cannot_create_team_with_invalid_data()
    {
        $user = User::factory()->create();
        $token = $user->createToken('api_token')->plainTextToken;

        // name 缺少
        $payload = ['description' => '描述'];

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson('/api/teams', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }

    /** @test
     * 非 owner 無法更新團隊
     */
    public function non_owner_cannot_update_team()
    {
        $owner = User::factory()->create();
        $nonOwner = User::factory()->create();

        $team = Team::factory()->create();
        $team->members()->attach($owner->id, ['role' => 'owner']);
        $team->members()->attach($nonOwner->id, ['role' => 'member']);

        $token = $nonOwner->createToken('api_token')->plainTextToken;

        $payload = ['name' => '更新團隊', 'description' => '更新描述'];

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->putJson("/api/teams/{$team->id}", $payload);

        $response->assertStatus(403);
    }

    /** @test
     * 非 owner 無法刪除團隊
     */
    public function non_owner_cannot_delete_team()
    {
        $owner = User::factory()->create();
        $nonOwner = User::factory()->create();

        $team = Team::factory()->create();
        $team->members()->attach($owner->id, ['role' => 'owner']);
        $team->members()->attach($nonOwner->id, ['role' => 'member']);

        $token = $nonOwner->createToken('api_token')->plainTextToken;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->deleteJson("/api/teams/{$team->id}");

        $response->assertStatus(403);
    }

    /** @test
     * 刪除不存在的團隊成員
     */
    public function removing_nonexistent_member_returns_error()
    {
        $user = User::factory()->create();
        $team = Team::factory()->create();
        $team->members()->attach($user->id, ['role' => 'owner']);

        $token = $user->createToken('api_token')->plainTextToken;

        $nonexistentMemberId = 9999;

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->deleteJson("/api/teams/{$team->id}/members/{$nonexistentMemberId}");

        $response->assertStatus(404)
            ->assertJson(['msg' => '該成員不在此團隊中。']); // 根據你 service 回傳訊息
    }

}
