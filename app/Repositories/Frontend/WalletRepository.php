<?php

namespace App\Repositories\Frontend;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Wallet;
use Prettus\Repository\Eloquent\BaseRepository;

class WalletRepository extends BaseRepository
{
    
    public function model()
    {
        return Wallet::class;   
    }

    public function index($dataTable)
    {
        return $dataTable->render('frontend.account.wallet');
    }
}