<?php

namespace App\Repositories\Backend;

use App\Exceptions\ExceptionHandler;
use App\Models\Subscribe;
use Exception;
use Prettus\Repository\Eloquent\BaseRepository;

class SubscribeRepository extends BaseRepository
{
    public function model()
    {
        return Subscribe::class;  
    }

    public function index($dataTable)
    {
        try{

            return $dataTable->render('backend.subscription.index');

        }catch(Exception $e){
            
            throw new ExceptionHandler($e->getMessage(), $e->getCode());

        }

    }
}