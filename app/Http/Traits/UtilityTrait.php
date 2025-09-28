<?php

namespace App\Http\Traits;

use App\Enums\PaymentMethod;
use App\Enums\PaypalCurrencies;
use App\Helpers\Helpers;
use App\Models\Service;
use App\Models\Tax;
use Exception;

trait UtilityTrait
{
    public function getUniqueProducts($products)
    {
        return collect($products)->unique(function ($product) {
            return $product['product_id'].'-'.$product['variation_id'];
        })->values()->toArray();
    }

    public function isActivePaymentMethod($method)
    {
        if ($method == PaymentMethod::PAYPAL) {
            $defaultCurrencyCode = Helpers::getDefaultCurrencyCode();
            if (! in_array($defaultCurrencyCode, array_column(PaypalCurrencies::cases(), 'value'))) {
                throw new Exception($defaultCurrencyCode.' currency code is not support for PayPal.', 400);
            }
        }

        $settings = Helpers::getSettings();
        if ($settings['payment_methods'][$method]) {
            return true;
        }

        throw new Exception('The provided payment method is not currently active.', 400);
    }

    public function formatDecimal($value)
    {
        return Helpers::formatDecimal($value);
    }

    public function getConsumerId($request)
    {
        return $request->consumer_id ?? Helpers::getCurrentUserId();
    }

    public function getTaxIds($product_id)
    {
        $service = Service::where('id', $product_id)->first();
        return $service?->taxes?->pluck('id')->toArray();
    }

    public function getTaxRates(array $tax_ids)
    {
        return Tax::whereIn('id', $tax_ids)
                  ->where('status', true)
                  ->pluck('rate')
                  ->toArray();
    }


    public function isOutOfStock($products)
    {
        $outOfStockProducts = [];
        foreach ($products as $product) {
            if (isset($product['variation_id'])) {
                $variationStock = Helpers::getVariationStock($product['variation_id']);
                if (! isset($variationStock)) {
                    $outOfStockProducts[] = [
                        'product_id' => $product['product_id'],
                        'variation_id' => $product['variation_id'],
                    ];
                }
            } else {
                $productStock = Helpers::getProductStock($product['product_id']);
                if (! isset($productStock)) {
                    $outOfStockProducts[] = [
                        'product_id' => $product['product_id'],
                    ];
                }
            }
        }

        if (! empty($outOfStockProducts)) {
            throw new Exception("Some of the products you've selected are either out of stock or inactive.", 400);
        }

        return false;
    }
}
