<?php

namespace Modules\Ccavenue\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Ccavenue\Payment\Ccavenue;

class CcavenueController extends Controller
{
    public function webhook(Request $request)
    {
        return Ccavenue::webhook($request);
    }
}
