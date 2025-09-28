<?php

namespace Modules\RazorPay\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\RazorPay\Payment\RazorPay;

class RazorPayController extends Controller
{
    public function status(Request $request)
    {
        return RazorPay::status($request);
    }

    public function webhook(Request $request)
    {
        return RazorPay::webhook($request);
    }
}
