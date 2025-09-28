<?php

namespace App\Enums;

enum BookingStatusReq: string
{
    const PENDING = 'pending';

    const ACCEPTED = 'accepted';

    const ASSIGNED = 'assigned';

    const ON_THE_WAY = 'ontheway';

    const ON_GOING = 'onGoing';

    // const DECLINE = 'decline';

    const CANCEL = 'cancel';

    const ON_HOLD = 'on hold';

    const START_AGAIN = 'start again';

    const COMPLETED = 'completed';
}
