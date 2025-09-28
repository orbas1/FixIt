<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Frontend\SubscribeRepositry;

class SubscribeController extends Controller
{
    public $repository;

    public function __construct(SubscribeRepositry $repository)
    {
        $this->repository = $repository;
    }

    public function store(Request $request)
    {
        return $this->repository->store($request);
    }
}