<?php

namespace Modules\Subscription\Enums;

enum PaymentAllowed: string
{
    case PAYPAL = 'paypal';
    case STRIPE = 'stripe';
    case RAZORPAY = 'razorpay';
}
