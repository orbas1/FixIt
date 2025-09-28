<?php

namespace Modules\Paystack\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Paystack\Payment\Paystack;

class PaystackController extends Controller
{
    public function webhook(Request $request)
    {
        return Paystack::webhook($request);
    }
}
