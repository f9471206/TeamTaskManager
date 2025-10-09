<?php

namespace App\Models;

use App\Enums\TaskStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'project_id',
        'board_id',
        'title',
        'description',
        'due_date',
        'status',
    ];

    // 將 status 自動轉成 Enum
    protected $casts = [
        'status' => TaskStatus::class,
    ];

    /**
     * 關聯：Task 屬於哪個 Project
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * 關聯：Task 指派的使用者 (多對多)
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'task_user')
            ->withPivot('assigned_at')
            ->withTimestamps();
    }
}
