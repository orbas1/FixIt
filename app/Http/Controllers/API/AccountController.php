<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\UpdatePasswordRequest;
use App\Http\Requests\API\UpdateProfileRequest;
use App\Repositories\API\AccountRepository;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    protected $repository;

    public function __construct(AccountRepository $repository)
    {
        $this->repository = $repository;
    }

    public function self()
    {
        return $this->repository->self();
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        return $this->repository->updateProfile($request);
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        return $this->repository->updatePassword($request);
    }

    public function updateUserZone(Request $request)
    {
        return $this->repository->updateUserZone($request);
    }

    public function deleteAccount()
    {
        return $this->repository->deleteAccount();
    }
}
