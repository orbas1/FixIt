<?php

namespace App\Http\Traits;

use App\Enums\RoleEnum;
use App\Enums\TransactionType;
use App\Exceptions\ExceptionHandler;
use App\Helpers\Helpers;
use App\Models\ProviderWallet;
use App\Models\ServicemanWallet;
use App\Models\Wallet;
use Exception;

trait WalletPointsTrait
{
    use TransactionsTrait;

    public function getWallet($consumer_id)
    {
        if (Helpers::walletIsEnable()) {
            $roleName = Helpers::getRoleNameByUserId($consumer_id);
            if ($roleName == RoleEnum::CONSUMER) {
                return Wallet::firstOrCreate(['consumer_id' => $consumer_id]);
            }

            if ($roleName == RoleEnum::PROVIDER) {
                return ProviderWallet::firstOrCreate(['provider_id' => $consumer_id]);
            }

            throw new ExceptionHandler(__('errors.must_be_consumer',['consumer' => RoleEnum::CONSUMER]), 400);
        }

        throw new ExceptionHandler(__('errors.turn_on_wallet_feature'), 405);
    }

    public function getProviderWallet($provider_id)
    {
        $roleName = Helpers::getRoleNameByUserId($provider_id);
        if ($roleName == RoleEnum::PROVIDER) {
            return ProviderWallet::firstOrCreate(['provider_id' => $provider_id]);
        }
        throw new ExceptionHandler(__('errors.must_be_consumer',['consumer' => RoleEnum::PROVIDER]), 400);
    }

    public function getServicemanWallet($serviceman_id)
    {
        $roleName = Helpers::getRoleNameByUserId($serviceman_id);
        if ($roleName == RoleEnum::SERVICEMAN) {
            return ServicemanWallet::firstOrCreate(['serviceman_id' => $serviceman_id]);
        }
        throw new ExceptionHandler('user must be '.RoleEnum::SERVICEMAN, 400);
    }

    public function getServicemanWalletBalance($serviceman_id)
    {
        return $this->getServicemanWallet($serviceman_id)?->balance;
    }

    public function getProviderWalletBalance($provider_id)
    {
        return $this->getProviderWallet($provider_id)->balance;
    }

    public function verifyWallet($consumer_id, $balance)
    {
        if ($balance > 0.00) {
            $roleName = Helpers::getCurrentRoleName();
            if ($roleName != RoleEnum::PROVIDER) {
                if (Helpers::walletIsEnable()) {
                    $walletBalance = $this->getWalletBalance($consumer_id);
                    if ($walletBalance >= $balance) {
                        return true;
                    }

                    throw new Exception(__('errors.insufficient_wallet_balance'), 400);
                }

                throw new Exception(__('errors.wallet_feature_disabled'), 400);
            }

            throw new Exception(__('errors.provider_wallet_disabled'), 400);
        }
    }

    public function getWalletBalance($consumer_id)
    {
        return $this->getWallet($consumer_id)->balance;
    }

    public function creditWallet($consumer_id, $balance, $detail)
    {
        $wallet = $this->getWallet($consumer_id);
        if ($wallet) {
            $wallet->increment('balance', $balance);
        }

        $this->creditTransaction($wallet, $balance, $detail);

        return $wallet;
    }

    public function debitWallet($consumer_id, $balance, $detail)
    {
        $wallet = $this->getWallet($consumer_id);
        if ($wallet) {
            if ($wallet->balance >= $balance) {
                $wallet->decrement('balance', $balance);
                $this->debitTransaction($wallet, $balance, $detail);

                return $wallet;
            }

            throw new ExceptionHandler(__('errors.insufficient_wallet_balance'), 400);
        }
    }

    public function creditServicemanWallet($serviceman_id, $balance, $detail)
    {
        $servicemanWallet = $this->getServicemanWallet($serviceman_id);
        if ($servicemanWallet) {
            $servicemanWallet->increment('balance', $balance);
        }
        $this->creditServicemanTransaction($servicemanWallet, $balance, $detail);

        return $servicemanWallet;
    }

    public function debitServicemanWallet($serviceman_id, $balance, $detail)
    {
        $servicemanWallet = $this->getServicemanWallet($serviceman_id);
        if ($servicemanWallet) {
            if ($servicemanWallet->balance >= $balance) {
                $servicemanWallet->decrement('balance', $balance);
                $this->debitServicemanTransaction($servicemanWallet, $balance, $detail);

                return $servicemanWallet;
            }

            throw new ExceptionHandler('The Serviceman wallet balance is not sufficient for this booking.', 400);
        }
    }

    public function creditProviderWallet($provider_id, $balance, $detail)
    {
        $providerWallet = $this->getProviderWallet($provider_id);
        if ($providerWallet) {
            $providerWallet->increment('balance', $balance);
        }
        $this->creditProviderTransaction($providerWallet, $balance, $detail);

        return $providerWallet;
    }

    public function debitProviderWallet($provider_id, $balance, $detail)
    {
        $providerWallet = $this->getProviderWallet($provider_id);
        if ($providerWallet) {
            if ($providerWallet->balance >= $balance) {
                $providerWallet->decrement('balance', $balance);
                $this->debitProviderTransaction($providerWallet, $balance, $detail);

                return $providerWallet;
            }

            throw new ExceptionHandler(__('errors.provider_wallet_balance_insufficient'), 400);
        }
    }

    public function debitProviderTransaction($providerWallet, $amount, $detail, $booking_id = null)
    {
        return $this->storeProviderTransaction($providerWallet, TransactionType::DEBIT, $detail, $amount, $booking_id);
    }
}
