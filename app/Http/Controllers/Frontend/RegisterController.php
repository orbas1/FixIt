<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\RegisterRequest;
use App\Repositories\Frontend\RegisterRepository;

class RegisterController extends Controller
{
    private $repository;

    public function __construct(RegisterRepository $repository)
    {
        $this->repository = $repository;
    }


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('frontend.auth.register');
    }

    public function register(RegisterRequest $request)
    {
        return $this->repository->store($request);
    }
}
