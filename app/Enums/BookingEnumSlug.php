<?php

namespace App\Enums;

enum BookingEnumSlug: string
{
    const PENDING = 'pending';

    const ACCEPTED = 'accepted';

    const ASSIGNED = 'assigned';

    const ON_THE_WAY = 'on-the-way';

    const ON_GOING = 'on-going';

    // const DECLINE = 'decline';

    const CANCEL = 'cancel';

    const ON_HOLD = 'on-hold';

    const START_AGAIN = 'start-again';

    const COMPLETED = 'completed';

    // const PENDING_APPROVAL = 'Pending Approval';
}
