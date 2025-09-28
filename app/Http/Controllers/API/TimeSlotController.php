<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Repositories\API\TimeSlotRepository;
use Illuminate\Http\Request;

class TimeSlotController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    protected $repository;

    public function __construct(TimeSlotRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(Request $request)
    {
        return $this->repository->index($request);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function chart(Request $request)
    {
        return $this->repository->chart($request);
    }
}
