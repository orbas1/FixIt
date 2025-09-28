<?php

namespace App\Http\Controllers\API;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Models\Zone;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $filteredPaymentMethods = Helpers::getPaymentMethodList();
        if ($request->zone_id) {
            $zone = Zone::where('id', $request?->zone_id)?->first();
            $allowedSlugs = $zone?->payment_methods ?? [];
            $allPaymentMethods = Helpers::getPaymentMethodList();

            $filteredPaymentMethods = collect($allPaymentMethods)->filter(function ($method) use ($allowedSlugs) {
                if (!$method['status']) {
                    return false;
                }

                if ($method['slug'] === 'cash') {
                    return true;
                }

                if (!in_array($method['slug'], $allowedSlugs)) {
                    return false;
                }
                return true;
            })->values();
        }  else {
            $filteredPaymentMethods = collect($filteredPaymentMethods)->filter(function ($method) {
                return $method['status'];
            })->values();
        }

        return response()->json([
            'success' => true,
            'data' => $filteredPaymentMethods
        ]);
    }
}
