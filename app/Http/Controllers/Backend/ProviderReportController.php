<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Repositories\Backend\ProviderReportRepository;

class ProviderReportController extends Controller
{
    public $repository;

    /**
     * Display a listing of the resource.
     */
    public function __construct(ProviderReportRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index()
    {
        return $this->repository->index();
    }

    public function filter(Request $request)
    {
        return $this->repository->filter($request);
    }

    public function export(Request $request)
    {
        return $this->repository->export($request);
    }
}
