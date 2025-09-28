<?php

namespace App\Enums;

enum FrontEnum: string
{
    const PLACE_HOLDER_IMG = 'frontend/images/img-not-found.jpg';

    public static function getPlaceholderImageUrl(): string
    {
        return asset(self::PLACE_HOLDER_IMG);
    }
}
