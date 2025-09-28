<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\CustomerDataTable;
use App\Helpers\Helpers;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\CreateCustomerRequest;
use App\Http\Requests\Backend\UpdateCustomerRequest;
use App\Models\Customer;
use App\Repositories\Backend\CustomerRepository;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    private $repository;

    public function __construct(CustomerRepository $repository)
    {
        $this->authorizeResource(Customer::class, 'customer');
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(CustomerDataTable $dataTable)
    {
        return $dataTable->render('backend.customer.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('backend.customer.create', [
            'countries' => Helpers::getCountries(),
            'countryCodes' => Helpers::getCountryCodes(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateCustomerRequest $request)
    {
        return $this->repository->store($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        return $this->repository->edit($customer?->id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer)
    {
        return $this->repository->update($request, $customer?->id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        return $this->repository->destroy($customer?->id);
    }

    public function status(Request $request, $id)
    {
        return $this->repository->status($id, $request->status);
    }

    public function deleteRows(Request $request)
    {
        try {
            foreach ($request->id as $row => $key) {
                $banner = Customer::find($request->id[$row]);
                $banner->delete();
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
