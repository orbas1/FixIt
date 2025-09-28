<?php

namespace App\Http\Traits;

use App\Enums\RoleEnum;
use App\Enums\TransactionType;
use App\Models\Booking;
use App\Models\User;

trait TransactionsTrait
{
    public function getAdminRoleId()
    {
        return User::role(RoleEnum::ADMIN)->first()?->id;
    }

    public function debitTransaction($model, $amount, $detail, $order_id = null)
    {
        return $this->storeTransaction($model, TransactionType::DEBIT, $detail, $amount, $order_id);
    }

    public function creditTransaction($model, $amount, $detail, $order_id = null)
    {
        return $this->storeTransaction($model, TransactionType::CREDIT, $detail, $amount, $order_id);
    }

    public function storeTransaction($model, $type, $detail, $amount, $booking_id = null)
    {
        return $model->transactions()->create([
            'amount' => $amount,
            'booking_id' => $booking_id,
            'detail' => $detail,
            'type' => $type,
            'from' => $this->getAdminRoleId(),
        ]);
    }

    public static function updateItemPaymentStatus($booking, $status)
    {
        $booking->update([
            'payment_status' => $status,
        ]);

        Booking::where('parent_id', $booking->id)->update(['payment_status' => $status]);
        $booking = $booking->fresh();

        return $booking;
    }

    public function debitServicemanTransaction($servicemanWallet, $amount, $detail, $order_id = null)
    {
        return $this->storeServicemanTransaction($servicemanWallet, TransactionType::DEBIT, $detail, $amount, $order_id);
    }

    public function creditServicemanTransaction($servicemanWallet, $amount, $detail, $booking_id = null)
    {
        return $this->storeServicemanTransaction($servicemanWallet, TransactionType::CREDIT, $detail, $amount, $booking_id);
    }

    public function debitVendorTransaction($vendorWallet, $amount, $detail, $order_id = null)
    {
        return $this->storeProviderTransaction($vendorWallet, TransactionType::DEBIT, $detail, $amount, $order_id);
    }

    public function creditProviderTransaction($providerWallet, $amount, $detail, $booking_id = null)
    {
        return $this->storeProviderTransaction($providerWallet, TransactionType::CREDIT, $detail, $amount, $booking_id);
    }

    public function storeServicemanTransaction($servicemanWallet, $type, $detail, $amount)
    {
        return $servicemanWallet->transactions()->create([
            'amount' => $amount,
            'serviceman_wallet_id' => $servicemanWallet->id,
            'serviceman_id' => $servicemanWallet->serviceman_id,
            'detail' => $detail,
            'type' => $type,
            'from' => auth()?->user()?->id ?? $this->getAdminRoleId(),
        ]);
    }

    public function storeProviderTransaction($providerWallet, $type, $detail, $amount)
    {
        return $providerWallet->transactions()->create([
            'amount' => $amount,
            'provider_wallet_id' => $providerWallet->id,
            'provider_id' => $providerWallet->provider_id,
            'detail' => $detail,
            'type' => $type,
            'from' => auth()?->user()?->id ?? $this->getAdminRoleId(),
        ]);
    }
}
