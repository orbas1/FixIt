<?php

namespace App\Http\Controllers\API;

use App\Exceptions\ExceptionHandler;
use App\Http\Controllers\Controller;
use App\Repositories\Backend\TaxRepository;
use Exception;
use Illuminate\Http\Request;

class TaxController extends Controller
{
    public $repository;

    public function __construct(TaxRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            if (!$request->has('zone_id')) {
                throw new ExceptionHandler('Zone ID is required', 404);
            }
            $taxes = $this->repository->where(['status' => true]);
            if ($request->has('zone_id')) {
                $taxes = $taxes->where('zone_id', $request->zone_id);
            }

            return $taxes = $taxes->latest('created_at')->paginate($request->paginate ?? $taxes->count());
        } catch (Exception $e) {
            throw new ExceptionHandler($e->getMessage(), $e->getCode());
        }
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
    public function store(Request $request)
    {
        //
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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
