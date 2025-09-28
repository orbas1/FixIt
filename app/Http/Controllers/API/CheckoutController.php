<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\CalculateCheckoutRequest;
use App\Http\Traits\CheckoutTrait;

class CheckoutController extends Controller
{
    use CheckoutTrait;

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function verifyCheckout(CalculateCheckoutRequest $request)
    {
        return $this->calculate($request);
    }
}
