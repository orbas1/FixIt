<?php

namespace App\Enums;

enum EscrowStatusEnum: string
{
    const DRAFT = 'draft';
    const AWAITING_FUNDING = 'awaiting_funding';
    const FUNDED = 'funded';
    const PARTIALLY_RELEASED = 'partially_released';
    const RELEASED = 'released';
    const REFUNDED = 'refunded';
    const CANCELLED = 'cancelled';
    const REQUIRES_ACTION = 'requires_action';

    const TERMINAL_STATES = [
        self::RELEASED,
        self::REFUNDED,
        self::CANCELLED,
    ];
}
