<?php

namespace App\Enums;

enum UserTypeEnum: string
{
    const COMPANY = 'company';

    const FREELANCER = 'freelancer';

    const ALL = [
        'company',
        'freelancer',
    ];
}
