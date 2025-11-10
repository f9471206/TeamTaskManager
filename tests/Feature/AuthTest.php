<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /** @test
     * 登入成功
     */
    public function user_can_login_with_valid_credentials()
    {
        // 建立假使用者
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // 呼叫登入 API
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'msg',
                'data' => [
                    'token',
                    'user' => ['id', 'name', 'email'],
                ],
            ]);
    }

    /** @test
     * 登入失敗
     */
    public function user_cannot_login_with_wrong_password()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401) // 或依你實際回傳狀態碼修改
            ->assertJson([
                'msg' => 'Invalid credentials',
            ]);
    }

    /** @test
     * 成功註冊
     */
    public function user_can_register_successfully()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'msg' => 'created',
            ])
            ->assertJsonStructure([
                'msg',
                'data' => [
                    'token',
                    'user' => ['id', 'name', 'email'],
                ],
            ]);

        // 確認資料真的寫進資料庫
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
        ]);
    }

    /** @test
     * 註冊失敗：email已經存在
     */
    public function user_cannot_register_with_existing_email()
    {
        // 先建立一個使用者
        User::factory()->create(['email' => 'test@example.com']);

        // 嘗試用同樣 email 註冊
        $response = $this->postJson('/api/register', [
            'name' => 'New User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertStatus(422) // Laravel 驗證失敗
            ->assertJsonValidationErrors(['email']);
    }

    /** @test
     * 註冊失敗：必填欄位缺失
     */
    public function user_cannot_register_with_missing_fields()
    {
        $response = $this->postJson('/api/register', [
            'email' => 'test@example.com',
            // 缺少 name 和 password
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'password']);
    }

    /** @test
     * 登出
     */
    public function user_can_logout_successfully()
    {
        // 建立一個使用者
        $user = User::factory()->create();

        // 為這個使用者產生 token
        $token = $user->createToken('api_token')->plainTextToken;

        // 使用 token 認證呼叫登出
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/logout');

        // 驗證回傳
        $response->assertStatus(200)
            ->assertJson([
                'msg' => 'success',
                'data' => [
                    'message' => 'Logged out',
                ],
            ]);

        // 確認 token 已被刪除
        $this->assertDatabaseMissing('personal_access_tokens', [
            'tokenable_id' => $user->id,
            'name' => 'api_token',
        ]);
    }

    /** @test
     * 登出失敗
     */
    public function logout_requires_valid_token()
    {
        // 不帶 token 或 token 過期
        $response = $this->postJson('/api/logout');

        $response->assertStatus(401)
            ->assertJson([
                'msg' => 'Token 已過期或未授權，請重新登入',
                'code' => 401,
            ]);
    }
}
