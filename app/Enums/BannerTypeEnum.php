<?php

namespace App\Enums;

enum BannerTypeEnum: string
{
    const BANNERTYPE = [
        'category' => 'category',
        'service' => 'service',
        'provider' => 'provider',
    ];

    const SERVICE = 'service';
    const CATEGORY = 'category';
    const PROVIDER = 'provider';
}
