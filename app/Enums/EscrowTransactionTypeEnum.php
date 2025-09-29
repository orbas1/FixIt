<?php

namespace App\Enums;

enum EscrowTransactionTypeEnum: string
{
    const HOLD = 'hold';
    const RELEASE = 'release';
    const REFUND = 'refund';
    const ADJUSTMENT = 'adjustment';
    const FEE = 'fee';
}
