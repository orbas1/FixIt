<?php

namespace Modules\Stripe\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Stripe\Payment\Stripe;

class StripeController extends Controller
{
    public function webhook(Request $request)
    {
        return Stripe::webhook($request);
    }
}
