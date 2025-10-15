<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('收通知的使用者ID');
            $table->string('type')->nullable()->comment('通知類型');
            $table->string('message')->comment('通知訊息');
            $table->string('link')->nullable()->comment('點擊導向的連結或 API');
            $table->timestamp('read_at')->nullable()->comment('已讀時間，NULL 表示未讀');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
