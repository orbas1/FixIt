<?php

namespace App\Enums;

enum BookingEnum: string
{
    const PENDING = 'Pending';

    const ACCEPTED = 'Accepted';

    const ASSIGNED = 'Assigned';

    const ON_THE_WAY = 'On The Way';

    const ON_GOING = 'On Going';

    // const DECLINE = 'Decline';

    const CANCEL = 'Cancel';

    const ON_HOLD = 'On Hold';

    const START_AGAIN = 'Start Again';

    const COMPLETED = 'Completed';

    // const PENDING_APPROVAL = 'Pending Approval';
}
