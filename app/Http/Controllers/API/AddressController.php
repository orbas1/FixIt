<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\CreateAddressRequest;
use App\Http\Requests\API\UpdateAddressRequest;
use App\Models\Address;
use App\Repositories\API\AddressRepository;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected $model;

    protected $repository;

    public function __construct(Address $address, AddressRepository $repository)
    {
        $this->model = $address;
        $this->repository = $repository;

    }

    public function index(Request $request)
    {
        $address = $this->model->where('user_id', auth()->user()->id);
        $paginate = $request->input('paginate', $address->count());
        $addresses = $address->latest('created_at')->simplePaginate($paginate);

        return response()->json(['success' => true, 'data' => $addresses]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateAddressRequest $request)
    {
        return $this->repository->store($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAddressRequest $request, $address)
    {
        return $this->repository->update($request->all(), $address);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        return $this->repository->destroy($id);
    }

    public function isPrimary($id)
    {
        return $this->repository->isPrimary($id);
    }

    public function changeAddressStatus(Request $request, $id)
    {
        return $this->repository->changeAddressStatus($request, $id);
    }
}
