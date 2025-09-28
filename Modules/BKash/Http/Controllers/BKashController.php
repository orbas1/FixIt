<?php

namespace Modules\BKash\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\BKash\Payment\BKash;

class BKashController extends Controller
{
    public function webhook(Request $request)
    {
        return BKash::webhook($request->token);
    }
}
