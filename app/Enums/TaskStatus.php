<?php

namespace App\Enums;

enum TaskStatus: int {
    case TODO = 0;
    case IN_PROGRESS = 1;
    case DONE = 2;
    case ARCHIVED = 3;

    public function label(): string
    {
        return match ($this) {
            self::TODO => '待辦',
            self::IN_PROGRESS => '進行中',
            self::DONE => '已完成',
            self::ARCHIVED => '已封存',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::TODO => 'warning',
            self::IN_PROGRESS => 'primary',
            self::DONE => 'success',
            self::ARCHIVED => 'secondary',
        };
    }
}
