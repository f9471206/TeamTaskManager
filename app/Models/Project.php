<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * 可批量填充的欄位
     */
    protected $fillable = [
        'team_id',
        'name',
        'description',
        'status',
        'created_by',
        'due_date',
    ];

    protected $casts = [
        'status' => ProjectStatus::class,
    ];

    /**
     * 專案所屬團隊
     */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * 專案建立者
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * 專案下的任務 (Tasks)
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

}
