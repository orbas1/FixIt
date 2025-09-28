<?php

namespace App\Enums;

enum ServiceTypeEnum: string
{
    const FREE = 'free';
    const FIXED = 'fixed';
    const USER_SITE = 'user_site';
    const PROVIDER_SITE = 'provider_site';
    const REMOTELY = 'remotely';
}
