<?php

namespace App\Http\Controllers\Backend;

use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Repositories\AccountRepository;

class AccountController extends Controller
{
    protected $repository;

    protected $address;

    public function __construct(AccountRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Show Admin Profile
     */
    public function profile()
    {
        $user = auth()->user();
        return view('backend.account.profile', ['countries' => Helpers::getCountries(),'user' => $user]);
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        return $this->repository->updatePassword($request);
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        return $this->repository->updateProfile($request);
    }
}
