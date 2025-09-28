<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\CreateBankDetailRequest;
use App\Http\Requests\API\UpdateBankDetailRequest;
use App\Models\BankDetail;
use App\Repositories\API\BankDetailRepository;
use Illuminate\Http\Request;

class BankDetailController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected $repository;

    public function __construct(BankDetailRepository $repository)
    {
        $this->authorizeResource(BankDetail::class, 'bankDetail', ['except' => ['update']]);
        $this->repository = $repository;
    }

    public function index(Request $request)
    {
        return $this->repository->index($request);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function store(CreateBankDetailRequest $request)
    {
        return $this->repository->store($request);
    }


    public function show(BankDetail $bankDetail)
    {
        //
    }


    public function edit(BankDetail $bankDetail)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBankDetailRequest $request, $user_id)
    {
        return $this->repository->update($request, $user_id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, BankDetail $bankDetail)
    {
        //
    }
}
