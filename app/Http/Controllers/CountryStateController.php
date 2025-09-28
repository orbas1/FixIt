<?php

namespace App\Http\Controllers;

use App\Helpers\Helpers;
use App\Models\Country;
use Illuminate\Http\Request;

class CountryStateController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function getStates(Request $request)
    {
        $states['states'] = Helpers::getStatesByCountryId($request->country_id);

        return response()->json($states);
    }

    public function getCountryCode(Request $request)
    {
        $codes['codes'] = Country::get(['phone_code', 'id', 'iso_3166_2']);

        return response()->json($codes);
    }
}
