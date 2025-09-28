
@use('app\Helpers\Helpers')
<ul class="payment-list">
    <?php
        $total = 0.0;
        $subTotal = 0.0;
        $totalTax = 0.0;

    ?>

    @forelse ($cartItems as $cartItem)
        <?php
            $taxAmount = 0.0;

            foreach ($cartItem->service->taxes as $tax) {
                $taxAmount += ($cartItem->service->service_rate * $tax->rate) / 100;
            }

            $subTotal += $cartItem->service->service_rate;
            $totalTax += $taxAmount;
            $total += $cartItem->service->service_rate + $taxAmount;
        ?>
    @empty
    @endforelse

    <li>Subtotal
        <span id="subtotal-value">{{ Helpers::getDefaultCurrency()->symbol }}{{ Helpers::covertDefaultExchangeRate($subTotal) }}</span>
    </li>
    <li>Discount
        <span id="discount-value">0</span>
    </li>
    <li>Total Tax
        <span id="tax-value">{{ Helpers::getDefaultCurrency()->symbol }}{{ Helpers::covertDefaultExchangeRate($totalTax) }}</span>
    </li>
    <li>Total
        <span id="total-value">{{ Helpers::getDefaultCurrency()->symbol }}{{ Helpers::covertDefaultExchangeRate($total) }}</span>
    </li>
</ul>
