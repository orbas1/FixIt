<?php

namespace App\Enums;

enum PaymentMethod: string
{
    const COD = 'cash';

    const PAYPAL = 'paypal';

    const STRIPE = 'stripe';

    const MOLLIE = 'mollie';

    const RAZORPAY = 'razorpay';

    const WALLET = 'wallet';
    
    const IN_APP_PURCHASE = 'in_app_purchase';

    const ALL_PAYMENT_METHODS = [
        'cash', 'paypal', 'stripe', 'mollie', 'razorpay',
    ];
}
