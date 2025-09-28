<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\CurrencyDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\CreateCurrencyRequest;
use App\Http\Requests\Backend\UpdateCurrencyRequest;
use App\Models\Currency;
use App\Repositories\Backend\CurrencyRepository;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public $repository;

    public function __construct(CurrencyRepository $repository)
    {
        $this->authorizeResource(Currency::class, 'currency');
        $this->repository = $repository;
    }

    public function index(CurrencyDataTable $dataTable)
    {
        return $dataTable->render('backend.currency.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return $this->repository->create();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CreateCurrencyRequest $request)
    {
        return $this->repository->store($request);
    }

    /**
     * Display the specified resource.
     */
    public function show(Currency $currency)
    {
        //
    }

    public function getSymbol(Request $request)
    {
        return $this->repository->getSymbol($request);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Currency $currency)
    {
        return $this->repository->edit($currency?->id);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCurrencyRequest $request, Currency $currency)
    {
        return $this->repository->update($request, $currency?->id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Currency $currency)
    {
        return $this->repository->destroy($currency?->id);
    }

    public function status(Request $request, $id)
    {
        return $this->repository->status($id, $request->status);
    }

    public function deleteRows(Request $request)
    {
        try {
            foreach ($request->id as $row => $key) {
                $currency = Currency::find($request->id[$row]);
                $currency->delete();
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
