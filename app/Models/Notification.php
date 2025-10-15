<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    // 可批量填入的欄位
    protected $fillable = [
        'user_id',
        'type',
        'message',
        'link',
        'read_at',
    ];

    /**
     * 與 User 關聯
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * 是否已讀
     */
    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * 標記為已讀
     */
    public function markAsRead()
    {
        $this->read_at = now();
        $this->save();
    }

}
