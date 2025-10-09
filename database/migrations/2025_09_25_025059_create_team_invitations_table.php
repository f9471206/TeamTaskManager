<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitations', function (Blueprint $table) {
            $table->id();

            // 邀請對應的團隊
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();

            // 邀請的使用者
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // 邀請狀態
            $table->enum('status', ['pending', 'accepted', 'expired'])->default('pending')->comment('邀請狀態');

            $table->string('token', 64)->unique()->comment('邀請唯一識別碼');

            $table->timestamp('expires_at')->nullable()->comment('邀請過期時間');

            $table->timestamps();

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitations');
    }
};
