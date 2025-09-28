<?php

namespace App\Http\Controllers\Backend;

use App\DataTables\CommissionDataTable;
use App\Http\Controllers\Controller;
use App\Http\Requests\Backend\CreateBlogRequest;
use App\Http\Requests\Backend\UpdateBlogRequest;
use App\Models\Blog;
use App\Repositories\Backend\CommissionRepository;
use Illuminate\Http\Request;

class CommissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public $repository;

    public function __construct(CommissionRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(CommissionDataTable $dataTable)
    {
        return $dataTable->render('backend.commission.index');
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
    public function store()
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
    public function edit()
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update()
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy()
    {
        //
    }

    public function updateStatus()
    {
        //
    }

    public function export(Request $request)
    {
        return $this->repository->export($request);
    }
}
