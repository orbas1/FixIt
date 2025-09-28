<?php

namespace Modules\PhonePe\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\PhonePe\Payment\PhonePe;

class PhonePeController extends Controller
{
    public function webhook(Request $request)
    {
        return PhonePe::webhook($request);
    }
}
