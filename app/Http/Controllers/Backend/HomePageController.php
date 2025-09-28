<?php

namespace App\Http\Controllers\Backend;

use App\Models\HomePage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Backend\HomePageRepository;

class HomePageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public $repository;

    public function __construct(HomePageRepository $repository)
    {
        $this->authorizeResource(HomePage::class, 'homePage');
        $this->repository = $repository;
    }

    public function index()
    {
        return $this->repository->index();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, HomePage $homePage)
    {
        return $this->repository->update($request, $homePage?->id);
    }
}
