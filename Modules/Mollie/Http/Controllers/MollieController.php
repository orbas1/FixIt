<?php

namespace Modules\Mollie\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Mollie\Payment\Mollie;

class MollieController extends Controller
{
    public function status(Request $request)
    {
        return Mollie::status($request);
    }

    public function webhook(Request $request)
    {
        return Mollie::webhook($request);
    }
}
