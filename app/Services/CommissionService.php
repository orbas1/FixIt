<?php

namespace App\Services;

use App\Http\Traits\CommissionTrait;
use App\Models\Booking;

class CommissionService
{
    use CommissionTrait;

    public function handleCommission(Booking $booking)
    {
        $this->adminVendorCommission($booking);
    }
} 