<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\CreateRateAppRequest;
use App\Repositories\API\RateAppRepository;

class RateAppController extends Controller
{
    protected $repository;

    public function __construct(RateAppRepository $repository)
    {
        $this->repository = $repository;
    }

    public function store(CreateRateAppRequest $request)
    {
        return $this->repository->store($request);
    }
}
