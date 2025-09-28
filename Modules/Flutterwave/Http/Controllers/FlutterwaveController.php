<?php

namespace Modules\Flutterwave\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Flutterwave\Payment\Flutterwave;

class FlutterwaveController extends Controller
{
    public function webhook(Request $request)
    {
        return Flutterwave::webhook($request);
    }
}
