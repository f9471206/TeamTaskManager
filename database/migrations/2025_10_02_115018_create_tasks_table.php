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
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('title')->comment('任務標題');
            $table->text('description')->nullable()->comment('任務描述');
            $table->dateTime('due_date')->nullable()->comment('到期日');
            $table->tinyInteger('status')->default(0)->comment('任務狀態 0=待辦 1=進行中 2=完成 3=已封存');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
