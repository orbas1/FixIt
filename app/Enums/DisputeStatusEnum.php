<?php

namespace App\Enums;

enum DisputeStatusEnum: string
{
    const OPEN = 'open';
    const NEEDS_ACTION_CONSUMER = 'needs_action_consumer';
    const NEEDS_ACTION_PROVIDER = 'needs_action_provider';
    const UNDER_REVIEW = 'under_review';
    const RESOLVED = 'resolved';
    const CLOSED = 'closed';
    const CANCELLED = 'cancelled';
}
