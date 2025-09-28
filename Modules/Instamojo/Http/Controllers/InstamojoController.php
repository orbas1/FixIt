<?php

namespace Modules\Instamojo\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Instamojo\Payment\Instamojo;

class InstamojoController extends Controller
{
    public function status(Request $request)
    {
        return Instamojo::status($request);
    }

    public function webhook(Request $request)
    {
        return Instamojo::webhook($request);
    }
}
