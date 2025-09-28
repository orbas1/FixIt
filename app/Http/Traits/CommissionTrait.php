<?php

namespace App\Http\Traits;

use App\Enums\PaymentStatus;
use App\Enums\RoleEnum;
use App\Enums\WalletPointsDetail;
use App\Helpers\Helpers;
use App\Models\booking;
use App\Models\CommissionHistory;

trait CommissionTrait
{
    use WalletPointsTrait;

    public function isExistsCommissionHistory(booking $booking)
    {
        return CommissionHistory::where('booking_id', $booking->id)->exists();
    }

    public function getMonthlyProviderCommissions($monthlyCommssions)
    {
        return $monthlyCommssions->where('provider_id', Helpers::getCurrentProviderId())->pluck('provider_commission')->toArray();
    }

    public function getMonthlyAdminCommissions($monthlyCommssions)
    {
        return $monthlyCommssions->pluck('admin_commission')->toArray();
    }

    public function getMonthlyCommissions($year, $roleName)
    {
        $months = range(1, 12);
        foreach ($months as $month) {
            $perMonthCommissions = [];
            $commissionHistory = CommissionHistory::whereMonth('created_at', $month)->whereYear('created_at', $year)->whereNull('deleted_at');
            if ($roleName == RoleEnum::PROVIDER) {
                $perMonthCommissions = $this->getMonthlyProviderCommissions($commissionHistory);
            } else {
                $perMonthCommissions = $this->getMonthlyAdminCommissions($commissionHistory);
            }

            $commissions[] = array_sum($perMonthCommissions);
        }

        return $commissions;
    }

    public function adminVendorCommission($booking)
    {
        $settings = Helpers::getSettings();
        // provider_commissions => this key for admin commissions
        if ($settings['provider_commissions']['status']) {
            if (($booking->payment_status == PaymentStatus::COMPLETED)) {
                if ($booking->sub_bookings->isEmpty()) {
                    $booking->sub_bookings = [$booking];
                }

                foreach ($booking->sub_bookings as $sub_booking) {
                    $commissions = [];
                    $category_id = null;
                    
                    if ($sub_booking) {
                        $extraChargeTotal = $sub_booking->extra_charges->sum('total') ?? 0;
                        $subTotal = $sub_booking->subtotal + $extraChargeTotal;
                        // $subTotal = $sub_booking?->subtotal;

                        $paymentMethod = $sub_booking?->payment_method;
                        
                        if ($settings['provider_commissions']['is_category_based_commission']) {
                            $commissionRate = (float) max(($sub_booking?->service->categories->pluck('commission')->toArray()));
                            $category_id = $sub_booking->service->categories()->orderBy('commission', 'desc')->first()?->id;

                            if (!$commissionRate) {
                                $commissionRate = $settings['provider_commissions']['default_commission_rate'];
                            }

                        } else {
                            $commissionRate = (float) $settings['provider_commissions']['default_commission_rate'];
                        }

                        $providerId = Helpers::getProviderIdByServiceId($sub_booking->service->id);

                        // Calculate commissions
                        $commissions['admin'][] = $this->getAdminCommission($subTotal, $commissionRate);
                        $providerCommission = $this->getProviderCommission($subTotal, $commissionRate);
                        $commissions['serviceman'] = $this->getServicemanCommission($sub_booking, $providerCommission);
                        $commissions['provider'][] = $providerCommission; 

                        if($paymentMethod == 'cash') {
                            if (!$this->isExistsCommissionHistory($sub_booking)) {
                                $this->debitProviderWallet($providerId, $this->getAdminCommission($subTotal, $commissionRate), "Admin has debited commission");
                                if($sub_booking->tax > 0){
                                    $this->debitProviderWallet($providerId, $sub_booking->tax, "Admin has debited tax amount");
                                }
                                if($sub_booking->platform_fees > 0){
                                    $this->debitProviderWallet($providerId, $sub_booking->platform_fees, "Admin debited has platform fee");
                                }

                                 // Extra Charges Commission
                                foreach ($sub_booking->extra_charges as $extra) {
                                    $extraTitle = $extra->title;

                                    // Tax for extra
                                    if ($extra->tax_amount > 0) {
                                        $this->debitProviderWallet($providerId, $extra->tax_amount, "Admin tax for extra charge: $extraTitle");
                                    }
                                }

                                $this->createCommissionHistory($sub_booking, $providerId, $commissions, $category_id);
                            }
                        } else {
                            if (!$this->isExistsCommissionHistory($sub_booking)) {
                                $providerCommission = array_sum($commissions['provider']);
                                $this->creditProviderWallet($providerId, $providerCommission, WalletPointsDetail::COMMISSION);
                                foreach ($sub_booking->servicemen as $serviceman) {
                                    $roleName = Helpers::getRoleNameByUserId($serviceman->id);
                                    if ($roleName == RoleEnum::SERVICEMAN) {
                                        $this->creditServicemanWallet($serviceman->id, $commissions['serviceman'], WalletPointsDetail::SERVICEMAN_COMMISSION);
                                        $this->debitProviderWallet($providerId, $commissions['serviceman'], "Sent commission to {$serviceman->name}");
                                    }
                                } 
                                $this->createCommissionHistory($sub_booking, $providerId, $commissions, $category_id);
                            }
                        }

                    }

                }
            }
        }
    }


    public function debitProviderWallet($provider_id, $balance, $detail)
    {
        $providerWallet = $this->getProviderWallet($provider_id);
        if ($providerWallet) {
            $providerWallet->decrement('balance', $balance);
            $this->debitProviderTransaction($providerWallet, $balance, $detail);

            return $providerWallet;
        }
    }

    public function getProviderCommission($subTotal, $commissionRate)
    {
        // Calculate the admin commission first
        $adminCommission = $this->getAdminCommission($subTotal, $commissionRate);

        // Calculate the provider commission after deducting the admin commission
        $providerCommissionBeforeServicemen = $subTotal - $adminCommission;

        // Return the provider commission before the servicemen deduction
        return $providerCommissionBeforeServicemen;
    }

    public function getServicemanCommission($sub_booking, $providerCommission)
    {
        // Calculate serviceman commission based on provider's earnings
        $servicemanCommissionRate = $sub_booking->service->per_serviceman_commission;
        $totalServicemen = $sub_booking->servicemen()->count();
        
        // Calculate total serviceman commission
        $totalServicemanCommission = ($providerCommission * $servicemanCommissionRate) / 100;

        // Adjust the provider's commission after servicemen deduction
        $providerCommission = $totalServicemanCommission / $totalServicemen;

        return $providerCommission;
    }

    public function getAdminCommission($subTotal, $commissionRate)
    {
        return ($subTotal * $commissionRate) / 100;
    }

    public function createCommissionHistory($sub_booking, $provider_id, $commissions, $category_id)
    {
        $commissionHistory = $sub_booking->commission_history()->create([
            'admin_commission' => array_sum($commissions['admin']),
            'provider_commission' => array_sum($commissions['provider']),
            'provider_id' => $provider_id,
            'category_id' => $category_id,
        ]);
        // Store servicemen's commissions
        foreach ($sub_booking->servicemen as $serviceman) {
            $commissionHistory->serviceman_commissions()->create([
                'serviceman_id' => $serviceman->id,
                'commission' => $commissions['serviceman'],
            ]);
        }

        return $sub_booking;
    }
}
