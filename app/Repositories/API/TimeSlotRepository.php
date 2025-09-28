<?php

namespace App\Repositories\API;

use App\Exceptions\ExceptionHandler;
use App\Http\Traits\CommissionTrait;
use App\Models\TimeSlot;
use Exception;
use Prettus\Repository\Eloquent\BaseRepository;

class TimeSlotRepository extends BaseRepository
{
    use CommissionTrait;

    public function model()
    {
        return TimeSlot::class;
    }

    public function index($request)
    {
        try {

        } catch (Exception $e) {

            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
    }
}
