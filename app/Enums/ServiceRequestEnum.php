<?php

namespace App\Enums;

enum ServiceRequestEnum: string
{
    const OPEN = 'open';
    
    const PENDING = 'pending';

    const CLOSED = 'closed';
}
