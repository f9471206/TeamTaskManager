<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Team extends Model
{
    use HasFactory, SoftDeletes;

    // 可以批量填充的欄位
    protected $fillable = [
        'name',
        'description',
        'slug',
    ];

    protected static function booted()
    {
        static::creating(function ($team) {
            $team->slug = Str::slug($team->name) . '-' . Str::uuid()->toString();
        });
    }

    public function members()
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * 建立者 (Owner)
     */
    public function getOwnerAttribute()
    {
        return $this->members()->wherePivot('role', 'owner')->first();
    }

    public function projects()
    {
        return $this->hasMany(Project::class, 'team_id', 'id');
    }
}
