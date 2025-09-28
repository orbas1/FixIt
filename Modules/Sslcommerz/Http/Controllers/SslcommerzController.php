<?php

namespace Modules\Sslcommerz\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Sslcommerz\Payment\Sslcommerz;

class SslcommerzController extends Controller
{
    public function webhook(Request $request)
    {
        return Sslcommerz::webhook($request);
    }
}
