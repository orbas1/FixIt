<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\CreateFavouriteListRequest;
use App\Models\FavouriteList as ModelsFavouriteList;
use App\Repositories\API\FavouriteListRepository;
use Illuminate\Http\Request;

class FavouriteListController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected $model;

    protected $repository;

    public function __construct(ModelsFavouriteList $favouriteList, FavouriteListRepository $repository)
    {
        $this->model = $favouriteList;
        $this->repository = $repository;

    }

    public function index(Request $request)
    {
        $favouriteList = $this->repository->index($request);

        return response()->json([
            'data' => $favouriteList,
            'success' => true,
        ]);
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
    public function store(CreateFavouriteListRequest $request)
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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        return $this->repository->destroy($id);
    }
}
