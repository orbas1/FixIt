<?php

namespace App\Http\Controllers\API;

use App\Exceptions\ExceptionHandler;
use App\Http\Controllers\Controller;
use App\Repositories\API\SystemLangRepository;
use Exception;
use Illuminate\Http\Request;

class SystemLangController extends Controller
{
    public $repository;

    public function __construct(SystemLangRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {

            $languages = $this->repository->where('status', true);

            return $languages = $languages->latest('created_at')->simplePaginate($request->paginate ?? $languages->count());

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

    public function getTranslate(Request $request)
    {
        return $this->repository?->getTranslate($request);
    }

    public function getProviderTranslate(Request $request)
    {
        return $this->repository?->getProviderTranslate($request);
    }
}
