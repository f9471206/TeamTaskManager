<?php

namespace App\Enums;

enum ProjectStatus: int {
    case ACTIVE = 0;
    case ARCHIVED = 1;

    /**
     * 取得中文標籤
     */
    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => '進行中',
            self::ARCHIVED => '已封存',
        };
    }

    /**
     * 取得顏色（Bootstrap Badge 用）
     */
    public function color(): string
    {
        return match ($this) {
            self::ACTIVE => 'success',
            self::ARCHIVED => 'secondary',
        };
    }
}
