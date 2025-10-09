<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Invitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'user_id',
        'status',
        'token',
        'expires_at',
    ];

    protected $dates = [
        'expires_at',
    ];

    protected static function booted()
    {
        static::creating(function ($invitation) {
            // 如果沒有傳入 token，就自動生成 UUID
            if (empty($invitation->token)) {
                $invitation->token = Str::uuid()->toString();
            }
        });
    }

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
