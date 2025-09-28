<?php

namespace App\Http\Traits;

use App\Enums\AmountEnum;
use App\Exceptions\ExceptionHandler;
use App\Helpers\Helpers;
use App\Models\Service;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

trait CheckoutTrait
{
    use CouponTrait, UtilityTrait, WalletPointsTrait;

    public function calculate(Request $request)
    {
        try {

            $settings = Helpers::getSettings();
            $minBookingAmount = $settings['general']['min_booking_amount'];
            $services = $request->services ?? [];
            $service_package = $request->service_packages;
            $request->merge(['services' => $services, 'service_package' => $service_package]);
            $amount = Helpers::getTotalAmount($request->services, $request->service_package);
            if ($amount < $minBookingAmount) {
                throw new Exception(__('errors.minimum_booking_amount',['minBookingAmount' => $minBookingAmount]), 422);
            }

            return $this->getCosts($request);

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function getCosts($request)
    {
        return $this->calculateCosts($request);
    }

    public function calculateCosts($request)
    {
        try {
            $tax = [];
            $walletBalance = 0;
            $perServiceCost = [];
            $couponTotalDiscount = [];
            $convert_wallet_balance = 0;
            $settings = Helpers::getSettings();
            $amount = Helpers::getTotalAmount($request->services, $request->service_package);
            $items = $this->calculateService($request, $request->services, $amount);

            if ($request->service_packages) {
                $items['services_package'] = $this->calculateServicePackage($request->service_packages, $request);
            }

            $total = $amount;
            $couponDiscount = array_sum($couponTotalDiscount);

            if ($request->wallet_balance) {
                if ($this->verifyWallet($this->getConsumerId($request), $walletBalance)) {
                    $convert_wallet_balance = abs($walletBalance);
                    $walletBalance -= $convert_wallet_balance;
                    $total -= $convert_wallet_balance;
                    if ($total < 0) {
                        $walletBalance = abs($total);
                        $total = 0;
                    }

                    if ($walletBalance > 0) {
                        $convert_wallet_balance -= $walletBalance;
                    }

                    $convert_wallet_balance = -$convert_wallet_balance;
                }
            }

            if ($couponDiscount > 0) {
                $total -= $couponDiscount;
                if ($total < 0) {
                    $couponDiscount = abs($total);
                    $total = 0;
                }
            }

            $total += array_sum($tax);
            $platform_fees = round($this->getTotalItems($items, 'platform_fees'));
            $itemTotal = [
                'required_servicemen' => $this->getTotalItems($items, 'required_servicemen'),
                'total_extra_servicemen' => $this->getTotalItems($items, 'total_extra_servicemen'),
                'total_servicemen' => $this->getTotalItems($items, 'total_servicemen'),
                'total_serviceman_charge' => $this->getTotalItems($items, 'total_serviceman_charge'),
                'coupon_total_discount' => $this->getTotalItems($items, 'coupon_total_discount'),
                'tax' => $this->getTotalItems($items, 'tax'),
                'platform_fees' => $platform_fees,
                'platform_fees_type' => $settings['general']['platform_fees_type'],
                'subtotal' => $this->getTotalItems($items, 'subtotal'),
                'total' => round($this->getTotalItems($items, 'total'), 3),
            ];

            $items['total'] = $itemTotal;

            return $items;

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }

    public function getTotalItems($items, $key)
    {
        $total = [];
        foreach ($items['services'] as $service) {

            $total[] = $service['total'][$key];
        }

        if (isset($items['services_package'])) {
            foreach ($items['services_package'] as $servicePackage) {
                foreach ($servicePackage['services'] as $service) {
                    $total[] = $service['total'][$key];
                }
            }
        }

        return array_sum($total);
    }

    public function getTax($service_id, $subtotal, $platform_fees)
    {
        $totalTax = 0;
        $taxIds = $this->getTaxIds($service_id);

        if (!empty($taxIds)) {
            $taxRates = $this->getTaxRates($taxIds);
            $taxableAmount = $subtotal + $platform_fees;
            foreach ($taxRates as $taxRate) {
                $totalTax += ($taxableAmount * $taxRate) / 100;
            }
        }

        return $totalTax;
    }

    public function getTaxes($serviceId, $subtotal = 0, $platform_fees = 0)
    {
        $taxesData = [];
        $taxableAmount = $subtotal + $platform_fees;
        $service = Service::with('taxes')->find($serviceId);
        if ($service && $service->taxes) {
        foreach ($service->taxes as $tax) {
                $taxesData[] = [
                    'id' => $tax->id,
                    'name' => $tax->name,
                    'rate' => $tax->rate,
                    'amount' => round(($taxableAmount * $tax->rate) / 100, 2),
                ];
            }
        }

        return $taxesData;
    }


    public function calculateServicePackage($service_packages, $request)
    {
        $settings = Helpers::getSettings();
        foreach ($service_packages as $key => $service_package) {
            $services = $service_package['services'];
            $servicePackage = $service_package;
            $platFormFees = $this->getPlatFormFees($services);
            foreach ($services as $service) {

                $couponTotalDiscount = [];
                $perServiceTax = 0;
                $perServicemanCharge = 0;
                $perServiceDiscount = 0;

                $singleServicePrice = Helpers::getSalePrice($service);
                $requiredServiceman = Helpers::getTotalRequireServicemenByServiceId($service['service_id']);

                $perServicemanCharge = ($requiredServiceman != 0) ? ($singleServicePrice / $requiredServiceman) : 0;
                $subTotal = Helpers::getSubTotal($perServicemanCharge) * $service['required_servicemen'];
                $totalExtraServicemanCharge = 0;

                if ($service_package) {
                    $extraServiemen = 1;
                    $singleServicePackagePrice = (Helpers::getPackageSalePrice($service_package) / count($services));
                    $extraServiemenPerService = $service['required_servicemen'] - $requiredServiceman;
                    $total_servicemen = $extraServiemenPerService ?? 0 + $requiredServiceman ?? 0;

                    $totalExtraServicemanCharge = $extraServiemenPerService * $singleServicePrice;
                    $perServicemanCharge = ($requiredServiceman != 0) ? ($singleServicePrice / $requiredServiceman) : 0;
                    $subTotal = $singleServicePackagePrice + $totalExtraServicemanCharge;
                    $service['required_servicemen'] = $extraServiemenPerService;
                }

                if (isset($request->coupon)) {
                    $coupon = Helpers::getCoupon($request->coupon);
                    if ($this->isValidCoupon($coupon, $subTotal, $this->getConsumerId($request))) {
                        if ($this->isValidForZone($request->zone_id,$coupon)) {
                            // if($this->isValidForUser(Helpers::getCurrentUserId(),$coupon)){
                                if ($this->isIncludeOrExclude($coupon, $service)) {
                                    switch ($coupon->type) {
                                        case AmountEnum::FIXED:
                                            $perServiceDiscount = $this->fixedDiscount($subTotal, $coupon->amount);
                                            break;

                                        case AmountEnum::PERCENTAGE:
                                            $perServiceDiscount = $this->percentageDiscount($subTotal, $coupon->amount);
                                            break;

                                        default:
                                            $perProductShippingCost = 0;
                                            $shippingTotal = 0;
                                    }

                                    $couponTotalDiscount[] = $perServiceDiscount;
                                    $subTotal = $subTotal - $perServiceDiscount;
                                }
                            // }
                        }
                    }
                }

                $perServiceTax = $this->getTax($service['service_id'], $subTotal, $platFormFees);
                $taxes = $this->getTaxes($service['service_id'], $subTotal, $platFormFees);
                $_item[$key]['service_package_id'] = $service_package['service_package_id'];
                $_item[$key]['services'][] = [
                    'service_package_id' => $service_package['service_package_id'],
                    'provider_id' => Helpers::getProviderIdByServiceId($service['service_id']),
                    'service_id' => $service['service_id'],
                    'type' => $service['type'],
                    'taxes' => $taxes,
                    'service_package_price' => Helpers::getPackageSalePrice($service_package),
                    'address_id' => $service['address_id'],
                    'tax' => $perServiceTax,
                    'service_price' => $singleServicePackagePrice,
                    'per_serviceman_charge' => $singleServicePackagePrice,
                    'date_time' => $service['date_time'],
                    'servicemen_ids' => $service['serviceman_id'] ?? [],
                    'total' => [
                        'required_servicemen' => $requiredServiceman,
                        'total_extra_servicemen' => $extraServiemenPerService,
                        'total_servicemen' => $total_servicemen,
                        'total_serviceman_charge' => $totalExtraServicemanCharge,
                        'coupon_total_discount' => array_sum($couponTotalDiscount),
                        'platform_fees' => $platFormFees,
                        'platform_fees_type' => $settings['general']['platform_fees_type'],
                        'tax' => $perServiceTax,
                        'subtotal' => $subTotal,
                        'total' => $subTotal + $perServiceTax + $platFormFees,
                    ],
                ];
            }
        }
        return $_item;
    }

    public function getPlatFormFees($services)
    {
        $settings = Helpers::getSettings();
        $platFormFees = 0;
        if (count($services)) {
            if (isset($settings['activation']['platform_fees_status'])) {
                if ($settings['activation']['platform_fees_status']) {
                    $platFormFees = $settings['general']['platform_fees'];
                    if ($settings['general']['platform_fees_type'] === 'fixed') {
                        $platFormFees = round($platFormFees / count($services),2);
                    }
                }
            }
        }

        return (double) $platFormFees;
    }

    public function calculateService($request, $services, $amount, $servicePackage = null)
    {
        $couponTotalDiscount = [];
        $_item = ['services' => []];
        $settings = Helpers::getSettings();
        $platFormFees = $this->getPlatFormFees($services);
        foreach ($services as $service) {
            $perServicemanCharge = 0;
            $perServiceDiscount = 0;
            $perServiceTax = 0;
            $getOriginalServicePrice = Helpers::getOriginalServicePrice($service);
            $singleServicePrice = Helpers::getSalePrice($service);
            $requiredServiceman = Helpers::getTotalRequireServicemenByServiceId($service['service_id']);
            $total_extra_servicemen = $service['required_servicemen'] - $requiredServiceman;
            $total_servicemen = $requiredServiceman ?? 0 + $total_extra_servicemen ?? 0;
            $perServicemanCharge = ($requiredServiceman != 0) ? ($singleServicePrice / $requiredServiceman) : 0;
            $platformfees = 0;

            $subTotal = Helpers::getSubTotal($perServicemanCharge) * $service['required_servicemen'];
            if (isset($request->coupon)) {
                $coupon = Helpers::getCoupon($request->coupon);
                if ($this->isValidCoupon($coupon, $amount, $this->getConsumerId($request))) {
                    if ($this->isIncludeOrExclude($coupon, $service)) {
                        switch ($coupon->type) {
                            case AmountEnum::FIXED:
                                $perServiceDiscount = $this->fixedDiscount($subTotal, $coupon->amount);
                                break;

                            case AmountEnum::PERCENTAGE:
                                $perServiceDiscount = $this->percentageDiscount($subTotal, $coupon->amount);
                                break;

                            default:
                                $perProductShippingCost = 0;
                                $shippingTotal = 0;
                        }
                        $couponTotalDiscount[] = $perServiceDiscount;
                        $subTotal = $subTotal - $perServiceDiscount;
                    }
                }
            }

            $additionalServiceTotalPrice = 0;
            $additionalServicesDetails = [];

            if (isset($service['additional_services']) && !empty($service['additional_services'])) {
                    if (Helpers::additionalServicesIsEnable() !== "1") {
                        throw new ExceptionHandler(__('errors.turn_on_additional_services'), Response::HTTP_METHOD_NOT_ALLOWED);
                    }
                    foreach ($service['additional_services'] as $additionalService) {
                        $additionalServiceId = $additionalService['id'];
                        $qty = $additionalService['qty'] ?? 1;
                        // Get additional service price and details
                        $additionalServicePrice = Helpers::getAdditionalServicePrice($additionalServiceId);
                        $additionalServicesDetails[] = [
                            'id' => $additionalServiceId,
                            'price' => $additionalServicePrice,
                            'qty' => $qty,
                            'total_price' => $additionalServicePrice * $qty,
                        ];
                        $additionalServiceTotalPrice += ($additionalServicePrice * $qty);
                    }
                    $subTotal += $additionalServiceTotalPrice;
            }

            $perServiceTax = $this->getTax($service['service_id'], $subTotal, $platFormFees);
            $taxes = $this->getTaxes($service['service_id'], $subTotal, $platFormFees);
            $tax[] = $perServiceTax;

            $_item['services'][] = [
                'provider_id' => Helpers::getProviderIdByServiceId($service['service_id']),
                'service_id' => $service['service_id'],
                'type' => $service['type'],
                'taxes' => $taxes,
                'service_price' => $singleServicePrice,
                'price' => $getOriginalServicePrice,
                'address_id' => $service['address_id'],
                'per_serviceman_charge' => $perServicemanCharge,
                'date_time' => $service['date_time'],
                'servicemen_ids' => $service['serviceman_id'],
                'additional_services' => $additionalServicesDetails,
                'total' => [
                    'required_servicemen' => $requiredServiceman,
                    'total_extra_servicemen' => $total_extra_servicemen,
                    'total_servicemen' => $total_servicemen,
                    'total_serviceman_charge' => $perServicemanCharge * $service['required_servicemen'],
                    'coupon_total_discount' => array_sum($couponTotalDiscount),
                    'platform_fees' => $platFormFees,
                    'platform_fees_type' => $settings['general']['platform_fees_type'],
                    'subtotal' => $subTotal,
                    'tax' => $perServiceTax,
                    'total' => $subTotal + $perServiceTax + $platFormFees,
                ],
            ];
        }

        return $_item;
    }


    // Frontend Cart Payload
    public function generateCheckoutPayload($cartItems, $request)
    {
        $payload = [];
        $payload['consumer_id'] = $request?->consumer_id ?? Helpers::getCurrentUserId();
        $payload['payment_method'] = $request?->payment_method;
        $payload['coupon'] = $request?->coupon;
        $payload['request_type'] = 'web';

        foreach($cartItems as $item) {
            $servicemanIds = [];

            if (isset($item['service_packages'])) {
                if (count($item['service_packages']['services'] ?? [])) {
                    foreach($item['service_packages']['services'] as $index => $service) {
                        if (isset($service['serviceman_id']) && $service['select_serviceman'] != 'app_choose') {
                            $servicemanIds = explode(',', $service['serviceman_id']);
                        }

                        $service['service_packages'] = $servicemanIds;
                        $service['type'] = $this->getServiceTypeById($service['service_id']);
                        $item['service_packages']['services'][$index] = $service;
                    }
                }

                $payload['service_packages'][] = $item['service_packages'];

            } elseif (isset($item['service_id'])) {
                if (isset($item['serviceman_id']) && $item['select_serviceman'] != 'app_choose') {
                    $servicemanIds = explode(',', $item['serviceman_id']);
                }

                $payload['services'][] = [
                    'service_id' => $item['service_id'],
                    'required_servicemen' => $item['required_servicemen'] ?? Helpers::getServiceById( $item['service_id'])?->required_servicemen,
                    'address_id' => $item['address_id'],
                    'description' => $item['description'],
                    'type' => $this->getServiceTypeById($item['service_id']),
                    'select_serviceman' => $item['select_serviceman'] ?? Helpers::getServiceById( $item['service_id'])?->required_servicemen,
                    'serviceman_id' => $servicemanIds,
                    'select_date_time' => $item['select_date_time'],
                    'date_time' => $item['date_time'],
                    'additional_services' => $item['additional_services'] ?? []
                ];
            }
        }

        if (session('coupon')) {
            $payload['coupon'] = session('coupon');
        }

        $payload = $request->merge($payload);


        return $payload;
    }

    public function getServiceTypeById($service_id)
    {
        return Service::where('id', $service_id)?->whereNUll('deleted_at')?->value('type');
    }
}
