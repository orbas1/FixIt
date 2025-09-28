<?php

namespace App\Enums;

enum SymbolPositionEnum: string
{
    case LEFT = 'left';
    case RIGHT = 'right';

    public function label(): string
    {
        return match ($this) {
            self::LEFT => 'Left',
            self::RIGHT => 'Right',
        };
    }
}