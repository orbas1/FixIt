<?php

namespace Modules\Subscription\Enums;

enum RoleEnum: string
{
    const ADMIN = 'admin';

    const CONSUMER = 'user';

    const SERVICEMAN = 'serviceman';

    const PROVIDER = 'provider';
}
