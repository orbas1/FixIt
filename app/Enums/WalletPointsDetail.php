<?php

namespace App\Enums;

enum WalletPointsDetail: string
{
    const REDEEM = 'Utilized from booking.';

    const REFUND = 'Amount Returned.';

    const REJECTED = 'Request Not Approved.';

    const ADMIN = 'Admin has changed balance.';

    const WALLET_ORDER = 'Wallet amount successfully debited for Booking';

    const COMMISSION = 'Admin has sent a commission';

    const SERVICEMAN_COMMISSION = 'Provider has sent a commission';

    const ADMIN_CREDIT = 'Admin has credited the balance.';

    const ADMIN_DEBIT = 'Admin has debited the balance.';

    const SUBSCRIPTION = 'Subscription fee charged.';

    const WITHDRAW = 'Balance Withdrawn Requested';

    const TOPUP = 'Balance Credited Successfully';

    const WALLET_ADVERTISEMENT = 'Wallet amount successfully debited for Advertisement';

}
